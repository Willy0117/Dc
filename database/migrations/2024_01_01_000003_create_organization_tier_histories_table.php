<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organization_tier_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->integer('case_count')->default(0);
            $table->tinyInteger('tier')->default(1);
            $table->timestamps();

            $table->index(['organization_id', 'period_start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_tier_histories');
    }
};
