<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resource_document_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('resource_documents', function (Blueprint $table) {
            $table->foreignId('category_id')
                ->after('id')
                ->constrained('resource_document_categories')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('resource_documents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
        });

        Schema::dropIfExists('resource_document_categories');
    }
};
