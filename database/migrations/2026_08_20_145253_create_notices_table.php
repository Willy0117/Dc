<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notices', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('youtube_url')->nullable();
            $table->dateTime('video_available_from')->nullable();
            $table->dateTime('video_available_until')->nullable();
            $table->string('target_type')->default('all');
            $table->dateTime('published_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('notice_organization', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('notice_member', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('notice_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->dateTime('viewed_at');
            $table->timestamps();

            $table->unique(['notice_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notice_views');
        Schema::dropIfExists('notice_member');
        Schema::dropIfExists('notice_organization');
        Schema::dropIfExists('notices');
    }
};
