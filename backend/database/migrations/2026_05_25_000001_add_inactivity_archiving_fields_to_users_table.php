<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('is_archived')->default(false)->index()->after('archived_at');
            $table->timestamp('last_login_at')->nullable()->index()->after('remember_token');
            $table->timestamp('archive_warning_sent_at')->nullable()->index()->after('last_login_at');
        });

        DB::table('users')
            ->whereNotNull('archived_at')
            ->update(['is_archived' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'is_archived',
                'last_login_at',
                'archive_warning_sent_at',
            ]);
        });
    }
};