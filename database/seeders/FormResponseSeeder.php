<?php

namespace Database\Seeders;

use App\Models\Organization;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FormResponseSeeder extends Seeder
{
    // CSVファイルのパス（storage/app/ 配下に置くこと）
    const CSV_PATH = __DIR__ . '/../../storage/app/responses.csv';

    // マッチングできなかった施設名を記録
    private array $unmatchedFacilities = [];

    public function run(): void
    {
        if (!file_exists(self::CSV_PATH)) {
            $this->command->error('CSVファイルが見つかりません: ' . self::CSV_PATH);
            $this->command->info('storage/app/responses.csv に配置してください。');
            return;
        }

        // organizationsを正規化済みマップとして読み込む
        $orgMap = $this->buildOrgMap();
        $this->command->info("organizations読み込み: {$orgMap->count()} 件");

        $handle   = fopen(self::CSV_PATH, 'r');
        $rowNum   = 0;
        $imported = 0;
        $skipped  = 0;

        DB::transaction(function () use ($handle, $orgMap, &$rowNum, &$imported, &$skipped) {
            while (($row = fgetcsv($handle)) !== false) {
                $rowNum++;

                // 文字コード変換（必要に応じて）
                $row = array_map(fn($v) => mb_convert_encoding($v ?? '', 'UTF-8', 'UTF-8'), $row);

                // 1行目ヘッダーをスキップ
                if ($rowNum === 1) continue;

                // 空行スキップ
                if (empty(array_filter($row))) {
                    $skipped++;
                    continue;
                }

                // ----------------------------------------
                // カラムマッピング（0始まり）
                // [0] 施設名確認（ほぼNULL、使わない）
                // [1] タイムスタンプ
                // [2] 契約医療機関名・契約者名
                // [3] 患者性別
                // [4] 年代
                // [5] 治療部位
                // [6] 穿刺血管（手・共通）
                // [7] 病名（手・共通）
                // [8] 右手疼痛部位
                // [9] 左手疼痛部位
                // [10] 穿刺血管.1（足）
                // [11] 病名.1（足）
                // [12] 駆血部位（足）
                // [13] 穿刺血管.2（肘）
                // [14] 駆血部位.1（肘）
                // [15] 病名.2（肘）
                // [16] 肩_穿刺血管
                // [17] 肩_駆血部位
                // [18] 肩_病名
                // [19] 膝_穿刺血管
                // [20] 膝_駆血部位
                // [21] 膝_病名
                // [22] トラブル・合併症
                // [23] 上記の詳細特記事項
                // ----------------------------------------

                $facilityRaw  = $this->clean($row[2] ?? '');
                $treatmentArea = $this->clean($row[5] ?? '');

                // 治療部位が不正な値はスキップ
                if (!in_array($treatmentArea, ['手', '足', '肘', '肩', '膝'])) {
                    $skipped++;
                    continue;
                }

                // 施設名マッチング
                $orgId = $this->matchOrganization($facilityRaw, $orgMap);

                // case_reports（共通）を挿入
                $caseReportId = DB::table('case_reports')->insertGetId([
                    'organization_id'   => $orgId,
                    'facility_name_raw' => str_replace('様', '', $facilityRaw),
                    'patient_gender'    => $this->normalizeGender($row[3] ?? ''),
                    'patient_age_group' => $this->normalizeAgeGroup($row[4] ?? ''),
                    'treatment_area'    => $treatmentArea,
                    'complications'     => $this->clean($row[22] ?? ''),
                    'notes'             => $this->clean($row[23] ?? ''),
                    'submitted_at'      => $this->parseDate($row[1] ?? ''),
                    'csv_row'           => $rowNum,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);

                // 治療部位別テーブルに挿入
                match ($treatmentArea) {
                    '手' => DB::table('case_hand_details')->insert([
                        'case_report_id'  => $caseReportId,
                        'puncture_vessel' => $this->clean($row[6] ?? ''),
                        'disease_name'    => $this->clean($row[7] ?? ''),
                        'right_pain_areas' => $this->clean($row[8] ?? ''),
                        'left_pain_areas'  => $this->clean($row[9] ?? ''),
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ]),
                    '足' => DB::table('case_foot_details')->insert([
                        'case_report_id'     => $caseReportId,
                        'puncture_vessel'    => $this->clean($row[10] ?? ''),
                        'disease_name'       => $this->clean($row[11] ?? ''),
                        'tourniquet_position' => $this->normalizeTourniquet($row[12] ?? ''),
                        'created_at'         => now(),
                        'updated_at'         => now(),
                    ]),
                    '肘' => DB::table('case_elbow_details')->insert([
                        'case_report_id'     => $caseReportId,
                        'puncture_vessel'    => $this->clean($row[13] ?? ''),
                        'disease_name'       => $this->clean($row[15] ?? ''),
                        'tourniquet_position' => $this->normalizeTourniquet($row[14] ?? ''),
                        'created_at'         => now(),
                        'updated_at'         => now(),
                    ]),
                    '肩' => DB::table('case_shoulder_details')->insert([
                        'case_report_id'     => $caseReportId,
                        'puncture_vessel'    => $this->clean($row[16] ?? ''),
                        'disease_name'       => $this->clean($row[18] ?? ''),
                        'tourniquet_position' => $this->normalizeTourniquet($row[17] ?? ''),
                        'created_at'         => now(),
                        'updated_at'         => now(),
                    ]),
                    '膝' => DB::table('case_knee_details')->insert([
                        'case_report_id'     => $caseReportId,
                        'puncture_vessel'    => $this->clean($row[19] ?? ''),
                        'disease_name'       => $this->clean($row[21] ?? ''),
                        'tourniquet_position' => $this->normalizeTourniquet($row[20] ?? ''),
                        'created_at'         => now(),
                        'updated_at'         => now(),
                    ]),
                };

                $imported++;

                if ($imported % 500 === 0) {
                    $this->command->info("  {$imported} 件処理済み...");
                }
            }
        });

        fclose($handle);

        $this->command->info("========== 完了 ==========");
        $this->command->info("インポート: {$imported} 件");
        $this->command->warn("スキップ  : {$skipped} 件");

        // 未マッチの施設を表示
        if (!empty($this->unmatchedFacilities)) {
            $this->command->warn("\n【要確認】organization_id が紐づかなかった施設（" . count($this->unmatchedFacilities) . " 件）:");
            foreach (array_unique($this->unmatchedFacilities) as $name) {
                $this->command->warn("  - {$name}");
            }
            $this->command->warn("上記は case_reports.organization_id が NULL になっています。");
            $this->command->warn("管理画面から手動で紐づけてください。");
        }
    }

    // ----------------------------------------
    // organizationsをロードして正規化マップを作成
    // ----------------------------------------
    private function buildOrgMap(): \Illuminate\Support\Collection
    {
        return DB::table('organizations')
            ->select('id', 'name')
            ->get()
            ->mapWithKeys(function ($org) {
                return [$this->normalizeFacilityName($org->name) => $org->id];
            });
    }

    // ----------------------------------------
    // 施設名マッチング
    // ----------------------------------------
    private function matchOrganization(string $rawName, \Illuminate\Support\Collection $orgMap): ?int
    {
        if (empty($rawName)) return null;

        $normalized = $this->normalizeFacilityName($rawName);

        // 完全一致
        if ($orgMap->has($normalized)) {
            return $orgMap->get($normalized);
        }

        // 部分一致（5文字以上の場合のみ）
        if (mb_strlen($normalized) >= 5) {
            foreach ($orgMap as $orgNormalized => $orgId) {
                if (mb_strlen($orgNormalized) < 5) continue;
                if (str_contains($orgNormalized, $normalized) || str_contains($normalized, $orgNormalized)) {
                    return $orgId;
                }
            }
        }

        // マッチなし
        $this->unmatchedFacilities[] = $rawName;
        return null;
    }

    // ----------------------------------------
    // 施設名の正規化
    // 法人名除去はせず、様・スペースのみ正規化。マッチングは部分一致で吸収。
    // ----------------------------------------
    private function normalizeFacilityName(string $name): string
    {
        $name = trim($name);
        $name = str_replace('様', '', $name);
        // 全角・半角スペース、タブ、改行コード文字を除去
        $name = preg_replace('/[\s\t　↵]+/u', '', $name);
        return trim($name);
    }

    // ----------------------------------------
    // ヘルパー
    // ----------------------------------------
    private function clean(?string $value): ?string
    {
        if ($value === null) return null;
        $value = trim($value);
        return $value === '' ? null : $value;
    }

    private function parseDate(?string $value): ?string
    {
        if (empty(trim($value ?? ''))) return null;
        try {
            return Carbon::parse(str_replace('/', '-', $value))->toDateTimeString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function normalizeGender(?string $value): ?string
    {
        return match (trim($value ?? '')) {
            '男性' => '男性',
            '女性' => '女性',
            default => '不明',
        };
    }

    private function normalizeAgeGroup(?string $value): ?string
    {
        $value = trim($value ?? '');
        if (empty($value)) return null;

        // 全角数字→半角に変換
        $value = mb_convert_kana($value, 'n');

        // 「50代」のような標準形はそのまま
        if (preg_match('/^(\d+)代以下$/', $value)) return '10代以下';
        if (preg_match('/^(\d+)代以上$/', $value)) return '90代以上';
        if (preg_match('/^(\d+)代$/', $value, $m)) {
            $decade = (int)$m[1];
            if ($decade <= 10) return '10代以下';
            if ($decade >= 90) return '90代以上';
            return "{$decade}代";
        }
        // 「80歳以上」「90歳以上」など
        if (preg_match('/(\d+)歳以上/', $value, $m)) {
            return (int)$m[1] >= 80 ? '90代以上' : '80代';
        }
        // 「8歳」「95歳」など具体的な年齢
        if (preg_match('/^(\d+)歳?$/', $value, $m)) {
            $age = (int)$m[1];
            if ($age < 10) return '10代以下';
            if ($age >= 90) return '90代以上';
            $decade = (int)floor($age / 10) * 10;
            return "{$decade}代";
        }
        return null;
    }

    private function normalizeTourniquet(?string $value): ?string
    {
        return match (trim($value ?? '')) {
            '中枢側' => '中枢側',
            '末梢側' => '末梢側',
            '駆血なし' => '駆血なし',
            default => null,
        };
    }
}
