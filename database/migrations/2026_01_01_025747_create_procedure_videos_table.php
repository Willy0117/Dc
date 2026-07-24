<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procedure_videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->string('title')->nullable()->comment('動画タイトル');
            $table->string('file_path')->comment('動画ファイルパス（S3キー）');
            $table->string('thumbnail_path')->nullable()->comment('サムネイル画像パス（未使用・将来用）');
            $table->unsignedBigInteger('file_size')->nullable()->comment('ファイルサイズ(byte)');
            $table->unsignedBigInteger('uploaded_by')->nullable()->comment('アップロードしたUserのid');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procedure_videos');
    }
};