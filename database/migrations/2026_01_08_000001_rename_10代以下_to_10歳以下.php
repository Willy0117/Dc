<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 変更点：症例報告の年代「10代以下」を「10歳以下」にリネームする。
        // ENUMから値を安全に置き換えるため、以下の3段階で行う。
        // 1. 新しい値「10歳以下」をENUMに追加（一時的に新旧両方が使える状態にする）
        // 2. 既存データの「10代以下」を「10歳以下」に更新
        // 3. ENUMから古い値「10代以下」を除去

        DB::statement("ALTER TABLE case_reports MODIFY patient_age_group ENUM('10代以下','10歳以下','10代','20代','30代','40代','50代','60代','70代','80代','90代以上') DEFAULT NULL");

        DB::table('case_reports')
            ->where('patient_age_group', '10代以下')
            ->update(['patient_age_group' => '10歳以下']);

        DB::statement("ALTER TABLE case_reports MODIFY patient_age_group ENUM('10歳以下','10代','20代','30代','40代','50代','60代','70代','80代','90代以上') DEFAULT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE case_reports MODIFY patient_age_group ENUM('10歳以下','10代以下','10代','20代','30代','40代','50代','60代','70代','80代','90代以上') DEFAULT NULL");

        DB::table('case_reports')
            ->where('patient_age_group', '10歳以下')
            ->update(['patient_age_group' => '10代以下']);

        DB::statement("ALTER TABLE case_reports MODIFY patient_age_group ENUM('10代以下','10代','20代','30代','40代','50代','60代','70代','80代','90代以上') DEFAULT NULL");
    }
};
