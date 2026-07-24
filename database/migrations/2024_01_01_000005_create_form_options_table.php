<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_options', function (Blueprint $table) {
            $table->id();
            $table->enum('treatment_area', ['手', '足', '肘', '肩', '膝', '共通'])
                  ->comment('治療部位（共通はトラブル・合併症など）');
            $table->string('field_name')->comment('フィールド名（穿刺血管/病名/疼痛部位/駆血部位/トラブル）');
            $table->string('label')->comment('表示名');
            $table->unsignedSmallInteger('sort_order')->default(0)->comment('並び順');
            $table->boolean('is_active')->default(true)->comment('表示/非表示');
            $table->timestamps();

            $table->index(['treatment_area', 'field_name', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_options');
    }
};
