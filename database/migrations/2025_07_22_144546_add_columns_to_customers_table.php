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
        Schema::table('customers', function (Blueprint $table) {
            $table->date('birthdate')->nullable()->after('name');
            $table->integer('deleted_by')->nullable()->after('phone');
            $table->integer('updated_by')->nullable()->after('phone');
            $table->integer('created_by')->after('phone');
            $table->integer('status')->default(1)->after('phone')->comment('[1=activo, 0=inactivo]');
            $table->string('address', 15)->nullable()->after('phone');
            $table->string('rfc', 15)->nullable()->after('phone');
            $table->string('company_name')->nullable()->after('phone');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('birthdate');
            $table->dropColumn('company_name');
            $table->dropColumn('rfc');
            $table->dropColumn('address');
            $table->dropColumn('status');
            $table->dropColumn('created_by');
            $table->dropColumn('updated_by');
            $table->dropColumn('deleted_by');
            $table->dropColumn('deleted_at');
        });
    }
};
