<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();

            // SaaS分離
            $table->foreignId('organization_id')
                ->constrained()
                ->cascadeOnDelete();

            // 日付
            $table->date('application_date')->nullable();
            $table->date('delivery_date')->nullable();
            $table->dateTime('vigil_datetime')->nullable();
            $table->dateTime('funeral_datetime')->nullable();

            $table->string('staff_name')->nullable();

            // 故人
            $table->string('deceased_name');
            $table->string('deceased_furigana')->nullable();
            $table->unsignedTinyInteger('age_at_death')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();

            // 配偶者状態
            $table->enum('spouse_status', ['none', 'alive', 'deceased'])->nullable();

            // 家族（軽量）
            $table->unsignedTinyInteger('children_count')->nullable();
            $table->unsignedTinyInteger('grandchildren_count')->nullable();

            // 喪主
            $table->string('chief_mourner_name')->nullable();
            $table->string('relationship_to_deceased')->nullable();

            // 性格（チェック項目）
            $table->json('traits')->nullable();

            $table->text('special_notes')->nullable();
            $table->string('text_color')->nullable();
            $table->string('bg_color')->nullable();
            $table->text('remarks')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};