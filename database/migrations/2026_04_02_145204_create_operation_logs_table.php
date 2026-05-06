<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('operation_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(); // 操作ユーザー
            $table->string('action'); // create, update, delete, login など
            $table->string('model_type')->nullable(); // 対象モデル
            $table->unsignedBigInteger('model_id')->nullable(); // 対象ID
            $table->json('before')->nullable(); // 変更前
            $table->json('after')->nullable();  // 変更後
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operation_logs');
    }
};
