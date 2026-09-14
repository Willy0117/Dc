<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 変更点：form_fieldsと同様、form_optionsのtreatment_areaもENUMのままだと
        // 新しいカテゴリー（例：肩こり）を保存できないため、VARCHARに変更する。
        // 対応漏れの修正（2026_01_06_000002で他3テーブルは既に対応済み）。
        DB::statement("ALTER TABLE form_options MODIFY treatment_area VARCHAR(50) NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE form_options MODIFY treatment_area ENUM('手','足','肘','肩','膝','共通') NOT NULL");
    }
};
