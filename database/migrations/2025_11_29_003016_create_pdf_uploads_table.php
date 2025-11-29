<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pdf_uploads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->onDelete('cascade');
            $table->string('file_path');
            $table->enum('category', ['conference', 'seminar', 'journal']);
            $table->string('role');
            $table->string('organization_name');
            $table->enum('status', ['pending','approved','rejected'])->default('pending');
            $table->text('rejection_message')->nullable();
            $table->integer('unit')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pdf_uploads');
    }
};
