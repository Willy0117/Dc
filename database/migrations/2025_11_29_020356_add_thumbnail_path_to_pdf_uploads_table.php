<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pdf_uploads', function (Blueprint $table) {
            $table->string('thumbnail_path')->nullable()->after('file_path')->comment('PDFサムネイル画像のパス');
        });
    }

    public function down(): void
    {
        Schema::table('pdf_uploads', function (Blueprint $table) {
            $table->dropColumn('thumbnail_path');
        });
    }
};

