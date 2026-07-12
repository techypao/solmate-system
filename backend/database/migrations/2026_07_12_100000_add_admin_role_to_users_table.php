<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('admin_role', 40)->nullable()->index();
        });

        DB::table('users')
            ->where('role', User::ROLE_ADMIN)
            ->whereNull('admin_role')
            ->update(['admin_role' => User::ADMIN_ROLE_SUPER_ADMIN]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('admin_role');
        });
    }
};
