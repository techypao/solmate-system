<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->timestamp('delete_requested_at')->nullable()->index()->after('cancellation_count');
            $table->text('delete_request_reason')->nullable()->after('delete_requested_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['delete_requested_at', 'delete_request_reason']);
        });
    }
};
