<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 新しい詳細テーブル
        Schema::create('case_report_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_report_id')->constrained()->cascadeOnDelete();
            $table->enum('treatment_area', ['手', '足', '肘', '肩', '膝']);
            $table->json('data')->comment('フィールド名 => 値のJSON');
            $table->timestamps();

            $table->index('case_report_id');
        });

        // 既存データを移行
        $this->migrateHand();
        $this->migrateFoot();
        $this->migrateElbow();
        $this->migrateShoulder();
        $this->migrateKnee();
    }

    private function migrateHand(): void
    {
        $rows = DB::table('case_hand_details')->get();
        foreach ($rows as $row) {
            DB::table('case_report_details')->insert([
                'case_report_id' => $row->case_report_id,
                'treatment_area' => '手',
                'data'           => json_encode([
                    '穿刺血管'     => json_decode($row->puncture_vessels ?? '[]', true),
                    '病名'         => json_decode($row->disease_names ?? '[]', true),
                    '右手疼痛部位' => json_decode($row->right_pain_area_list ?? '[]', true),
                    '左手疼痛部位' => json_decode($row->left_pain_area_list ?? '[]', true),
                ], JSON_UNESCAPED_UNICODE),
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }
        echo "手: {$rows->count()}件 移行完了\n";
    }

    private function migrateFoot(): void
    {
        $rows = DB::table('case_foot_details')->get();
        foreach ($rows as $row) {
            DB::table('case_report_details')->insert([
                'case_report_id' => $row->case_report_id,
                'treatment_area' => '足',
                'data'           => json_encode([
                    '穿刺血管' => json_decode($row->puncture_vessels ?? '[]', true),
                    '病名'     => json_decode($row->disease_names ?? '[]', true),
                    '駆血部位' => $row->tourniquet_position,
                ], JSON_UNESCAPED_UNICODE),
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }
        echo "足: {$rows->count()}件 移行完了\n";
    }

    private function migrateElbow(): void
    {
        $rows = DB::table('case_elbow_details')->get();
        foreach ($rows as $row) {
            DB::table('case_report_details')->insert([
                'case_report_id' => $row->case_report_id,
                'treatment_area' => '肘',
                'data'           => json_encode([
                    '穿刺血管' => json_decode($row->puncture_vessels ?? '[]', true),
                    '病名'     => json_decode($row->disease_names ?? '[]', true),
                    '駆血部位' => $row->tourniquet_position,
                ], JSON_UNESCAPED_UNICODE),
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }
        echo "肘: {$rows->count()}件 移行完了\n";
    }

    private function migrateShoulder(): void
    {
        $rows = DB::table('case_shoulder_details')->get();
        foreach ($rows as $row) {
            DB::table('case_report_details')->insert([
                'case_report_id' => $row->case_report_id,
                'treatment_area' => '肩',
                'data'           => json_encode([
                    '穿刺血管' => json_decode($row->puncture_vessels ?? '[]', true),
                    '病名'     => json_decode($row->disease_names ?? '[]', true),
                    '駆血部位' => $row->tourniquet_position,
                ], JSON_UNESCAPED_UNICODE),
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }
        echo "肩: {$rows->count()}件 移行完了\n";
    }

    private function migrateKnee(): void
    {
        $rows = DB::table('case_knee_details')->get();
        foreach ($rows as $row) {
            DB::table('case_report_details')->insert([
                'case_report_id' => $row->case_report_id,
                'treatment_area' => '膝',
                'data'           => json_encode([
                    '穿刺血管' => json_decode($row->puncture_vessels ?? '[]', true),
                    '病名'     => json_decode($row->disease_names ?? '[]', true),
                    '駆血部位' => $row->tourniquet_position,
                ], JSON_UNESCAPED_UNICODE),
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }
        echo "膝: {$rows->count()}件 移行完了\n";
    }

    public function down(): void
    {
        Schema::dropIfExists('case_report_details');
    }
};
