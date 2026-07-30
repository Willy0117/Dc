<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 設問を動画(講義)単位に紐づける
        Schema::table('questions', function (Blueprint $table) {
            $table->foreignId('video_id')->nullable()->after('video_set_id')->constrained()->cascadeOnDelete();
        });

        // 受験記録を動画(講義)単位に紐づける
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->foreignId('video_id')->nullable()->after('order_id')->constrained();
        });

        // 証明書を動画(講義)単位に紐づける
        Schema::table('certificates', function (Blueprint $table) {
            $table->foreignId('video_id')->nullable()->after('order_id')->constrained();
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('video_id');
        });
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('video_id');
        });
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropConstrainedForeignId('video_id');
        });
    }
};
