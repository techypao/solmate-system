<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->foreignId('service_request_option_id')
                ->nullable()
                ->after('request_type')
                ->index('service_requests_option_id_index');
            $table->string('service_request_option_label')->nullable()->after('service_request_option_id');
        });
    }

    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropIndex('service_requests_option_id_index');
            $table->dropColumn('service_request_option_id');
            $table->dropColumn('service_request_option_label');
        });
    }
};
