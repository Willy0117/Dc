<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('resource_documents', function (Blueprint $table) {
            // この資料を閲覧できる最低グレード（1:ベーシック〜4:マスター）。
            // MyPage側で、先生自身のグレードがこれ未満の場合は
            // 一覧に表示しない（変更点：資料のグレード制御）。
            // デフォルト1（ベーシック）＝全員閲覧可、既存資料も同様に扱う。
            $table->unsignedTinyInteger('required_tier')->default(1)->after('title');
        });
    }

    public function down(): void
    {
        Schema::table('resource_documents', function (Blueprint $table) {
            $table->dropColumn('required_tier');
        });
    }
};
