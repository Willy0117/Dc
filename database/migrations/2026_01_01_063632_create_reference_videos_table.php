<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reference_videos', function (Blueprint $table) {
            $table->id();
            $table->string('category', 10)->comment('全体/手/足/肘/肩/膝');
            $table->string('title');
            $table->string('youtube_url');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reference_videos');
    }
};
