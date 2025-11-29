<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('法人名');
            $table->string('billing_name')->nullable()->comment('請求先名（個人と異なる場合）');
            $table->string('billing_postal')->nullable();
            $table->string('billing_address')->nullable();
            $table->string('contact_person')->nullable()->comment('窓口担当者');
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('registration_number')->nullable()->comment('適格請求書番号等');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
