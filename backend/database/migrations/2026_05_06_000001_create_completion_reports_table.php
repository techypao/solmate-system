<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('completion_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('inspection_request_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('technician_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->longText('report_text');
            $table->text('findings')->nullable();
            $table->text('recommendations')->nullable();
            $table->string('status')->default('pending');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->unique('service_request_id');
            $table->unique('inspection_request_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('completion_reports');
    }
};
