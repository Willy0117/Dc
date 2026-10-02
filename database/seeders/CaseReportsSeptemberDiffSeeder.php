<?php

namespace Database\Seeders;

use App\Models\CaseReport;
use App\Models\CaseReportDetail;
use App\Models\Organization;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * 9月末時点データとの差分：566件を case_reports + case_report_details に投入する。
 *
 * 元データ：「9月末時点クリニック別症例件数.xlsx」の「最新データ」シートで
 * A列に NEW が付いている行（2026-08-31 17:56:10 〜 2026-09-30 15:55:24）。
 * database/data/case_reports_september_diff.json に外出ししている。
 *
 * 8月分（CaseReportsAugustDiffSeeder）との違い：
 * - organization_id はJSONに持たず、Excelの「契約リストNo.」（contract_no）から
 *   実行時に organizations を引いて決める。
 *   1件でも見つからない契約No.があれば、何も投入せずに終了する。
 * - 二重実行に備え、同じ submitted_at・organization_id・treatment_area の
 *   レコードが既にあればスキップする。
 *   （submitted_at だけだと、9/2 11:28:45 に別院の報告が2件あるため重複判定を誤る）
 *
 * member_id は8月分と同様に投入時点では設定しない（別途、既存の一括セットSQLで対応）。
 *
 * 【重要・実行前に必ず確認】
 * CaseReport::$casts / CaseReportDetail::$casts に
 * 'complication_types' => 'array' や 'data' => 'array' のような
 * castが設定されている場合、下記の json_encode() 済み文字列を
 * そのまま渡すと二重エンコードされてしまう。
 * castが設定されていれば、json_encode()せずPHP配列のまま渡すこと。
 *
 * 実行：
 *   php artisan db:seed --class=Database\\Seeders\\CaseReportsSeptemberDiffSeeder
 */
class CaseReportsSeptemberDiffSeeder extends Seeder
{
    /**
     * Excelの契約リストNo.が organizations.contract_no と一致しない契約先の対応表。
     * [Excelの契約リストNo. => organizations.id]
     *
     * 個人契約の先生（共有の契約先から個別の契約先に分割された先生など）は、
     * Excel側は旧い契約リストNo.のままのため、ここで organization_id を直接指定する。
     * ※実行前に、既存の症例報告がどの organization_id に付いているかで必ず確認すること。
     */
    private const CONTRACT_NO_OVERRIDES = [
        87  => 217, // 清水 勇樹（OC00217）
        165 => 219, // 田中 健太（OC00219）
    ];

    /**
     * Excelの年代表記 → case_reports.patient_age_group（enum）の値の対応表。
     * Excelは「80歳以上」だが、enumには存在しないため変換する。
     * ※既存データでの保存値を確認して合わせること。
     */
    private const AGE_GROUP_MAP = [
        '80歳以上' => '80代',
    ];

    public function run(): void
    {
        $jsonPath = database_path('data/case_reports_september_diff.json');

        if (!file_exists($jsonPath)) {
            $this->command->error("データファイルが見つかりません: {$jsonPath}");
            return;
        }

        $records = json_decode(file_get_contents($jsonPath), true);

        // ── 契約No. → organization_id の対応を事前に確定 ──
        $contractNos = collect($records)->pluck('contract_no')->unique()->values();
        $orgMap = Organization::whereIn('contract_no', $contractNos)->pluck('id', 'contract_no');

        // 対応表で上書き（指定した organization_id が実在するかも確認）
        foreach (self::CONTRACT_NO_OVERRIDES as $contractNo => $organizationId) {
            if (!Organization::whereKey($organizationId)->exists()) {
                $this->command->error("対応表の organization_id={$organizationId}（契約No.{$contractNo}）が存在しません。投入を中止しました。");
                return;
            }
            $orgMap[$contractNo] = $organizationId;
        }

        $missing = $contractNos->reject(fn ($no) => $orgMap->has($no));
        if ($missing->isNotEmpty()) {
            $this->command->error('organizations に存在しない契約No.があります。投入を中止しました: '
                . $missing->implode(', '));
            return;
        }

        $beforeCount = CaseReport::count();
        $created = 0;
        $skipped = 0;

        DB::transaction(function () use ($records, $orgMap, &$created, &$skipped) {
            foreach ($records as $r) {
                $organizationId = $orgMap[$r['contract_no']];

                // 二重実行防止
                $exists = CaseReport::where('submitted_at', $r['submitted_at'])
                    ->where('organization_id', $organizationId)
                    ->where('treatment_area', $r['treatment_area'])
                    ->exists();
                if ($exists) {
                    $skipped++;
                    continue;
                }

                $caseReport = CaseReport::create([
                    'organization_id'    => $organizationId,
                    'member_id'          => null, // 別途一括セットで対応
                    'facility_name_raw'  => $r['facility_name_raw'],
                    'patient_gender'     => $r['patient_gender'],
                    'patient_age_group'  => self::AGE_GROUP_MAP[$r['patient_age_group']] ?? $r['patient_age_group'],
                    'treatment_area'     => $r['treatment_area'],
                    'complications'      => $r['complications'],
                    // $casts に array 等が設定されていればPHP配列のまま、無ければJSON文字列化する
                    // （8月分は常にjson_encodeしていたため、castがあると二重エンコードになっていた）
                    'complication_types' => $r['complication_types']
                        ? $this->jsonValue(new CaseReport, 'complication_types', $r['complication_types'])
                        : null,
                    'notes'              => $r['notes'],
                    'submitted_at'       => $r['submitted_at'],
                ]);

                if (!empty($r['detail_data'])) {
                    CaseReportDetail::create([
                        'case_report_id' => $caseReport->id,
                        'treatment_area' => $r['treatment_area'],
                        'data'           => $this->jsonValue(new CaseReportDetail, 'data', $r['detail_data']),
                    ]);
                }

                $created++;
            }
        });

        $afterCount = CaseReport::count();

        $this->command->info("投入前: {$beforeCount}件 → 投入後: {$afterCount}件（新規作成: {$created}件 / 既存のためスキップ: {$skipped}件）");

        if ($created + $skipped !== count($records)) {
            $this->command->warn('想定件数と処理件数が一致していません。内容を確認してください。');
        }
    }

    /**
     * モデルにcastがあればPHP配列のまま渡し（castがエンコードする）、
     * 無ければここでJSON文字列にする。二重エンコード防止。
     */
    private function jsonValue($model, string $attribute, array $value)
    {
        return $model->hasCast($attribute)
            ? $value
            : json_encode($value, JSON_UNESCAPED_UNICODE);
    }
}
