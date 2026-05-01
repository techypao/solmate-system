<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\VisualHighlight;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class VisualHighlightApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_endpoint_only_returns_active_visual_highlights(): void
    {
        Storage::fake(VisualHighlight::PUBLIC_DISK);

        $active = VisualHighlight::query()->create([
            'image_path' => 'visual-highlights/active-one.jpg',
            'is_active' => true,
        ]);

        VisualHighlight::query()->create([
            'image_path' => 'visual-highlights/inactive-one.jpg',
            'is_active' => false,
        ]);

        $this->getJson('/api/public/visual-highlights')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $active->id)
            ->assertJsonPath('data.0.is_active', true)
            ->assertJsonPath('data.0.image_path', 'visual-highlights/active-one.jpg');

        $this->assertStringContainsString(
            '/storage/visual-highlights/active-one.jpg',
            $this->getJson('/api/public/visual-highlights')->json('data.0.image_url')
        );
    }

    public function test_admin_can_create_update_list_and_delete_visual_highlights(): void
    {
        Storage::fake(VisualHighlight::PUBLIC_DISK);

        $admin = $this->createUserWithRole(User::ROLE_ADMIN);

        Sanctum::actingAs($admin);

        $createResponse = $this->withHeader('Accept', 'application/json')
            ->post('/api/admin/visual-highlights', [
                'image' => UploadedFile::fake()->image('highlight-one.jpg'),
                'is_active' => '1',
            ])
            ->assertCreated()
            ->assertJsonPath('data.is_active', true);

        $highlightId = $createResponse->json('data.id');
        $originalImagePath = $createResponse->json('data.image_path');

        Storage::disk(VisualHighlight::PUBLIC_DISK)->assertExists($originalImagePath);

        $this->getJson('/api/admin/visual-highlights')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $highlightId);

        $updateResponse = $this->withHeader('Accept', 'application/json')
            ->post("/api/admin/visual-highlights/{$highlightId}", [
                '_method' => 'PATCH',
                'image' => UploadedFile::fake()->image('highlight-two.png'),
                'is_active' => '0',
            ])
            ->assertOk()
            ->assertJsonPath('data.id', $highlightId)
            ->assertJsonPath('data.is_active', false);

        $updatedImagePath = $updateResponse->json('data.image_path');

        Storage::disk(VisualHighlight::PUBLIC_DISK)->assertMissing($originalImagePath);
        Storage::disk(VisualHighlight::PUBLIC_DISK)->assertExists($updatedImagePath);

        $this->assertDatabaseHas('visual_highlights', [
            'id' => $highlightId,
            'is_active' => false,
            'image_path' => $updatedImagePath,
        ]);

        $this->deleteJson("/api/admin/visual-highlights/{$highlightId}")
            ->assertOk()
            ->assertJsonPath('message', 'Visual highlight removed successfully.');

        Storage::disk(VisualHighlight::PUBLIC_DISK)->assertMissing($updatedImagePath);
        $this->assertDatabaseMissing('visual_highlights', [
            'id' => $highlightId,
        ]);
    }

    public function test_visual_highlight_validation_and_admin_permissions_are_enforced(): void
    {
        Storage::fake(VisualHighlight::PUBLIC_DISK);

        $admin = $this->createUserWithRole(User::ROLE_ADMIN);
        $customer = $this->createUserWithRole(User::ROLE_CUSTOMER);
        $technician = $this->createUserWithRole(User::ROLE_TECHNICIAN);

        Sanctum::actingAs($admin);

        $this->postJson('/api/admin/visual-highlights', [
        ])->assertStatus(422)
            ->assertJsonValidationErrors('image');

        $this->withHeader('Accept', 'application/json')
            ->post('/api/admin/visual-highlights', [
                'image' => UploadedFile::fake()->create('notes.pdf', 100, 'application/pdf'),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('image');

        Sanctum::actingAs($customer);

        $this->getJson('/api/admin/visual-highlights')->assertForbidden();
        $this->postJson('/api/admin/visual-highlights', [])->assertForbidden();

        Sanctum::actingAs($technician);

        $this->getJson('/api/admin/visual-highlights')->assertForbidden();
    }

    private function createUserWithRole(string $role): User
    {
        return User::query()->create([
            'name' => ucfirst($role) . ' User',
            'email' => $role . '_' . uniqid() . '@example.com',
            'password' => Hash::make('password'),
            'role' => $role,
        ]);
    }
}
