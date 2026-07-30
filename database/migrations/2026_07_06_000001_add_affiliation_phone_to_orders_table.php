<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('affiliation')->nullable()->after('customer_name'); // 所属（例：〇〇病院 医療安全管理室）
            $table->string('phone', 30)->nullable()->after('affiliation');     // TEL
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['affiliation', 'phone']);
        });
    }
};
