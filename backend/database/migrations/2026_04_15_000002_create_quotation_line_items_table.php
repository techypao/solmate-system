<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotation_line_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('pricing_item_id')
                ->nullable()
                ->constrained('pricing_items')
                ->nullOnDelete();
            $table->text('description');
            $table->string('category')->index();
            $table->decimal('qty', 12, 2);
            $table->string('unit');
            $table->decimal('unit_amount', 12, 2);
            $table->decimal('total_amount', 12, 2);
            $table->timestamps();

            $table->index(['quotation_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_line_items');
    }
};
