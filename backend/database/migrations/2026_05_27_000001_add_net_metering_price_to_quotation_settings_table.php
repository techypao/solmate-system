<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotation_settings', function (Blueprint $table) {
            $table->decimal('net_metering_price', 12, 2)
                ->default(30000.00)
                ->after('initial_price_per_kw');
        });

        DB::table('quotation_settings')
            ->whereNull('net_metering_price')
            ->update(['net_metering_price' => 30000.00]);
    }

    public function down(): void
    {
        Schema::table('quotation_settings', function (Blueprint $table) {
            $table->dropColumn('net_metering_price');
        });
    }
};