<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->string('speaker_name')->nullable()->after('title'); // 講師名・所属
            $table->unsignedInteger('duration_minutes')->nullable()->after('speaker_name');
        });

        Schema::table('video_sets', function (Blueprint $table) {
            $table->string('theme')->nullable()->after('name'); // 例：基礎知識・ヒューマンエラー・質改善
            $table->string('category')->nullable()->after('theme'); // 例：医療安全の基本的知識 / 安全管理体制の構築
        });
    }

    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->dropColumn(['speaker_name', 'duration_minutes']);
        });
        Schema::table('video_sets', function (Blueprint $table) {
            $table->dropColumn(['theme', 'category']);
        });
    }
};
