<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {

            // 追加
            $table->string('last_name')->after('id');
            $table->string('first_name')->after('last_name');

            // 削除
            $table->dropColumn('deceased_name');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {

            // 戻す
            $table->string('deceased_name')->after('id');

            $table->dropColumn([
                'last_name',
                'first_name'
            ]);
        });
    }
};