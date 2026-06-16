<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->string('discount_request_status')
                ->default('none')
                ->after('promo_discount');
            $table->text('discount_request_message')
                ->nullable()
                ->after('discount_request_status');
            $table->timestamp('discount_requested_at')
                ->nullable()
                ->after('discount_request_message');
            $table->timestamp('discount_request_resolved_at')
                ->nullable()
                ->after('discount_requested_at');
            $table->decimal('admin_discount_amount', 12, 2)
                ->default(0)
                ->after('discount_request_resolved_at');
            $table->text('admin_discount_reason')
                ->nullable()
                ->after('admin_discount_amount');
            $table->foreignId('admin_discount_applied_by')
                ->nullable()
                ->after('admin_discount_reason')
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('admin_discount_applied_at')
                ->nullable()
                ->after('admin_discount_applied_by');
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropForeign(['admin_discount_applied_by']);
            $table->dropColumn([
                'discount_request_status',
                'discount_request_message',
                'discount_requested_at',
                'discount_request_resolved_at',
                'admin_discount_amount',
                'admin_discount_reason',
                'admin_discount_applied_by',
                'admin_discount_applied_at',
            ]);
        });
    }
};
