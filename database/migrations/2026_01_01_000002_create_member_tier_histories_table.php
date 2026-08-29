<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // organization_tier_histories と同一構造。member単位で持つ。
        Schema::create('member_tier_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->date('period_start');
            $table->date('period_end')->nullable(); // 旧テーブルの積み残し課題(nullable化)を踏襲
            $table->unsignedInteger('case_count')->default(0);
            $table->unsignedTinyInteger('tier')->default(1);
            $table->timestamps();

            $table->index(['member_id', 'period_start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_tier_histories');
    }
};
