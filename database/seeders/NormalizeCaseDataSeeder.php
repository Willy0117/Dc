<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NormalizeCaseDataSeeder extends Seeder
{
    public function run(): void
    {
        // 毎回全リセットして再正規化
        DB::table('case_hand_details')->update(['puncture_vessels' => null, 'disease_names' => null, 'right_pain_area_list' => null, 'left_pain_area_list' => null]);
        DB::table('case_foot_details')->update(['puncture_vessels' => null, 'disease_names' => null]);
        DB::table('case_elbow_details')->update(['puncture_vessels' => null, 'disease_names' => null]);
        DB::table('case_shoulder_details')->update(['puncture_vessels' => null, 'disease_names' => null]);
        DB::table('case_knee_details')->update(['puncture_vessels' => null, 'disease_names' => null]);
        DB::table('case_reports')->update(['complication_types' => null]);

        $this->command->info('穿刺血管・病名の正規化を開始します...');

        $this->normalizeHand();
        $this->normalizeFoot();
        $this->normalizeElbow();
        $this->normalizeShoulder();
        $this->normalizeKnee();
        $this->normalizeComplications();

        $this->command->info('正規化完了');
    }

    // ────────────────────────────────
    // 手
    // ────────────────────────────────
    private function normalizeHand(): void
    {
        $vesselMap = [
            '右 橈骨動脈' => '右 橈骨動脈', '右　橈骨動脈' => '右 橈骨動脈', '右橈骨動脈' => '右 橈骨動脈', '右RA' => '右 橈骨動脈',
            '右 尺骨動脈' => '右 尺骨動脈', '右尺骨動脈' => '右 尺骨動脈',
            '左 橈骨動脈' => '左 橈骨動脈', '左　橈骨動脈' => '左 橈骨動脈', '左橈骨動脈' => '左 橈骨動脈',
            '左 尺骨動脈' => '左 尺骨動脈', '左尺骨動脈' => '左 尺骨動脈',
            '右 上腕動脈' => '右 上腕動脈', '右上腕動脈' => '右 上腕動脈', '右　上腕動脈' => '右 上腕動脈',
            '右上腕A' => '右 上腕動脈', '右上腕Ａ' => '右 上腕動脈', '右上腕' => '右 上腕動脈',
            '左 上腕動脈' => '左 上腕動脈', '左上腕動脈' => '左 上腕動脈',
            '左上腕A' => '左 上腕動脈', '左上腕Ａ' => '左 上腕動脈', '左上腕' => '左 上腕動脈',
            '両上腕動脈' => '両側', '両側上腕動脈' => '両側', '左右上腕動脈' => '両側',
            '右・左上腕動脈' => '両側', '両　上腕動脈' => '両側', '両腕上腕動脈' => '両側',
        ];

        $diseaseMap = [
            'ヘバーデン結節' => 'ヘバーデン結節', 'へバーデン結節' => 'ヘバーデン結節', 'ヘバーデン' => 'ヘバーデン結節',
            'ブシャール結節' => 'ブシャール結節',
            'CM関節症' => 'CM関節症',
            'バネ指（MP関節炎）' => 'バネ指（MP関節炎）', 'バネ指' => 'バネ指（MP関節炎）',
            '弾発指' => 'バネ指（MP関節炎）', 'ばね指' => 'バネ指（MP関節炎）',
            '腱鞘炎' => '腱鞘炎',
            'TFCC損傷' => 'TFCC損傷', 'TFCC' => 'TFCC損傷',
            '前回と同じ' => '前回と同じ',
        ];

        $rows = DB::table('case_hand_details')->whereNull('puncture_vessels')->get();
        $count = 0;

        foreach ($rows as $row) {
            $vessels  = $this->normalizeMultiple($row->puncture_vessel, $vesselMap, ['両側' => ['右 上腕動脈', '左 上腕動脈']]);
            $diseases = $this->normalizeMultiple($row->disease_name, $diseaseMap);

            DB::table('case_hand_details')->where('id', $row->id)->update([
                'puncture_vessels'   => json_encode($vessels, JSON_UNESCAPED_UNICODE),
                'disease_names'      => json_encode($diseases, JSON_UNESCAPED_UNICODE),
                'right_pain_area_list' => $row->right_pain_areas ? json_encode(array_map('trim', explode(',', $row->right_pain_areas)), JSON_UNESCAPED_UNICODE) : json_encode([]),
                'left_pain_area_list'  => $row->left_pain_areas  ? json_encode(array_map('trim', explode(',', $row->left_pain_areas)),  JSON_UNESCAPED_UNICODE) : json_encode([]),
            ]);
            $count++;
        }

        $this->command->info("手: {$count}件 正規化完了");
    }

    // ────────────────────────────────
    // 足
    // ────────────────────────────────
    private function normalizeFoot(): void
    {
        $vesselMap = [
            '右 PTA' => '右 PTA', '右PTA' => '右 PTA',
            '左 PTA' => '左 PTA', '左  PTA' => '左 PTA', '左PTA' => '左 PTA',
            '右 足背動脈' => '右 足背動脈', '右足背動脈' => '右 足背動脈',
            '左 足背動脈' => '左 足背動脈', '左足背動脈' => '左 足背動脈',
        ];

        $diseaseMap = [
            '足底腱膜炎' => '足底腱膜炎', '有痛性外脛骨' => '有痛性外脛骨',
            '外反母趾' => '外反母趾', 'モートン病' => 'モートン病',
            'アキレス腱炎' => 'アキレス腱炎', '足関節滑膜炎' => '足関節滑膜炎',
            '足関節捻挫' => '足関節捻挫', '疲労骨折' => '疲労骨折',
            '前回と同じ' => '前回と同じ',
        ];

        $rows = DB::table('case_foot_details')->whereNull('puncture_vessels')->get();
        $count = 0;

        foreach ($rows as $row) {
            DB::table('case_foot_details')->where('id', $row->id)->update([
                'puncture_vessels' => json_encode($this->normalizeMultiple($row->puncture_vessel, $vesselMap), JSON_UNESCAPED_UNICODE),
                'disease_names'    => json_encode($this->normalizeMultiple($row->disease_name, $diseaseMap), JSON_UNESCAPED_UNICODE),
            ]);
            $count++;
        }

        $this->command->info("足: {$count}件 正規化完了");
    }

    // ────────────────────────────────
    // 肘
    // ────────────────────────────────
    private function normalizeElbow(): void
    {
        $vesselMap = [
            '右上腕動脈' => '右上腕動脈', '右 上腕動脈' => '右上腕動脈',
            '左上腕動脈' => '左上腕動脈', '左 上腕動脈' => '左上腕動脈',
        ];

        $diseaseMap = [
            '外側上顆炎' => '外側上顆炎', '左上腕骨外側上顆炎' => '外側上顆炎', 'テニス肘' => '外側上顆炎',
            '内側上顆炎' => '内側上顆炎', 'MCL損傷' => 'MCL損傷',
            '肘関節滑膜炎' => '肘関節滑膜炎', 'TFCC損傷' => 'TFCC損傷',
            '腱鞘炎' => '腱鞘炎', '前回と同じ' => '前回と同じ',
        ];

        $rows = DB::table('case_elbow_details')->whereNull('puncture_vessels')->get();
        $count = 0;

        foreach ($rows as $row) {
            DB::table('case_elbow_details')->where('id', $row->id)->update([
                'puncture_vessels' => json_encode($this->normalizeMultiple($row->puncture_vessel, $vesselMap), JSON_UNESCAPED_UNICODE),
                'disease_names'    => json_encode($this->normalizeMultiple($row->disease_name, $diseaseMap), JSON_UNESCAPED_UNICODE),
            ]);
            $count++;
        }

        $this->command->info("肘: {$count}件 正規化完了");
    }

    // ────────────────────────────────
    // 肩
    // ────────────────────────────────
    private function normalizeShoulder(): void
    {
        $vesselMap = [
            '右 鎖骨下動脈' => '右 鎖骨下動脈', '右鎖骨下動脈' => '右 鎖骨下動脈',
            '左 鎖骨下動脈' => '左 鎖骨下動脈', '左鎖骨下動脈' => '左 鎖骨下動脈',
            '右 腋窩動脈' => '右 腋窩動脈', '右腋窩動脈' => '右 腋窩動脈', '右腋窩A' => '右 腋窩動脈',
            '左 腋窩動脈' => '左 腋窩動脈', '左腋窩動脈' => '左 腋窩動脈', '左腋窩A' => '左 腋窩動脈',
            // 頸横動脈系（右）
            '右頸横動脈' => '右 頸横動脈', '右頚横動脈' => '右 頸横動脈',
            '右けいおう動脈' => '右 頸横動脈', '右けいおうA' => '右 頸横動脈',
            '右頚横A' => '右 頸横動脈', '右頸横A' => '右 頸横動脈',
            // 頸横動脈系（左）
            '左頸横動脈' => '左 頸横動脈', '左頚横動脈' => '左 頸横動脈',
            '左けいおう動脈' => '左 頸横動脈', '左頚横A' => '左 頸横動脈',
            '左横頸動脈' => '左 頸横動脈',
            // 頸横動脈系（左右不明→その他）
            '頸横動脈' => 'その他', '頚横動脈' => 'その他',
            '頸横A' => 'その他', 'けいおう動脈' => 'その他', 'けいおうA' => 'その他',
            // 両側展開
            '両頚横動脈' => '両側頸横', '両側頸横動脈' => '両側頸横',
            '両頚横A' => '両側頸横', '両側頚横動脈' => '両側頸横',
            '両けいおう動脈' => '両側頸横', '両側けいおう動脈' => '両側頸横',
            '両けいおうA' => '両側頸横', '左右頸横動脈' => '両側頸横',
        ];

        $expand = ['両側頸横' => ['右 頸横動脈', '左 頸横動脈']];

        $diseaseMap = [
            '肩関節周囲炎' => '肩関節周囲炎', '凍結肩' => '肩関節周囲炎', '拘縮肩' => '肩関節周囲炎',
            'SIRVA' => 'SIRVA', '腱板断裂' => '腱板断裂', '腱板術後' => '腱板断裂',
            'CTA' => 'CTA', '前回と同じ' => '前回と同じ',
        ];

        // 再実行できるようにNULLにリセット
        DB::table('case_shoulder_details')->update(['puncture_vessels' => null]);

        $rows = DB::table('case_shoulder_details')->whereNull('puncture_vessels')->get();
        $count = 0;

        foreach ($rows as $row) {
            DB::table('case_shoulder_details')->where('id', $row->id)->update([
                'puncture_vessels' => json_encode($this->normalizeMultiple($row->puncture_vessel, $vesselMap, $expand), JSON_UNESCAPED_UNICODE),
                'disease_names'    => json_encode($this->normalizeMultiple($row->disease_name, $diseaseMap), JSON_UNESCAPED_UNICODE),
            ]);
            $count++;
        }

        $this->command->info("肩: {$count}件 正規化完了");
    }

    // ────────────────────────────────
    // 膝
    // ────────────────────────────────
    private function normalizeKnee(): void
    {
        $vesselMap = [
            '右膝窩動脈' => '右膝窩動脈', '左膝窩動脈' => '左膝窩動脈',
            '右膝窩A' => '右膝窩動脈', '左膝窩A' => '左膝窩動脈',
            '右膝窩Ａ' => '右膝窩動脈', '左膝窩Ａ' => '左膝窩動脈',
            '右膝窩同動脈' => '右膝窩動脈', '右膝窩' => '右膝窩動脈', '左膝窩' => '左膝窩動脈',
            '左足膝窩' => '左膝窩動脈',
            '両膝窩' => '両膝窩動脈', '両膝窩A' => '両膝窩動脈', '両膝窩Ａ' => '両膝窩動脈',
            '両膝窩動脈' => '両膝窩動脈', '両側膝窩動脈' => '両膝窩動脈',
            '左右膝窩動脈' => '両膝窩動脈', '左右膝窩' => '両膝窩動脈',
            '膝窩動脈（両膝）' => '両膝窩動脈',
            // 外腸骨動脈系
            '右外腸骨動脈' => 'その他', '左外腸骨動脈' => 'その他', '外腸骨動脈' => 'その他',
            // 膝か（誤字）
            '右膝か動脈' => '右膝窩動脈', '左膝か動脈' => '左膝窩動脈', '膝か' => 'その他',
            '右大腿動脈' => '右鼠径動脈', '右鼠径動脈' => '右鼠径動脈', '右浅大腿動脈' => '右鼠径動脈',
            '右大腿A' => '右鼠径動脈', '右大腿Ａ' => '右鼠径動脈',
            '左大腿動脈' => '左鼠径動脈', '左鼠径動脈' => '左鼠径動脈', '左浅大腿動脈' => '左鼠径動脈',
            '左大腿A' => '左鼠径動脈', '左大腿Ａ' => '左鼠径動脈',
            '右足背動脈' => '右足背動脈', '左足背動脈' => '左足背動脈',
        ];

        $diseaseMap = [
            '変形性膝関節症' => '変形性膝関節症', '膝蓋腱炎' => '膝蓋腱炎',
            '鵞足炎' => '鵞足炎', '膝蓋下脂肪体炎' => '膝蓋下脂肪体炎',
            '腸脛骨靭帯炎' => '腸脛骨靭帯炎', '膝蓋大腿靭帯炎' => '膝蓋大腿靭帯炎',
            'オスグッド病' => 'オスグッド病', 'シンスプリント' => 'シンスプリント',
            '関節リウマチ' => '関節リウマチ', '脛骨疲労骨折' => '脛骨疲労骨折',
            '前回と同じ' => '前回と同じ',
        ];

        $expand = ['両膝窩動脈' => ['右膝窩動脈', '左膝窩動脈']];

        // 再実行できるようにNULLにリセット
        DB::table('case_knee_details')->update(['puncture_vessels' => null, 'disease_names' => null]);

        $rows = DB::table('case_knee_details')->whereNull('puncture_vessels')->get();
        $count = 0;

        foreach ($rows as $row) {
            DB::table('case_knee_details')->where('id', $row->id)->update([
                'puncture_vessels' => json_encode($this->normalizeMultiple($row->puncture_vessel, $vesselMap, $expand), JSON_UNESCAPED_UNICODE),
                'disease_names'    => json_encode($this->normalizeMultiple($row->disease_name, $diseaseMap), JSON_UNESCAPED_UNICODE),
            ]);
            $count++;
        }

        $this->command->info("膝: {$count}件 正規化完了");
    }

    // ────────────────────────────────
    // トラブル・合併症
    // ────────────────────────────────
    private function normalizeComplications(): void
    {
        $complicationMap = [
            '疼痛部に明らかにチエナムが分布した'     => '疼痛部に明らかにチエナムが分布した',
            '疼痛部にチエナムが明らかに到達しなかった' => '疼痛部にチエナムが明らかに到達しなかった',
            '一部には到達して、一部には到達しなかった' => '一部には到達して、一部には到達しなかった',
            '疼痛部にチエナムが到達したかが不明'      => '疼痛部にチエナムが到達したかが不明',
            '治療中の痛み強かった'    => '治療中の痛み強かった',
            '治療に時間がかかった'    => '治療に時間がかかった',
            'アレルギー反応あり'      => 'アレルギー反応あり',
            '神経損傷'                => '神経損傷',
        ];

        $rows = DB::table('case_reports')->whereNull('complication_types')->whereNotNull('complications')->get();
        $count = 0;

        foreach ($rows as $row) {
            $types = $this->normalizeMultiple($row->complications, $complicationMap);
            DB::table('case_reports')->where('id', $row->id)->update([
                'complication_types' => json_encode($types, JSON_UNESCAPED_UNICODE),
            ]);
            $count++;
        }

        $this->command->info("トラブル・合併症: {$count}件 正規化完了");
    }

    // ────────────────────────────────
    // ヘルパー：カンマ区切りテキストを正規化してarray返す
    // ────────────────────────────────
    private function normalizeMultiple(?string $value, array $map, array $expand = []): array
    {
        if (empty($value)) return [];

        $result = [];
        // カンマ・読点・全角読点で分割（スペースは血管名に含まれるため除外）
        $items = array_filter(array_map('trim', preg_split('/[,、。]+/u', $value)));

        foreach ($items as $item) {
            if (empty($item)) continue;

            // 展開マップにある場合（明示的に定義された両側など）
            if (isset($expand[$item])) {
                foreach ($expand[$item] as $expanded) {
                    if (!in_array($expanded, $result)) {
                        $result[] = $expanded;
                    }
                }
                continue;
            }

            // 通常マップにある場合
            if (isset($map[$item])) {
                $normalized = $map[$item];
                if (!in_array($normalized, $result)) {
                    $result[] = $normalized;
                }
                continue;
            }

            // 両・両側・左右 プレフィックスを自動展開
            // 例）両脛骨動脈 → 右脛骨動脈、左脛骨動脈
            $bilateral = $this->expandBilateral($item, $map);
            if ($bilateral !== null) {
                foreach ($bilateral as $expanded) {
                    if (!in_array($expanded, $result)) {
                        $result[] = $expanded;
                    }
                }
                continue;
            }

            // どれにもマッチしない → その他
            if (!in_array('その他', $result)) {
                $result[] = 'その他';
            }
        }

        return $result;
    }

    /**
     * 両・両側・左右 プレフィックスを除去して右・左に展開
     * マップに右〇〇または左〇〇が存在する場合のみ展開する
     */
    private function expandBilateral(string $item, array $map): ?array
    {
        $prefixes = ['両側', '左右', '右左', '両'];

        foreach ($prefixes as $prefix) {
            if (mb_strpos($item, $prefix) === 0) {
                $base  = mb_substr($item, mb_strlen($prefix));
                $right = '右' . $base;
                $left  = '左' . $base;

                // マップに右・左が存在すれば展開
                $rightNormalized = $map[$right] ?? null;
                $leftNormalized  = $map[$left]  ?? null;

                if ($rightNormalized || $leftNormalized) {
                    $result = [];
                    if ($rightNormalized && $rightNormalized !== 'その他') $result[] = $rightNormalized;
                    if ($leftNormalized  && $leftNormalized  !== 'その他') $result[] = $leftNormalized;
                    return count($result) > 0 ? $result : null;
                }

                // マップにないが右・左の形に展開できる場合はそのまま展開
                if (mb_strlen($base) >= 3) {
                    return [$right, $left];
                }
            }
        }

        return null;
    }
}
