<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resource_documents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('file_path')->comment('資料ファイルパス');
            $table->string('original_filename')->nullable()->comment('元のファイル名（拡張子判定・表示用）');
            $table->unsignedBigInteger('file_size')->nullable()->comment('ファイルサイズ(byte)');
            $table->unsignedBigInteger('uploaded_by')->nullable()->comment('アップロードした管理者Userのid');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resource_documents');
    }
};
