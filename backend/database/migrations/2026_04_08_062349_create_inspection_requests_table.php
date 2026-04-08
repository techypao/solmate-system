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
    Schema::create('inspection_requests', function (Blueprint $table) {
        $table->id();

        // Link to INITIAL quotation
        $table->foreignId('quotation_id')->constrained()->cascadeOnDelete();

        $table->date('scheduled_date')->nullable();
        $table->text('notes')->nullable();
        $table->string('status')->default('pending');

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspection_requests');
    }
};