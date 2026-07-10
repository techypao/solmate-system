<?php

namespace Database\Seeders;

use App\Models\ServiceRequestOption;
use Illuminate\Database\Seeder;

class ServiceRequestOptionSeeder extends Seeder
{
    public function run(): void
    {
        $options = [
            [ServiceRequestOption::CATEGORY_INSTALLATION_TYPE, 'Residential rooftop installation', 'For standard rooftop panel installation and on-site setup.', 10],
            [ServiceRequestOption::CATEGORY_INSTALLATION_TYPE, 'Ground-mounted solar setup', 'For properties using a ground structure instead of a roof mount.', 20],
            [ServiceRequestOption::CATEGORY_INSTALLATION_TYPE, 'System expansion or additional panels', 'For adding panels or expanding an existing approved system.', 30],
            [ServiceRequestOption::CATEGORY_INSTALLATION_TYPE, 'Installation schedule coordination', 'For customers ready to coordinate the installation appointment and site access.', 40],
            [ServiceRequestOption::CATEGORY_MAINTENANCE_CONCERN, 'Battery check-up', 'For battery health review, charging issues, or preventive battery service.', 10],
            [ServiceRequestOption::CATEGORY_MAINTENANCE_CONCERN, 'Panel cleaning', 'For dirt buildup, output drops, or scheduled solar panel cleaning.', 20],
            [ServiceRequestOption::CATEGORY_MAINTENANCE_CONCERN, 'Inverter check', 'For inverter alerts, unusual readings, or operational checks.', 30],
            [ServiceRequestOption::CATEGORY_MAINTENANCE_CONCERN, 'Wiring inspection', 'For electrical connection review, cable concerns, or safety checks.', 40],
            [ServiceRequestOption::CATEGORY_MAINTENANCE_CONCERN, 'General system check', 'For regular maintenance, performance review, or overall system inspection.', 50],
            [ServiceRequestOption::CATEGORY_MAINTENANCE_CONCERN, 'Other custom concern', 'For anything else you want the technician to review during the visit.', 60],
        ];

        foreach ($options as [$category, $label, $description, $sortOrder]) {
            ServiceRequestOption::query()->updateOrCreate(
                [
                    'category' => $category,
                    'label' => $label,
                ],
                [
                    'description' => $description,
                    'sort_order' => $sortOrder,
                    'is_active' => true,
                ]
            );
        }
    }
}
