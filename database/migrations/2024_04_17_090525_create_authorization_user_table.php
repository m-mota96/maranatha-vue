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
        Schema::create('authorization_user', function (Blueprint $table) {
            $table->unsignedBigInteger('authorization_id');
            $table->unsignedBigInteger('user_id');
            $table->foreign('authorization_id')->references('id')->on('authorizations');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('authorization_user');
    }
};
