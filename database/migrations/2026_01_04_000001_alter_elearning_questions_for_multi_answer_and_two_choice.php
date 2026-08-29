<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('elearning_questions', function (Blueprint $table) {
            // ①複数正解対応（mainカテゴリのみ使用想定）：
            // 'A' のような単一文字から、'A,C' のようなカンマ区切り複数文字を
            // 保持できるよう型を拡張する。既存データ（単一文字）はそのまま有効。
            $table->string('correct_answer', 20)->comment('A/B/C/D。複数正解の場合は "A,C" のようにカンマ区切り')->change();

            // ②2択対応（simpleカテゴリのみ使用想定）：
            // choice_c・choice_d を必須から任意に変更。
            // NULLの場合、その選択肢は出題されない（○×等の2択問題に対応）。
            $table->string('choice_c')->nullable()->change();
            $table->string('choice_d')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('elearning_questions', function (Blueprint $table) {
            $table->char('correct_answer', 1)->comment('A/B/C/D')->change();
            $table->string('choice_c')->nullable(false)->change();
            $table->string('choice_d')->nullable(false)->change();
        });
    }
};
