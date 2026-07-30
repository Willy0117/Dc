<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('video_sets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedInteger('price_jpy'); // 表示用（実際の金額はStripe側のPriceで管理）
            $table->string('stripe_price_id'); // Stripe Dashboardで作成したPrice ID
            $table->unsignedInteger('passing_score')->default(80); // 合格ライン(%)
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_sets');
    }
};
