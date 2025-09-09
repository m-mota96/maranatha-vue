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
        Schema::create('appoiment_service', function (Blueprint $table) {
            $table->unsignedBigInteger('appoiment_id');
            $table->unsignedBigInteger('service_id');
            $table->unsignedBigInteger('staff_id');
            $table->decimal('price', 10, 2);
            $table->decimal('discounted_price', 10, 2)->nullable();
            $table->string('start_time', 10);
            $table->string('end_time', 10);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appoiment_service');
    }
};
