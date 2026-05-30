<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            // Promo type drives how the discount is applied to a quotation.
            // Supported: 'percentage', 'fixed_amount', 'free_item', 'bundle'
            $table->string('promo_type', 50)->nullable()->after('is_active');

            // The numeric value of the discount:
            //   percentage  → e.g. 10.00 means 10%
            //   fixed_amount → e.g. 5000.00 means ₱5,000 off
            //   free_item    → 0 (the free_item_description explains the benefit)
            //   bundle       → e.g. 5000.00 means a ₱5,000 effective discount
            $table->decimal('discount_value', 12, 2)->nullable()->after('promo_type');

            // Human-readable description of the free item or bundle benefit.
            // Used for 'free_item' and 'bundle' types.
            $table->string('free_item_description')->nullable()->after('discount_value');

            // Optional JSON-encoded conditions (min_panels, package_type, etc.).
            // Stored as-is and surfaced to the mobile app for display.
            $table->json('conditions')->nullable()->after('free_item_description');
        });
    }

    public function down(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            $table->dropColumn(['promo_type', 'discount_value', 'free_item_description', 'conditions']);
        });
    }
};
