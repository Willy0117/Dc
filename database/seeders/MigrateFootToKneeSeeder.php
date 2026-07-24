<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MigrateFootToKneeSeeder extends Seeder
{
    // 膝と判断する穿刺血管キーワード
    const KNEE_VESSEL_KEYWORDS = [
        '膝窩動脈', '膝窩A', '膝窩',
        '鼠径動脈',
        '大腿動脈', '浅大腿動脈',
        '外腸骨動脈',
        '膝下動脈',
    ];

    // 膝血管の正規化マップ
    const KNEE_VESSEL_MAP = [
        '右膝窩動脈'   => '右膝窩動脈',
        '左膝窩動脈'   => '左膝窩動脈',
        '右膝窩A'      => '右膝窩動脈',
        '左膝窩A'      => '左膝窩動脈',
        '膝窩動脈'     => 'その他',
        '両膝窩動脈'   => '両側',
        '両側膝窩動脈' => '両側',
        '膝窩'         => 'その他',
        '右鼠径動脈'   => '右鼠径動脈',
        '左鼠径動脈'   => '左鼠径動脈',
        '右大腿動脈'   => '右鼠径動脈',
        '左大腿動脈'   => '左鼠径動脈',
        '右浅大腿動脈' => '右鼠径動脈',
        '左浅大腿動脈' => '左鼠径動脈',
        '両側浅大腿動脈' => '両側',
        '両側大腿動脈' => '両側',
        '右膝下動脈'   => 'その他',
        '左膝下動脈'   => 'その他',
        '両膝下動脈'   => 'その他',
        '右前脛骨動脈' => 'その他',
        '右後脛骨動脈' => 'その他',
        '後脛骨動脈'   => 'その他',
        '左脛骨動脈'   => 'その他',
    ];

    public function run(): void
    {
        $this->command->info('足→膝の移し替えを開始します...');

        // 足のcase_reportsで穿刺血管にその他が入っているものを取得
        $footDetails = DB::table('case_foot_details')
            ->whereJsonContains('puncture_vessels', 'その他')
            ->get();

        $migrated = 0;
        $skipped  = 0;

        foreach ($footDetails as $detail) {
            // 膝血管キーワードが含まれるか確認
            $isKnee = false;
            foreach (self::KNEE_VESSEL_KEYWORDS as $keyword) {
                if (str_contains($detail->puncture_vessel ?? '', $keyword)) {
                    $isKnee = true;
                    break;
                }
            }

            if (!$isKnee) {
                $skipped++;
                continue;
            }

            DB::transaction(function () use ($detail, &$migrated) {
                // 穿刺血管を膝用に正規化
                $vessels = [];
                $items   = array_map('trim', explode(',', $detail->puncture_vessel ?? ''));

                foreach ($items as $item) {
                    if (empty($item)) continue;
                    $normalized = self::KNEE_VESSEL_MAP[$item] ?? 'その他';

                    // 両側展開
                    if ($normalized === '両側') {
                        foreach (['右膝窩動脈', '左膝窩動脈'] as $v) {
                            if (!in_array($v, $vessels)) $vessels[] = $v;
                        }
                        continue;
                    }

                    if (!in_array($normalized, $vessels)) $vessels[] = $normalized;
                }

                // case_reports の treatment_area を膝に変更
                DB::table('case_reports')
                    ->where('id', $detail->case_report_id)
                    ->update(['treatment_area' => '膝']);

                // case_knee_details に挿入
                DB::table('case_knee_details')->insert([
                    'case_report_id'     => $detail->case_report_id,
                    'puncture_vessel'    => $detail->puncture_vessel,
                    'puncture_vessels'   => json_encode($vessels, JSON_UNESCAPED_UNICODE),
                    'disease_name'       => $detail->disease_name,
                    'disease_names'      => $detail->disease_names, // 足で正規化済み→そのまま
                    'tourniquet_position' => $detail->tourniquet_position,
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);

                // case_foot_details を削除
                DB::table('case_foot_details')->where('id', $detail->id)->delete();

                $migrated++;
            });
        }

        $this->command->info("移し替え完了: {$migrated}件 → 膝");
        $this->command->info("スキップ(足のまま): {$skipped}件");

        // 確認
        $this->command->info("\n--- 確認 ---");
        $this->command->info('case_reports 膝: ' . DB::table('case_reports')->where('treatment_area', '膝')->count() . '件');
        $this->command->info('case_reports 足: ' . DB::table('case_reports')->where('treatment_area', '足')->count() . '件');
        $this->command->info('case_knee_details: ' . DB::table('case_knee_details')->count() . '件');
        $this->command->info('case_foot_details: ' . DB::table('case_foot_details')->count() . '件');
    }
}
