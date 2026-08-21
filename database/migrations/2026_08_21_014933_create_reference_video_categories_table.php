<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. カテゴリーマスタテーブルを新設
        Schema::create('reference_video_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // 2. 既存の固定カテゴリー（全体/手/足/肘/肩/膝）を初期データとして投入
        $defaultCategories = ['全体', '手', '足', '肘', '肩', '膝'];
        foreach ($defaultCategories as $index => $name) {
            DB::table('reference_video_categories')->insert([
                'name'       => $name,
                'sort_order' => $index + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 3. reference_videos に category_id を追加
        Schema::table('reference_videos', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('id')
                ->constrained('reference_video_categories')->nullOnDelete();
        });

        // 4. 既存の category(文字列) から category_id へデータ移行
        $categoryMap = DB::table('reference_video_categories')->pluck('id', 'name');
        foreach ($categoryMap as $name => $id) {
            DB::table('reference_videos')->where('category', $name)->update(['category_id' => $id]);
        }

        // 5. 旧カラム category を削除
        Schema::table('reference_videos', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }

    public function down(): void
    {
        Schema::table('reference_videos', function (Blueprint $table) {
            $table->string('category')->nullable()->after('id');
        });

        $categoryMap = DB::table('reference_video_categories')->pluck('name', 'id');
        foreach ($categoryMap as $id => $name) {
            DB::table('reference_videos')->where('category_id', $id)->update(['category' => $name]);
        }

        Schema::table('reference_videos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
        });

        Schema::dropIfExists('reference_video_categories');
    }
};
