<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            // Tier本体（Organizationから移動）
            $table->unsignedTinyInteger('tier')->default(1)->after('organization_id');

            // 3.(氏名名寄せ)で「同一医師」と判定されたmember同士を束ねるためのキー。
            // デフォルトは自分自身のidを入れ、名寄せ確定時のみ他のmemberのidに揃える。
            // これにより8.(複数病院掛け持ち時の合算)のTier集計が
            // doctor_group_id単位でのSUMになる。
            $table->unsignedBigInteger('doctor_group_id')->nullable()->after('tier');

            $table->index('doctor_group_id');
        });

        // 既存データのbackfill: doctor_group_id を自分自身のidで初期化
        \DB::statement('UPDATE members SET doctor_group_id = id WHERE doctor_group_id IS NULL');
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropIndex(['doctor_group_id']);
            $table->dropColumn(['tier', 'doctor_group_id']);
        });
    }
};
