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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('barcode');
            $table->string('brand')->nullable();
            $table->decimal('price', 8, 2);
            $table->decimal('discounted_price', 8, 2)->nullable();
            $table->string('type_sale', 5);
            $table->string('content', 10)->nullable();
            $table->string('abreviation', 5)->nullable();
            $table->text('description')->nullable();
            $table->boolean('status')->default(true);
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
