<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('testimonies')) {
            return;
        }

        Schema::table('testimonies', function (Blueprint $table) {
            if (! Schema::hasColumn('testimonies', 'service_request_id')) {
                $table->foreignId('service_request_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained('service_requests')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('testimonies', 'inspection_request_id')) {
                $table->foreignId('inspection_request_id')
                    ->nullable()
                    ->after('service_request_id')
                    ->constrained('inspection_requests')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('testimonies', 'status')) {
                $table->string('status')
                    ->default('pending')
                    ->after(Schema::hasColumn('testimonies', 'message') ? 'message' : 'content');
            }

            if (! Schema::hasColumn('testimonies', 'admin_note')) {
                $table->text('admin_note')->nullable()->after('status');
            }
        });

        if (Schema::hasColumn('testimonies', 'content') && ! Schema::hasColumn('testimonies', 'message')) {
            Schema::table('testimonies', function (Blueprint $table) {
                $table->renameColumn('content', 'message');
            });
        }

        if (Schema::hasColumn('testimonies', 'is_approved')) {
            DB::table('testimonies')
                ->where('is_approved', true)
                ->update(['status' => 'approved']);

            DB::table('testimonies')
                ->whereNull('status')
                ->update(['status' => 'pending']);
        }

        if (Schema::hasColumn('testimonies', 'title')) {
            Schema::table('testimonies', function (Blueprint $table) {
                $table->string('title')->nullable()->change();
            });
        }

        if (Schema::hasColumn('testimonies', 'rating')) {
            Schema::table('testimonies', function (Blueprint $table) {
                $table->unsignedTinyInteger('rating')->nullable()->change();
            });
        }

        if (Schema::hasColumn('testimonies', 'is_approved')) {
            Schema::table('testimonies', function (Blueprint $table) {
                $table->dropColumn('is_approved');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('testimonies')) {
            return;
        }

        if (! Schema::hasColumn('testimonies', 'is_approved') && Schema::hasColumn('testimonies', 'status')) {
            Schema::table('testimonies', function (Blueprint $table) {
                $table->boolean('is_approved')->default(false)->after('rating');
            });

            DB::table('testimonies')
                ->where('status', 'approved')
                ->update(['is_approved' => true]);
        }

        if (Schema::hasColumn('testimonies', 'status')) {
            Schema::table('testimonies', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }

        if (Schema::hasColumn('testimonies', 'admin_note')) {
            Schema::table('testimonies', function (Blueprint $table) {
                $table->dropColumn('admin_note');
            });
        }

        if (Schema::hasColumn('testimonies', 'service_request_id')) {
            Schema::table('testimonies', function (Blueprint $table) {
                $table->dropConstrainedForeignId('service_request_id');
            });
        }

        if (Schema::hasColumn('testimonies', 'inspection_request_id')) {
            Schema::table('testimonies', function (Blueprint $table) {
                $table->dropConstrainedForeignId('inspection_request_id');
            });
        }

        if (Schema::hasColumn('testimonies', 'message') && ! Schema::hasColumn('testimonies', 'content')) {
            Schema::table('testimonies', function (Blueprint $table) {
                $table->renameColumn('message', 'content');
            });
        }

        if (Schema::hasColumn('testimonies', 'title')) {
            DB::table('testimonies')->whereNull('title')->update(['title' => '']);

            Schema::table('testimonies', function (Blueprint $table) {
                $table->string('title')->nullable(false)->change();
            });
        }

        if (Schema::hasColumn('testimonies', 'rating')) {
            Schema::table('testimonies', function (Blueprint $table) {
                $table->integer('rating')->nullable()->change();
            });
        }
    }
};
