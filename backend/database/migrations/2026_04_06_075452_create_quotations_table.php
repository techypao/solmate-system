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
    Schema::create('quotations', function (Blueprint $table) {
        $table->id();

        // Relationships
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();

        // Type of quotation
        $table->string('quotation_type'); // initial or final

        // ===== INPUT FIELDS =====
        $table->decimal('monthly_electric_bill', 10, 2)->nullable();
        $table->decimal('rate_per_kwh', 10, 2)->default(14);
        $table->integer('days_in_month')->default(30);
        $table->decimal('sun_hours', 5, 2)->default(4.5);
        $table->decimal('pv_safety_factor', 5, 2)->default(1.8);
        $table->decimal('battery_factor', 5, 2)->default(1.0);
        $table->decimal('battery_voltage', 5, 2)->default(51.2);

        // System setup (final quotation customization)
        $table->string('pv_system_type')->nullable(); // hybrid, on-grid, off-grid
        $table->boolean('with_battery')->default(true);
        $table->string('inverter_type')->nullable();
        $table->string('battery_model')->nullable();
        $table->decimal('battery_capacity_ah', 10, 2)->nullable();
        $table->decimal('panel_watts', 10, 2)->default(610);

        // ===== COMPUTED VALUES =====
        $table->decimal('monthly_kwh', 10, 2)->nullable();
        $table->decimal('daily_kwh', 10, 2)->nullable();
        $table->decimal('pv_kw_raw', 10, 2)->nullable();
        $table->decimal('pv_kw_safe', 10, 2)->nullable();
        $table->integer('panel_quantity')->nullable();
        $table->decimal('system_kw', 10, 2)->nullable();
        $table->decimal('battery_required_kwh', 10, 2)->nullable();
        $table->decimal('battery_required_ah', 10, 2)->nullable();

        // ===== COSTING =====
        $table->decimal('panel_cost', 12, 2)->nullable();
        $table->decimal('inverter_cost', 12, 2)->nullable();
        $table->decimal('battery_cost', 12, 2)->nullable();
        $table->decimal('bos_cost', 12, 2)->nullable();
        $table->decimal('materials_subtotal', 12, 2)->nullable();
        $table->decimal('labor_cost', 12, 2)->nullable();
        $table->decimal('project_cost', 12, 2)->nullable();

        // Status & notes
        $table->string('status')->default('pending');
        $table->text('remarks')->nullable();

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
