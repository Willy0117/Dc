<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ENUMのままだと新しいカテゴリー名（例：肩こり、頭痛）を保存できないため、
        // VARCHARに変更する。既存の値（手・足・肘・肩・膝）はそのまま有効。
        DB::statement("ALTER TABLE case_reports MODIFY treatment_area VARCHAR(50) NOT NULL");
        DB::statement("ALTER TABLE case_report_details MODIFY treatment_area VARCHAR(50) NOT NULL");
        DB::statement("ALTER TABLE form_fields MODIFY treatment_area VARCHAR(50) NOT NULL COMMENT '治療部位'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE case_reports MODIFY treatment_area ENUM('手','足','肘','肩','膝') NOT NULL");
        DB::statement("ALTER TABLE case_report_details MODIFY treatment_area ENUM('手','足','肘','肩','膝') NOT NULL");
        DB::statement("ALTER TABLE form_fields MODIFY treatment_area ENUM('手','足','肘','肩','膝','共通') NOT NULL COMMENT '治療部位'");
    }
};
