<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            // S3バケット内のファイルパス（例: videos/A01.mp4）。ダウンロード機能で使用。
            $table->string('s3_key')->nullable()->after('vimeo_hash');
        });
    }

    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->dropColumn('s3_key');
        });
    }
};
