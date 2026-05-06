<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('application_documents', 'documents'); // テーブル名を元に戻す
    }

    public function down(): void
    {
        Schema::rename('documents', 'application_documents'); // rollback 用
    }
};
