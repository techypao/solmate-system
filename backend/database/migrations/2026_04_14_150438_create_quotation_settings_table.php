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
        Schema::create('quotation_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('rate_per_kwh', 10, 2)->default(14.00);
            $table->unsignedInteger('days_in_month')->default(30);
            $table->decimal('sun_hours', 5, 2)->default(4.50);
            $table->decimal('pv_safety_factor', 5, 2)->default(1.80);
            $table->decimal('battery_factor', 5, 2)->default(1.00);
            $table->decimal('battery_voltage', 6, 2)->default(51.20);
            $table->decimal('labor_percentage', 5, 2)->default(0.00);
            $table->decimal('default_bos_cost', 12, 2)->default(0.00);
            $table->decimal('default_misc_cost', 12, 2)->default(0.00);
            $table->decimal('default_panel_watts', 10, 2)->default(610.00);
            $table->timestamps();
        });

        DB::table('quotation_settings')->insert([
            'rate_per_kwh' => 14.00,
            'days_in_month' => 30,
            'sun_hours' => 4.50,
            'pv_safety_factor' => 1.80,
            'battery_factor' => 1.00,
            'battery_voltage' => 51.20,
            'labor_percentage' => 0.00,
            'default_bos_cost' => 0.00,
            'default_misc_cost' => 0.00,
            'default_panel_watts' => 610.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotation_settings');
    }
};
