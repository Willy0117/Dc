<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->string('facility_name')->nullable();

            $table->integer('age')->nullable();
            $table->string('gender')->nullable();
            $table->string('visit_type')->nullable();

            $table->text('diagnosis')->nullable();
            $table->text('current_history')->nullable();
            $table->text('past_history')->nullable();

            $table->text('rehab_program')->nullable();
            $table->text('future_plan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
