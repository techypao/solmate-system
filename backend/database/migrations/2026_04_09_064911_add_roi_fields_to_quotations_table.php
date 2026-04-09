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
       Schema::table('quotations', function (Blueprint $table) {
    $table->decimal('estimated_monthly_savings', 12, 2)->nullable();
    $table->decimal('estimated_annual_savings', 12, 2)->nullable();
    $table->decimal('roi_years', 8, 2)->nullable();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            //
        });
    }
};
