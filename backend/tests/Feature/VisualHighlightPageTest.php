<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class VisualHighlightPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_visual_highlights_page(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin_visual_highlights_page@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
        ]);

        $this->actingAs($admin)
            ->get('/admin/visual-highlights')
            ->assertOk()
            ->assertSee('Manage Visual Highlights')
            ->assertSee('Upload image')
            ->assertSee('Carousel Images')
            ->assertDontSee('Title')
            ->assertDontSee('Description');
    }
}
