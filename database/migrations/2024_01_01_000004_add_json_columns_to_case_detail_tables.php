<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 手：複数選択をJSONカラムに追加
        Schema::table('case_hand_details', function (Blueprint $table) {
            $table->json('puncture_vessels')->nullable()->after('puncture_vessel')->comment('穿刺血管（JSON）');
            $table->json('disease_names')->nullable()->after('disease_name')->comment('病名（JSON）');
            $table->json('right_pain_area_list')->nullable()->after('right_pain_areas')->comment('右手疼痛部位（JSON）');
            $table->json('left_pain_area_list')->nullable()->after('left_pain_areas')->comment('左手疼痛部位（JSON）');
        });

        // 足
        Schema::table('case_foot_details', function (Blueprint $table) {
            $table->json('puncture_vessels')->nullable()->after('puncture_vessel')->comment('穿刺血管（JSON）');
            $table->json('disease_names')->nullable()->after('disease_name')->comment('病名（JSON）');
        });

        // 肘
        Schema::table('case_elbow_details', function (Blueprint $table) {
            $table->json('puncture_vessels')->nullable()->after('puncture_vessel')->comment('穿刺血管（JSON）');
            $table->json('disease_names')->nullable()->after('disease_name')->comment('病名（JSON）');
        });

        // 肩
        Schema::table('case_shoulder_details', function (Blueprint $table) {
            $table->json('puncture_vessels')->nullable()->after('puncture_vessel')->comment('穿刺血管（JSON）');
            $table->json('disease_names')->nullable()->after('disease_name')->comment('病名（JSON）');
        });

        // 膝
        Schema::table('case_knee_details', function (Blueprint $table) {
            $table->json('puncture_vessels')->nullable()->after('puncture_vessel')->comment('穿刺血管（JSON）');
            $table->json('disease_names')->nullable()->after('disease_name')->comment('病名（JSON）');
        });

        // case_reports：トラブル・合併症もJSONに
        Schema::table('case_reports', function (Blueprint $table) {
            $table->json('complication_types')->nullable()->after('complications')->comment('トラブル・合併症（JSON）');
        });
    }

    public function down(): void
    {
        Schema::table('case_hand_details', function (Blueprint $table) {
            $table->dropColumn(['puncture_vessels', 'disease_names', 'right_pain_area_list', 'left_pain_area_list']);
        });
        Schema::table('case_foot_details', function (Blueprint $table) {
            $table->dropColumn(['puncture_vessels', 'disease_names']);
        });
        Schema::table('case_elbow_details', function (Blueprint $table) {
            $table->dropColumn(['puncture_vessels', 'disease_names']);
        });
        Schema::table('case_shoulder_details', function (Blueprint $table) {
            $table->dropColumn(['puncture_vessels', 'disease_names']);
        });
        Schema::table('case_knee_details', function (Blueprint $table) {
            $table->dropColumn(['puncture_vessels', 'disease_names']);
        });
        Schema::table('case_reports', function (Blueprint $table) {
            $table->dropColumn('complication_types');
        });
    }
};
