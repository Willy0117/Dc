<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('elearning_questions', function (Blueprint $table) {
            // 'main'：既存の本試験（15問出題）用
            // 'simple'：契約前の簡易テスト用（変更点4）
            $table->enum('category', ['main', 'simple'])
                ->default('main')
                ->after('is_active');
        });

        // 既存の問題は全て本試験用として扱う（明示的に設定、defaultで既に'main'だが念のため）
        DB::table('elearning_questions')->update(['category' => 'main']);
    }

    public function down(): void
    {
        Schema::table('elearning_questions', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
};
