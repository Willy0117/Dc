<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('rehab_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // 自己申告フォーム
            $table->string('facility');
            $table->integer('age');
            $table->enum('gender', ['male','female','other']);
            $table->enum('visit_type', ['outpatient','inpatient']);
            $table->string('diagnosis');
            $table->text('current_history');
            $table->text('past_history')->nullable();
            $table->text('rehab')->nullable();
            $table->text('future_plan')->nullable();

            // PDFファイル
            $table->string('recommendation_pdf')->nullable();
            $table->string('clinical_report_1')->nullable();
            $table->string('clinical_report_2')->nullable();

            // 申込ステータス
            $table->enum('status', ['draft','submitted','rejected','approved'])->default('draft');
            $table->text('reject_message')->nullable(); // 差し戻し理由

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('rehab_applications');
    }
};

