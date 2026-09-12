<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reference_video_categories', function (Blueprint $table) {
            // このカテゴリーの動画を視聴できる最低グレード（1:ベーシック〜4:マスター）。
            // MyPage側で、先生自身のグレードがこれ未満の場合は
            // このカテゴリーの動画を視聴できない（変更点：動画カテゴリーのグレード制御）。
            // デフォルト1（ベーシック）＝全員視聴可、既存カテゴリーも同様に扱う。
            $table->unsignedTinyInteger('required_tier')->default(1)->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('reference_video_categories', function (Blueprint $table) {
            $table->dropColumn('required_tier');
        });
    }
};
