<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class NewsArticlePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_manage_news_page(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin News User',
            'email' => 'admin_news_page@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
        ]);

        $this->actingAs($admin)
            ->get('/admin/news-articles')
            ->assertOk()
            ->assertSee('Manage News')
            ->assertSee('Add News Article')
            ->assertSee('Paste only the article URL');
    }
}