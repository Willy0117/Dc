<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('case_report_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique(); // 例：手、足、肘、肩、膝、肩こり、頭痛
            $table->unsignedTinyInteger('required_tier')->default(1); // このグレード以上の先生のみ選択可能
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 既存の5部位を初期データとして投入（全員が使えるようrequired_tier=1）
        $now = now();
        DB::table('case_report_categories')->insert([
            ['name' => '手', 'required_tier' => 1, 'sort_order' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => '足', 'required_tier' => 1, 'sort_order' => 2, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => '肘', 'required_tier' => 1, 'sort_order' => 3, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => '肩', 'required_tier' => 1, 'sort_order' => 4, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => '膝', 'required_tier' => 1, 'sort_order' => 5, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('case_report_categories');
    }
};
