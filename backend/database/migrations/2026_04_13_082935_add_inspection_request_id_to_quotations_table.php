<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->foreignId('inspection_request_id')
                ->nullable()
                ->after('user_id')
                ->constrained('inspection_requests')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropForeign(['inspection_request_id']);
            $table->dropColumn('inspection_request_id');
        });
    }
};