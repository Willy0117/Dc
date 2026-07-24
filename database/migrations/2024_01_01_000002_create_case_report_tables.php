<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ----------------------------------------
        // 症例報告（共通）
        // ----------------------------------------
        Schema::create('case_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->string('facility_name_raw')->comment('CSVの施設名（マッチング前の生値）');
            $table->enum('patient_gender', ['男性', '女性', '不明'])->nullable();
            $table->enum('patient_age_group', [
                '10代以下', '10代', '20代', '30代', '40代',
                '50代', '60代', '70代', '80代', '90代以上'
            ])->nullable();
            $table->enum('treatment_area', ['手', '足', '肘', '肩', '膝']);
            $table->text('complications')->nullable()->comment('トラブル・合併症');
            $table->text('notes')->nullable()->comment('上記の詳細特記事項');
            $table->timestamp('submitted_at')->nullable();
            $table->integer('csv_row')->nullable()->comment('インポート元CSVの行番号');
            $table->timestamps();
        });

        // ----------------------------------------
        // 手の詳細
        // ----------------------------------------
        Schema::create('case_hand_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_report_id')->constrained()->cascadeOnDelete();
            $table->string('puncture_vessel')->nullable()->comment('穿刺血管');
            $table->string('disease_name')->nullable()->comment('病名');
            $table->text('right_pain_areas')->nullable()->comment('右手疼痛部位');
            $table->text('left_pain_areas')->nullable()->comment('左手疼痛部位');
            $table->timestamps();
        });

        // ----------------------------------------
        // 足の詳細
        // ----------------------------------------
        Schema::create('case_foot_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_report_id')->constrained()->cascadeOnDelete();
            $table->string('puncture_vessel')->nullable()->comment('穿刺血管');
            $table->string('disease_name')->nullable()->comment('病名');
            $table->enum('tourniquet_position', ['中枢側', '末梢側', '駆血なし'])->nullable()->comment('駆血部位');
            $table->timestamps();
        });

        // ----------------------------------------
        // 肘の詳細
        // ----------------------------------------
        Schema::create('case_elbow_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_report_id')->constrained()->cascadeOnDelete();
            $table->string('puncture_vessel')->nullable()->comment('穿刺血管');
            $table->string('disease_name')->nullable()->comment('病名');
            $table->enum('tourniquet_position', ['中枢側', '末梢側', '駆血なし'])->nullable()->comment('駆血部位');
            $table->timestamps();
        });

        // ----------------------------------------
        // 肩の詳細
        // ----------------------------------------
        Schema::create('case_shoulder_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_report_id')->constrained()->cascadeOnDelete();
            $table->string('puncture_vessel')->nullable()->comment('穿刺血管');
            $table->string('disease_name')->nullable()->comment('病名');
            $table->enum('tourniquet_position', ['中枢側', '末梢側', '駆血なし'])->nullable()->comment('駆血部位');
            $table->timestamps();
        });

        // ----------------------------------------
        // 膝の詳細
        // ----------------------------------------
        Schema::create('case_knee_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_report_id')->constrained()->cascadeOnDelete();
            $table->string('puncture_vessel')->nullable()->comment('穿刺血管');
            $table->string('disease_name')->nullable()->comment('病名');
            $table->enum('tourniquet_position', ['中枢側', '末梢側', '駆血なし'])->nullable()->comment('駆血部位');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_knee_details');
        Schema::dropIfExists('case_shoulder_details');
        Schema::dropIfExists('case_elbow_details');
        Schema::dropIfExists('case_foot_details');
        Schema::dropIfExists('case_hand_details');
        Schema::dropIfExists('case_reports');
    }
};
