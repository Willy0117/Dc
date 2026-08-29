<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 新規member登録時、氏名が一致する既存memberが見つかった場合に
        // 自動生成される「要確認」候補ペア。管理者がここを見て
        // 同一人物かどうかを判断し、doctor_group_idの統合を行う。
        Schema::create('member_name_match_candidates', function (Blueprint $table) {
            $table->id();

            // 新規登録された側
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            // 氏名が一致した既存側
            $table->foreignId('matched_member_id')->constrained('members')->cascadeOnDelete();

            $table->string('matched_name'); // 判定時点の正規化済み氏名（監査用に保存）

            // pending: 未確認 / confirmed: 同一人物として統合済み / rejected: 別人と判断
            $table->enum('status', ['pending', 'confirmed', 'rejected'])->default('pending');

            $table->foreignId('reviewed_by')->nullable()->constrained('admins'); // 管理者テーブル名は要確認
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();

            $table->unique(['member_id', 'matched_member_id']); // 同じペアを二重生成しない
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_name_match_candidates');
    }
};
