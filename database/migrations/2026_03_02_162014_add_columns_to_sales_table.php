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
        Schema::table('sales', function (Blueprint $table) {
            $table->string('type_discount')->nullable()->after('card');
            $table->integer('discount')->nullable()->after('card');
            $table->decimal('subtotal', 8, 2)->after('card');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('subtotal');
            $table->dropColumn('discount');
            $table->dropColumn('type_discount');
        });
    }
};
