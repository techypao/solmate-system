<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_item_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pricing_item_id')
                ->nullable()
                ->constrained('pricing_items')
                ->nullOnDelete();
            $table->foreignId('performed_by_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('action');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->timestamps();

            $table->index(['pricing_item_id', 'created_at']);
            $table->index(['action', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_item_histories');
    }
};
