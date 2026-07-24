<?php

namespace Database\Seeders;

use App\Models\FormOption;
use Illuminate\Database\Seeder;

class FormOptionSeeder extends Seeder
{
    public function run(): void
    {
        FormOption::truncate();

        $data = [
            // ──────────────────────────────────────────
            // 手
            // ──────────────────────────────────────────
            ['treatment_area' => '手', 'field_name' => '穿刺血管', 'labels' => [
                '右 橈骨動脈', '右 尺骨動脈', '左 橈骨動脈', '左 尺骨動脈',
                '右 上腕動脈', '左 上腕動脈', 'その他',
            ]],
            ['treatment_area' => '手', 'field_name' => '病名', 'labels' => [
                'ヘバーデン結節', 'ブシャール結節', 'CM関節症', 'バネ指（MP関節炎）',
                '腱鞘炎', 'TFCC損傷', '前回と同じ', 'その他',
            ]],
            ['treatment_area' => '手', 'field_name' => '右手疼痛部位', 'labels' => [
                '右第2DIP', '右第3DIP', '右第4DIP', '右第5DIP',
                '右第2PIP', '右第3PIP', '右第4PIP', '右第5PIP',
                '右母指CM', '右母指MP', '右母指DIP', '右TFCC',
                '前回と同じ', 'その他',
            ]],
            ['treatment_area' => '手', 'field_name' => '左手疼痛部位', 'labels' => [
                '左第2DIP', '左第3DIP', '左第4DIP', '左第5DIP',
                '左第2PIP', '左第3PIP', '左第4PIP', '左第5PIP',
                '左母指CM', '左母指MP', '左母指DIP', '左TFCC',
                '前回と同じ', 'その他',
            ]],

            // ──────────────────────────────────────────
            // 足
            // ──────────────────────────────────────────
            ['treatment_area' => '足', 'field_name' => '穿刺血管', 'labels' => [
                '右 PTA', '右 足背動脈', '左 PTA', '左 足背動脈', 'その他',
            ]],
            ['treatment_area' => '足', 'field_name' => '病名', 'labels' => [
                '足底腱膜炎', '有痛性外脛骨', '外反母趾', 'モートン病',
                'アキレス腱炎', '足関節滑膜炎', '足関節捻挫', '疲労骨折', 'その他',
            ]],
            ['treatment_area' => '足', 'field_name' => '駆血部位', 'labels' => [
                '中枢側', '末梢側', '駆血なし',
            ]],

            // ──────────────────────────────────────────
            // 肘
            // ──────────────────────────────────────────
            ['treatment_area' => '肘', 'field_name' => '穿刺血管', 'labels' => [
                '右上腕動脈', '左上腕動脈', 'その他',
            ]],
            ['treatment_area' => '肘', 'field_name' => '病名', 'labels' => [
                '内側上顆炎', 'MCL損傷', '外側上顆炎', '肘関節滑膜炎',
                'TFCC損傷', '腱鞘炎', '前回と同じ', 'その他',
            ]],
            ['treatment_area' => '肘', 'field_name' => '駆血部位', 'labels' => [
                '中枢側', '末梢側', '駆血なし',
            ]],

            // ──────────────────────────────────────────
            // 肩
            // ──────────────────────────────────────────
            ['treatment_area' => '肩', 'field_name' => '穿刺血管', 'labels' => [
                '右 鎖骨下動脈', '右 腋窩動脈', '左 鎖骨下動脈', '左 腋窩動脈',
                '右 頸横動脈', '左 頸横動脈', 'その他',
            ]],
            ['treatment_area' => '肩', 'field_name' => '病名', 'labels' => [
                '肩関節周囲炎', 'SIRVA', '腱板断裂', 'CTA', '前回と同じ', 'その他',
            ]],
            ['treatment_area' => '肩', 'field_name' => '駆血部位', 'labels' => [
                '中枢側', '末梢側', '駆血なし',
            ]],

            // ──────────────────────────────────────────
            // 膝
            // ──────────────────────────────────────────
            ['treatment_area' => '膝', 'field_name' => '穿刺血管', 'labels' => [
                '右鼠径動脈', '右膝窩動脈', '右足背動脈',
                '左鼠径動脈', '左膝窩動脈', '左足背動脈', 'その他',
            ]],
            ['treatment_area' => '膝', 'field_name' => '病名', 'labels' => [
                '変形性膝関節症', '膝蓋腱炎', '鵞足炎', '膝蓋下脂肪体炎',
                '腸脛骨靭帯炎', '膝蓋大腿靭帯炎', 'オスグッド病', 'シンスプリント',
                '関節リウマチ', '脛骨疲労骨折', '前回と同じ', 'その他',
            ]],
            ['treatment_area' => '膝', 'field_name' => '駆血部位', 'labels' => [
                '中枢側', '末梢側', '駆血なし',
            ]],

            // ──────────────────────────────────────────
            // 共通：トラブル・合併症
            // ──────────────────────────────────────────
            ['treatment_area' => '共通', 'field_name' => 'トラブル・合併症', 'labels' => [
                '疼痛部に明らかにチエナムが分布した',
                '疼痛部にチエナムが明らかに到達しなかった',
                '一部には到達して、一部には到達しなかった',
                '疼痛部にチエナムが到達したかが不明',
                '治療中の痛み強かった',
                '治療に時間がかかった',
                'アレルギー反応あり',
                '神経損傷',
                'その他',
            ]],
        ];

        foreach ($data as $group) {
            foreach ($group['labels'] as $i => $label) {
                FormOption::create([
                    'treatment_area' => $group['treatment_area'],
                    'field_name'     => $group['field_name'],
                    'label'          => $label,
                    'sort_order'     => $i,
                    'is_active'      => true,
                ]);
            }
        }

        $this->command->info('form_options: ' . FormOption::count() . '件 投入完了');
    }
}
