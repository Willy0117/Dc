<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('annual_fees', function (Blueprint $table) {
            $table->id();

            // 会員
            $table->foreignId('member_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // 年度
            $table->year('fiscal_year');

            // 金額
            $table->integer('annual_fee')->default(0);
            $table->integer('renewal_fee')->default(0);
            $table->integer('payment_amount')->default(0);

            // 入金日
            $table->date('payment_date')->nullable();

            // ステータス
            $table->string('status')->default('unpaid');

            $table->timestamps();

            // インデックス
            $table->index(['member_id', 'fiscal_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('annual_fees');
    }
};