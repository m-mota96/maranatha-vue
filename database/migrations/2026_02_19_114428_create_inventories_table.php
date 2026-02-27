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
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products');
            $table->unsignedBigInteger('reference_id');
            $table->foreign('reference_id')->references('id')->on('references');
            $table->unsignedBigInteger('sale_id')->nullable();
            $table->string('type', 20)->comment('entrada/salida');
            $table->decimal('quantity', 8, 2);
            $table->date('expiration_date')->nullable();
            $table->string('batch')->nullable();
            $table->string('discount')->nullable();
            $table->decimal('price', 8, 2)->nullable()->comment('Precio al que se vendio el producto');
            $table->decimal('product_cost', 8, 2)->nullable()->comment('Costo del producto');
            $table->text('description')->nullable();
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
        Schema::dropIfExists('inventories');
    }
};
