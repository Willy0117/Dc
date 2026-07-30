<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('video_set_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('vimeo_id');       // 例: 123456789
            $table->string('vimeo_hash')->nullable(); // 限定公開URLの h= パラメータ
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
