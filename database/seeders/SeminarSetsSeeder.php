<?php

namespace Database\Seeders;

use App\Models\Video;
use App\Models\VideoSet;
use Illuminate\Database\Seeder;

class SeminarSetsSeeder extends Seeder
{
    public function run(): void
    {
        $sets = [
            [
                'name' => 'A',
                'category' => '医療安全の基本的知識',
                'theme' => '基礎知識・ヒューマンエラー・質改善',
                'stripe_price_id' => 'price_XXXXXXXXXXXXXXXA', // Stripeで作成後に差し替え
                'lectures' => [
                    ['title' => '総論（２）医療安全の歴史から理解する基本知識（１・２）', 'speaker_name' => '菊地 龍明（横浜市立大学附属病院）', 'duration_minutes' => 90, 'vimeo_id' => '1208338425', 'vimeo_hash' => '62a9349ef0', 's3_key' => 'videos/A01.mp4'],
                    ['title' => '安全管理に求められる知識（３）ヒューマンエラー防止の心理学', 'speaker_name' => '石松 一真（滋慶医療科学大学大学院）', 'duration_minutes' => 60, 'vimeo_id' => '1208338451', 'vimeo_hash' => '17cf9550d6', 's3_key' => 'videos/A02.mp4'],
                    ['title' => '安全管理に求められる知識（４）レジリエンス・エンジニアリング', 'speaker_name' => '中島 和江（大阪大学医学部附属病院）', 'duration_minutes' => 60, 'vimeo_id' => '1208338463', 'vimeo_hash' => '0c84b8ee43', 's3_key' => 'videos/A03.mp4'],
                    ['title' => '医療の質・安全を確保する仕組み（３）質改善・品質管理', 'speaker_name' => '山口 悦子（国際医療福祉大学大学院）', 'duration_minutes' => 45, 'vimeo_id' => '1208338480', 'vimeo_hash' => '6681be8152', 's3_key' => 'videos/A04.mp4'],
                    ['title' => '医療の質・安全を確保する仕組み（４）業務プロセスの整備', 'speaker_name' => '山口 悦子（国際医療福祉大学大学院）', 'duration_minutes' => 60, 'vimeo_id' => '1208338670', 'vimeo_hash' => 'd0e85cf091', 's3_key' => 'videos/A05.mp4'],
                ],
            ],
            [
                'name' => 'B',
                'category' => '医療安全の基本的知識',
                'theme' => '制度・事業・地域連携',
                'stripe_price_id' => 'price_XXXXXXXXXXXXXXXB',
                'lectures' => [
                    ['title' => '総論（１）医療安全施策の動向', 'speaker_name' => '厚生労働省 医療安全推進・医務指導室', 'duration_minutes' => 30, 'vimeo_id' => '1208338825', 'vimeo_hash' => '1f5e9a136e', 's3_key' => 'videos/B06.mp4'],
                    ['title' => '医療安全に関わる制度（１）医療事故情報収集等事業', 'speaker_name' => '坂口 美佐（日本医療機能評価機構）', 'duration_minutes' => 45, 'vimeo_id' => '1208338942', 'vimeo_hash' => '8ed99779c3', 's3_key' => 'videos/B07.mp4'],
                    ['title' => '医療安全に関わる制度（２）医薬品副作用被害救済制度', 'speaker_name' => '本多 晃一（医薬品医療機器総合機構）', 'duration_minutes' => 30, 'vimeo_id' => '1208339039', 'vimeo_hash' => '8398bc6052', 's3_key' => 'videos/B08.mp4'],
                    ['title' => '医療安全に関わる制度（３）医療事故調査制度', 'speaker_name' => '宮田 哲郎（日本医療安全調査機構）', 'duration_minutes' => 30, 'vimeo_id' => '1208339072', 'vimeo_hash' => 'f52364c680', 's3_key' => 'videos/B09.mp4'],
                    ['title' => '安全管理に求められる知識（８）労働衛生', 'speaker_name' => '亀田 義人（順天堂大学大学院医学研究科）', 'duration_minutes' => 30, 'vimeo_id' => '1208339190', 'vimeo_hash' => 'e5bdbdfa73', 's3_key' => 'videos/B10.mp4'],
                    ['title' => '医療の質・安全を確保する仕組み（５）地域連携', 'speaker_name' => '菅野 隆彦（下伊那厚生病院）', 'duration_minutes' => 45, 'vimeo_id' => '1208339194', 'vimeo_hash' => '3d209589e8', 's3_key' => 'videos/B11.mp4'],
                ],
            ],
            [
                'name' => 'C',
                'category' => '安全管理体制の構築',
                'theme' => 'リーダーシップ・役割・実践',
                'stripe_price_id' => 'price_XXXXXXXXXXXXXXXC',
                'lectures' => [
                    ['title' => '総論（３）医療安全管理者のリーダーシップ', 'speaker_name' => '高田 誠（株式会社オーセンティックス）', 'duration_minutes' => 60, 'vimeo_id' => '1208339267', 'vimeo_hash' => '14c915e9c6', 's3_key' => 'videos/C12.mp4'],
                    ['title' => '総論（４）医療安全管理者に求められる能力と役割', 'speaker_name' => '甲斐 由紀子（宮崎大学）', 'duration_minutes' => 45, 'vimeo_id' => '1208339294', 'vimeo_hash' => 'f3d7381fe7', 's3_key' => 'videos/C13.mp4'],
                    ['title' => '安全管理に求められる知識（５）チーム医療と心理的安全性', 'speaker_name' => '辰巳 陽一（近畿大学病院）', 'duration_minutes' => 120, 'vimeo_id' => '1208339319', 'vimeo_hash' => '0733f0ba9e', 's3_key' => 'videos/C14.mp4'],
                    // 以下2件、Vimeo ID未着のため仮値のまま（届き次第 vimeo_id / vimeo_hash を差し替え）
                    ['title' => '医療安全管理の実際（２）医療安全管理者としての実践', 'speaker_name' => '荒井 有美（北里大学病院）', 'duration_minutes' => 30, 'vimeo_id' => 'TODO_C_4', 'vimeo_hash' => null, 's3_key' => 'videos/C15.mp4'],
                    ['title' => '医療安全管理の実際（３）少人数体制で実践する医療安全管理', 'speaker_name' => '遠田 光子（日本医療機能評価機構）', 'duration_minutes' => 15, 'vimeo_id' => 'TODO_C_5', 'vimeo_hash' => null, 's3_key' => 'videos/C16.mp4'],
                ],
            ],
            [
                'name' => 'D',
                'category' => '安全管理体制の構築',
                'theme' => '組織的な安全対策',
                'stripe_price_id' => 'price_XXXXXXXXXXXXXXXD',
                'lectures' => [
                    ['title' => '組織的な安全対策（３）医療機器の安全管理/医療機器安全管理者との連携', 'speaker_name' => '本田 靖雅（聖マリア病院）', 'duration_minutes' => 45, 'vimeo_id' => '1208339492', 'vimeo_hash' => '3c189b19b0', 's3_key' => 'videos/D17.mp4'],
                    ['title' => '組織的な安全対策（４）医薬品の安全管理/医薬品安全管理責任者との連携', 'speaker_name' => '德和目 篤史（大阪公立大学医学部附属病院）', 'duration_minutes' => 45, 'vimeo_id' => '1208339591', 'vimeo_hash' => '11aae9eefd', 's3_key' => 'videos/D18.mp4'],
                    ['title' => '組織的な安全対策（５）診療用放射線の安全管理/放射線安全管理責任者との連携', 'speaker_name' => '菊地 克彦（東京北医療センター）', 'duration_minutes' => 45, 'vimeo_id' => '1208339649', 'vimeo_hash' => '981c958144', 's3_key' => 'videos/D19.mp4'],
                    ['title' => '組織的な安全対策（６）診療情報と安全管理/診療情報管理士との連携', 'speaker_name' => '荒井 康夫（北里大学）', 'duration_minutes' => 45, 'vimeo_id' => '1208339784', 'vimeo_hash' => '6ade57cc15', 's3_key' => 'videos/D20.mp4'],
                    ['title' => '組織的な安全対策（７）高難度新規医療技術', 'speaker_name' => '中村 京太（横浜市立大学市民総合医療センター）', 'duration_minutes' => 30, 'vimeo_id' => '1208339801', 'vimeo_hash' => '55e07181de', 's3_key' => 'videos/D21.mp4'],
                ],
            ],
        ];

        foreach ($sets as $setData) {
            $videoSet = VideoSet::create([
                'name' => $setData['name'],
                'category' => $setData['category'],
                'theme' => $setData['theme'],
                'description' => "{$setData['category']}（{$setData['theme']}）",
                'price_jpy' => 9900,
                'stripe_price_id' => $setData['stripe_price_id'],
                'passing_score' => 100,
                'active' => true,
            ]);

            foreach ($setData['lectures'] as $i => $lecture) {
                Video::create([
                    'video_set_id' => $videoSet->id,
                    'title' => $lecture['title'],
                    'speaker_name' => $lecture['speaker_name'],
                    'duration_minutes' => $lecture['duration_minutes'],
                    'vimeo_id' => $lecture['vimeo_id'],
                    'vimeo_hash' => $lecture['vimeo_hash'],
                    's3_key' => $lecture['s3_key'],
                    'sort_order' => $i + 1,
                ]);
            }
        }
    }
}