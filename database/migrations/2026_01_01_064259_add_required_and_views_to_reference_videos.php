<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reference_videos', function (Blueprint $table) {
            $table->boolean('is_required')->default(false)->after('title')->comment('必須視聴動画かどうか');
        });

        Schema::create('reference_video_views', function (Blueprint $table) {
            $table->id();
            // 参考情報として残すが、視聴完了の判定には使わない（ユーザー単位で判定するため）
            $table->foreignId('organization_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('reference_video_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('user_id')->comment('視聴したUserのid（病院代表アカウント or 先生個人アカウント）');
            $table->timestamp('viewed_at')->useCurrent();
            $table->timestamps();

            // ユーザー単位で1動画1レコード（同じ動画を病院アカウント・先生個人アカウントそれぞれが視聴する必要がある）
            $table->unique(['user_id', 'reference_video_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reference_video_views');

        Schema::table('reference_videos', function (Blueprint $table) {
            $table->dropColumn('is_required');
        });
    }
};
