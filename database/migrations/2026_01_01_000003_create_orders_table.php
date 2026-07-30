<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('video_set_id')->constrained();
            $table->string('customer_name')->nullable();
            $table->string('customer_email');
            $table->string('stripe_checkout_session_id')->unique();
            $table->string('stripe_payment_intent')->nullable();
            $table->string('status')->default('pending'); // pending / paid / refunded
            $table->string('access_token', 64)->unique(); // 視聴サイトへのURLトークン
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
