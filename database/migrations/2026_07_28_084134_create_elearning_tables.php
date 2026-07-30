<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 問題マスタ
        Schema::create('elearning_questions', function (Blueprint $table) {
            $table->id();
            $table->text('question');
            $table->string('choice_a');
            $table->string('choice_b');
            $table->string('choice_c');
            $table->string('choice_d');
            $table->char('correct_answer', 1)->comment('A/B/C/D');
            $table->text('explanation')->nullable();
            $table->boolean('is_active')->default(true)->comment('出題対象にするかどうか');
            $table->timestamps();
        });

        // 受験セッション（1回のテスト実施）
        Schema::create('elearning_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('member_id')->constrained()->onDelete('cascade');
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->unsignedTinyInteger('total_questions')->default(15);
            $table->unsignedTinyInteger('correct_count')->nullable();
            $table->boolean('is_passed')->nullable();
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('submitted_at')->nullable();
            // 契約開始日を基準にした「何期目」かを表す期間キー（例: 2026-1, 2026-2）
            $table->string('period_key', 20);
            $table->timestamps();

            $table->index(['member_id', 'period_key']);
        });

        // 出題された設問と回答内容（1受験あたり15件）
        Schema::create('elearning_attempt_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained('elearning_attempts')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('elearning_questions')->onDelete('cascade');
            $table->unsignedTinyInteger('sort_order')->comment('出題順');
            $table->char('selected_answer', 1)->nullable()->comment('A/B/C/D');
            $table->boolean('is_correct')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('elearning_attempt_answers');
        Schema::dropIfExists('elearning_attempts');
        Schema::dropIfExists('elearning_questions');
    }
};
