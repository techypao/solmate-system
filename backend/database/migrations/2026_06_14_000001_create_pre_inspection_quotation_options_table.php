<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pre_inspection_quotation_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained('quotations')->cascadeOnDelete();
            $table->string('system_type')->index();
            $table->boolean('with_battery')->default(false);
            $table->decimal('system_kw', 10, 2)->nullable();
            $table->integer('panel_quantity')->nullable();
            $table->decimal('panel_watts', 10, 2)->nullable();
            $table->decimal('base_project_cost', 12, 2)->nullable();
            $table->decimal('inverter_capacity_kw', 10, 2)->nullable();
            $table->decimal('inverter_cost', 12, 2)->nullable();
            $table->decimal('battery_required_kwh', 10, 2)->nullable();
            $table->decimal('battery_required_ah', 10, 2)->nullable();
            $table->decimal('battery_capacity_ah', 10, 2)->nullable();
            $table->decimal('battery_voltage', 6, 2)->nullable();
            $table->decimal('battery_cost', 12, 2)->nullable();
            $table->decimal('project_cost', 12, 2)->nullable();
            $table->decimal('estimated_monthly_savings', 12, 2)->nullable();
            $table->decimal('estimated_annual_savings', 12, 2)->nullable();
            $table->decimal('roi_years', 8, 2)->nullable();
            $table->boolean('requires_technician_validation')->default(false);
            $table->text('validation_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pre_inspection_quotation_options');
    }
};
