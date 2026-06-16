<?php

namespace Tests\Feature;

use App\Models\InspectionRequest;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerFinalQuotationPdfDownloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_final_quotation_pdf_download_uses_customer_name_in_filename(): void
    {
        $customer = $this->createUser(User::ROLE_CUSTOMER, 'Juan Dela Cruz, Jr.');
        $technician = $this->createUser(User::ROLE_TECHNICIAN, 'Technician User');
        $inspectionRequest = $this->createInspectionRequest($customer->id, $technician->id);
        $quotation = $this->createFinalQuotation($customer->id, $inspectionRequest->id);

        $response = $this->actingAs($customer)
            ->get("/customer/final-quotation/{$inspectionRequest->id}/download-pdf");

        $response->assertOk();
        $this->assertStringContainsString(
            'attachment; filename=Final_Quotation_Juan_Dela_Cruz_Jr_' . $quotation->id . '.pdf',
            (string) $response->headers->get('content-disposition')
        );
    }

    public function test_final_quotation_pdf_download_falls_back_to_quotation_id_when_customer_name_is_missing(): void
    {
        $customer = $this->createUser(User::ROLE_CUSTOMER, '');
        $technician = $this->createUser(User::ROLE_TECHNICIAN, 'Technician User');
        $inspectionRequest = $this->createInspectionRequest($customer->id, $technician->id);
        $quotation = $this->createFinalQuotation($customer->id, $inspectionRequest->id);

        $response = $this->actingAs($customer)
            ->get("/customer/final-quotation/{$inspectionRequest->id}/download-pdf");

        $response->assertOk();
        $this->assertStringContainsString(
            'attachment; filename=Final_Quotation_' . $quotation->id . '.pdf',
            (string) $response->headers->get('content-disposition')
        );
    }

    private function createInspectionRequest(int $customerId, int $technicianId): InspectionRequest
    {
        return InspectionRequest::query()->create([
            'user_id' => $customerId,
            'technician_id' => $technicianId,
            'details' => 'Completed site inspection',
            'status' => 'completed',
        ]);
    }

    private function createFinalQuotation(int $customerId, int $inspectionRequestId): Quotation
    {
        return Quotation::query()->create([
            'user_id' => $customerId,
            'inspection_request_id' => $inspectionRequestId,
            'quotation_type' => 'final',
            'monthly_electric_bill' => 3200,
            'status' => 'pending',
        ]);
    }

    private function createUser(string $role, string $name): User
    {
        $user = User::query()->create([
            'name' => $name,
            'email' => strtolower($role) . '_' . uniqid() . '@example.com',
            'password' => Hash::make('password'),
            'role' => $role,
        ]);

        $user->forceFill(['email_verified_at' => now()])->save();

        return $user;
    }
}
