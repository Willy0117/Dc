<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\Video;
use App\Models\VideoSet;
use Illuminate\Database\Seeder;

class DemoVideoSetSeeder extends Seeder
{
    public function run(): void
    {
        $set = VideoSet::create([
            'name' => '安全管理者養成講習セット',
            'description' => "現場で必要な安全管理の基礎を学ぶ動画セットです。\n全3本の動画視聴後、確認テストに合格するとライセンス証が発行されます。",
            'price_jpy' => 15000,
            'stripe_price_id' => 'price_xxxxxxxxxxxxx', // Stripe Dashboardで作成したPrice IDに置き換え
            'passing_score' => 80,
            'active' => true,
        ]);

        $video1 = Video::create([
            'video_set_id' => $set->id,
            'title' => '第1章：安全管理の基本',
            'vimeo_id' => '000000001', // Vimeoの限定公開動画IDに置き換え
            'vimeo_hash' => 'aaaaaaaaaa', // 限定公開URLの h= パラメータ
            'sort_order' => 1,
        ]);

        Video::create([
            'video_set_id' => $set->id,
            'title' => '第2章：現場でのリスクアセスメント',
            'vimeo_id' => '000000002',
            'vimeo_hash' => 'bbbbbbbbbb',
            'sort_order' => 2,
        ]);

        Video::create([
            'video_set_id' => $set->id,
            'title' => '第3章：緊急時対応',
            'vimeo_id' => '000000003',
            'vimeo_hash' => 'cccccccccc',
            'sort_order' => 3,
        ]);

        $q1 = Question::create([
            'video_set_id' => $set->id,
            'question_text' => 'リスクアセスメントの目的として正しいものはどれですか？',
            'sort_order' => 1,
        ]);
        $q1->choices()->createMany([
            ['choice_text' => '作業効率を上げるため', 'is_correct' => false, 'sort_order' => 1],
            ['choice_text' => '潜在的な危険を特定し事故を未然に防ぐため', 'is_correct' => true, 'sort_order' => 2],
            ['choice_text' => 'コストを削減するため', 'is_correct' => false, 'sort_order' => 3],
        ]);

        $q2 = Question::create([
            'video_set_id' => $set->id,
            'question_text' => '緊急時にまず行うべきことはどれですか？',
            'sort_order' => 2,
        ]);
        $q2->choices()->createMany([
            ['choice_text' => '状況を確認し安全を確保する', 'is_correct' => true, 'sort_order' => 1],
            ['choice_text' => 'すぐにその場を離れる', 'is_correct' => false, 'sort_order' => 2],
            ['choice_text' => 'SNSに投稿する', 'is_correct' => false, 'sort_order' => 3],
        ]);
    }
}
