<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 複数選択の回答（例：A,C,D）を保存できるよう長さを広げる
        // （元は char(1) で、2つ以上選ぶと Data too long エラーになっていた）
        Schema::table('elearning_attempt_answers', function (Blueprint $table) {
            $table->string('selected_answer', 50)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('elearning_attempt_answers', function (Blueprint $table) {
            $table->char('selected_answer', 1)->nullable()->change();
        });
    }
};
