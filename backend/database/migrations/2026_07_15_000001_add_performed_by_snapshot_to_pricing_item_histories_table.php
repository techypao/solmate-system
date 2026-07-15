<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pricing_item_histories', function (Blueprint $table): void {
            $table->json('performed_by_snapshot')
                ->nullable()
                ->after('performed_by_id');
        });
    }

    public function down(): void
    {
        Schema::table('pricing_item_histories', function (Blueprint $table): void {
            $table->dropColumn('performed_by_snapshot');
        });
    }
};
