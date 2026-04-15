<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('quotation_settings', function (Blueprint $table) {
            $table->decimal('initial_price_per_kw', 12, 2)->default(50000.00);
        });

        DB::table('quotation_settings')
            ->whereNull('initial_price_per_kw')
            ->update([
                'initial_price_per_kw' => 50000.00,
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotation_settings', function (Blueprint $table) {
            $table->dropColumn('initial_price_per_kw');
        });
    }
};
