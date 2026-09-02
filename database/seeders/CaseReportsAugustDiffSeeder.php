<?php

namespace Database\Seeders;

use App\Models\CaseReport;
use App\Models\CaseReportDetail;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * 8月末時点データとの差分：956件を case_reports + case_report_details に投入する。
 *
 * 対象データは database/seeders/data/case_reports_august_diff.json に外出ししている
 * （956件・日本語テキスト・JSONを含むため、可読性のためファイル分離）。
 *
 * 判定基準：submitted_at（秒単位）で既存9,802件と突き合わせ、
 * 新Excel側にのみ存在する（=未取り込みの）レコードのみを対象とした。
 * member_id は投入時点では設定しない（別途、既存の一括セットSQLで対応する）。
 *
 * 【重要・実行前に必ず確認】
 * CaseReport::$casts / CaseReportDetail::$casts に
 * 'complication_types' => 'array' や 'data' => 'array' のような
 * castが設定されている場合、下記の json_encode() 済み文字列を
 * そのまま渡すと二重エンコードされてしまう。
 * castが設定されていれば、json_encode()せずPHP配列のまま渡すこと。
 *
 * 実行：
 *   php artisan db:seed --class=Database\\Seeders\\CaseReportsAugustDiffSeeder
 */
class CaseReportsAugustDiffSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = database_path('data/case_reports_august_diff.json');

        if (!file_exists($jsonPath)) {
            $this->command->error("データファイルが見つかりません: {$jsonPath}");
            return;
        }

        $records = json_decode(file_get_contents($jsonPath), true);

        $beforeCount = CaseReport::count();
        $created = 0;

        DB::transaction(function () use ($records, &$created) {
            foreach ($records as $r) {
                $caseReport = CaseReport::create([
                    'organization_id'    => $r['organization_id'],
                    'member_id'          => null, // 別途一括セットで対応
                    'facility_name_raw'  => $r['facility_name_raw'],
                    'patient_gender'     => $r['patient_gender'],
                    'patient_age_group'  => $r['patient_age_group'],
                    'treatment_area'     => $r['treatment_area'],
                    'complications'      => $r['complications'],
                    // $castsの有無に依存しないよう、ここで明示的にJSON文字列化する
                    'complication_types' => $r['complication_types']
                        ? json_encode($r['complication_types'], JSON_UNESCAPED_UNICODE)
                        : null,
                    'notes'              => $r['notes'],
                    'submitted_at'       => $r['submitted_at'],
                ]);

                if (!empty($r['detail_data'])) {
                    CaseReportDetail::create([
                        'case_report_id' => $caseReport->id,
                        'treatment_area' => $r['treatment_area'],
                        'data'           => json_encode($r['detail_data'], JSON_UNESCAPED_UNICODE),
                    ]);
                }

                $created++;
            }
        });

        $afterCount = CaseReport::count();

        $this->command->info("投入前: {$beforeCount}件 → 投入後: {$afterCount}件（新規作成: {$created}件）");

        if ($afterCount - $beforeCount !== count($records)) {
            $this->command->warn('想定件数と実際の増加件数が一致していません。内容を確認してください。');
        }
    }
}
