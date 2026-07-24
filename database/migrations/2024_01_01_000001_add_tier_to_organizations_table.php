<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            // Tier: 1=ベーシック 2=アドバンス 3=エキスパート 4=マスター
            $table->tinyInteger('tier')->default(1)->after('id');

            // 昇格権利フラグ（年間件数が条件を満たしたら true になる）
            // エキスパート・マスターへの実際の昇格は管理者が手動で tier を変更する
            $table->boolean('tier3_eligible')->default(false)->after('tier')
                  ->comment('エキスパート昇格権利（年間90件以上で取得）');
            $table->boolean('tier4_eligible')->default(false)->after('tier3_eligible')
                  ->comment('マスター昇格権利（年間120件以上で取得）');
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn(['tier', 'tier3_eligible', 'tier4_eligible']);
        });
    }
};
