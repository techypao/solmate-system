<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inspection_requests', function (Blueprint $table) {
            $table->string('contact_number', 30)->nullable()->after('details');
        });

        Schema::table('service_requests', function (Blueprint $table) {
            $table->string('contact_number', 30)->nullable()->after('details');
        });
    }

    public function down(): void
    {
        Schema::table('inspection_requests', function (Blueprint $table) {
            $table->dropColumn('contact_number');
        });

        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropColumn('contact_number');
        });
    }
};
