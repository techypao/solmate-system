<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inspection_requests', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
            $table->string('customer_name')->nullable()->after('user_id');
            $table->string('customer_email')->nullable()->after('customer_name');
        });

        Schema::table('quotations', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
        });

        DB::table('service_requests')
            ->where('request_type', 'Manual Inspection Request')
            ->orderBy('id')
            ->get()
            ->each(function (object $serviceRequest): void {
                $inspectionRequestId = DB::table('inspection_requests')->insertGetId([
                    'user_id' => null,
                    'customer_name' => $serviceRequest->customer_name,
                    'customer_email' => $serviceRequest->customer_email,
                    'technician_id' => $serviceRequest->technician_id,
                    'details' => $serviceRequest->details,
                    'cancellation_note' => $serviceRequest->cancellation_note,
                    'contact_number' => $serviceRequest->contact_number,
                    'address' => $serviceRequest->address,
                    'address_details' => $serviceRequest->address_details,
                    'latitude' => $serviceRequest->latitude,
                    'longitude' => $serviceRequest->longitude,
                    'date_needed' => $serviceRequest->date_needed,
                    'status' => $serviceRequest->status,
                    'created_at' => $serviceRequest->created_at,
                    'updated_at' => $serviceRequest->updated_at,
                ]);

                DB::table('completion_reports')
                    ->where('service_request_id', $serviceRequest->id)
                    ->update([
                        'service_request_id' => null,
                        'inspection_request_id' => $inspectionRequestId,
                    ]);

                DB::table('service_requests')
                    ->where('id', $serviceRequest->id)
                    ->delete();
            });
    }

    public function down(): void
    {
        DB::table('inspection_requests')
            ->whereNull('user_id')
            ->whereNotNull('customer_name')
            ->orderBy('id')
            ->get()
            ->each(function (object $inspectionRequest): void {
                $serviceRequestId = DB::table('service_requests')->insertGetId([
                    'user_id' => null,
                    'customer_name' => $inspectionRequest->customer_name,
                    'customer_email' => $inspectionRequest->customer_email,
                    'technician_id' => $inspectionRequest->technician_id,
                    'request_type' => 'Manual Inspection Request',
                    'details' => $inspectionRequest->details,
                    'cancellation_note' => $inspectionRequest->cancellation_note,
                    'contact_number' => $inspectionRequest->contact_number,
                    'address' => $inspectionRequest->address,
                    'address_details' => $inspectionRequest->address_details,
                    'latitude' => $inspectionRequest->latitude,
                    'longitude' => $inspectionRequest->longitude,
                    'date_needed' => $inspectionRequest->date_needed,
                    'status' => $inspectionRequest->status,
                    'created_at' => $inspectionRequest->created_at,
                    'updated_at' => $inspectionRequest->updated_at,
                ]);

                DB::table('completion_reports')
                    ->where('inspection_request_id', $inspectionRequest->id)
                    ->update([
                        'service_request_id' => $serviceRequestId,
                        'inspection_request_id' => null,
                    ]);

                DB::table('inspection_requests')
                    ->where('id', $inspectionRequest->id)
                    ->delete();
            });

        Schema::table('inspection_requests', function (Blueprint $table) {
            $table->dropColumn(['customer_name', 'customer_email']);
            $table->foreignId('user_id')->nullable(false)->change();
        });

        Schema::table('quotations', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
        });
    }
};
