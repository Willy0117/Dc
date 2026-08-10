<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profile_change_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('変更を行ったUserのid');
            $table->string('target_type', 20)->comment('organization / member / email / password');
            $table->unsignedBigInteger('target_id')->nullable()->comment('organizations.id または members.id');
            $table->json('changes')->nullable()->comment('変更前後の差分（フィールド毎の [before, after]）');
            $table->timestamps();

            $table->index(['target_type', 'target_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_change_logs');
    }
};
