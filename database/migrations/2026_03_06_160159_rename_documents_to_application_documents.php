<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 現在の documents を application_documents に戻す
        Schema::rename('documents', 'application_documents');
    }

    public function down(): void
    {
        // rollback 用
        Schema::rename('application_documents', 'documents');
    }
};