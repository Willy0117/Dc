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
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('login_id')->unique()->comment('ログインID（必須）');
            $table->string('name')->comment('氏名');
            $table->string('phone')->nullable()->comment('電話番号');
            $table->text('address')->nullable()->comment('住所');
            $table->enum('status', ['provisional','regular','suspended','expelled'])
                  ->default('provisional')->comment('会員状態');
            $table->string('member_number')->nullable()->unique()->comment('会員番号（任意）');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
