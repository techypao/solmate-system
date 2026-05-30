<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            // The promotion that was applied when this quotation was created.
            $table->unsignedBigInteger('applied_promo_id')->nullable()->after('remarks');
            $table->foreign('applied_promo_id')
                ->references('id')
                ->on('promotions')
                ->nullOnDelete();

            // The actual PHP-peso amount discounted from the project cost.
            $table->decimal('promo_discount', 12, 2)->nullable()->after('applied_promo_id');
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropForeign(['applied_promo_id']);
            $table->dropColumn(['applied_promo_id', 'promo_discount']);
        });
    }
};
