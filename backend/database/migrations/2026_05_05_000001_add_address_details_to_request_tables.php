<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inspection_requests', function (Blueprint $table) {
            $table->string('address_details')->nullable()->after('address');
        });

        Schema::table('service_requests', function (Blueprint $table) {
            $table->string('address_details')->nullable()->after('address');
        });
    }

    public function down(): void
    {
        Schema::table('inspection_requests', function (Blueprint $table) {
            $table->dropColumn('address_details');
        });

        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropColumn('address_details');
        });
    }
};
