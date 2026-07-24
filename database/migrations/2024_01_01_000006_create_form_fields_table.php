<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // form_fields テーブル作成
        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();
            $table->enum('treatment_area', ['手', '足', '肘', '肩', '膝', '共通'])
                  ->comment('治療部位');
            $table->string('field_name')->comment('質問名（例：穿刺血管、病名）');
            $table->enum('field_type', ['checkbox', 'radio', 'text'])
                  ->default('checkbox')
                  ->comment('入力タイプ');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['treatment_area', 'sort_order']);
        });

        // form_options に form_field_id を追加
        Schema::table('form_options', function (Blueprint $table) {
            $table->foreignId('form_field_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('form_fields')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('form_options', function (Blueprint $table) {
            $table->dropForeign(['form_field_id']);
            $table->dropColumn('form_field_id');
        });
        Schema::dropIfExists('form_fields');
    }
};
