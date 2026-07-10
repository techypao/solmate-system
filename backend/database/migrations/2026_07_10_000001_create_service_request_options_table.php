<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_request_options', function (Blueprint $table) {
            $table->id();
            $table->string('category', 80);
            $table->string('label');
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['category', 'label']);
            $table->index(['category', 'is_active', 'sort_order']);
        });

        $now = now();
        DB::table('service_request_options')->insert([
            [
                'category' => 'installation_type',
                'label' => 'Residential rooftop installation',
                'description' => 'For standard rooftop panel installation and on-site setup.',
                'sort_order' => 10,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 'installation_type',
                'label' => 'Ground-mounted solar setup',
                'description' => 'For properties using a ground structure instead of a roof mount.',
                'sort_order' => 20,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 'installation_type',
                'label' => 'System expansion or additional panels',
                'description' => 'For adding panels or expanding an existing approved system.',
                'sort_order' => 30,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 'installation_type',
                'label' => 'Installation schedule coordination',
                'description' => 'For customers ready to coordinate the installation appointment and site access.',
                'sort_order' => 40,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 'maintenance_concern',
                'label' => 'Battery check-up',
                'description' => 'For battery health review, charging issues, or preventive battery service.',
                'sort_order' => 10,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 'maintenance_concern',
                'label' => 'Panel cleaning',
                'description' => 'For dirt buildup, output drops, or scheduled solar panel cleaning.',
                'sort_order' => 20,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 'maintenance_concern',
                'label' => 'Inverter check',
                'description' => 'For inverter alerts, unusual readings, or operational checks.',
                'sort_order' => 30,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 'maintenance_concern',
                'label' => 'Wiring inspection',
                'description' => 'For electrical connection review, cable concerns, or safety checks.',
                'sort_order' => 40,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 'maintenance_concern',
                'label' => 'General system check',
                'description' => 'For regular maintenance, performance review, or overall system inspection.',
                'sort_order' => 50,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 'maintenance_concern',
                'label' => 'Other custom concern',
                'description' => 'For anything else you want the technician to review during the visit.',
                'sort_order' => 60,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('service_request_options');
    }
};
