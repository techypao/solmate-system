<?php

namespace Tests\Feature;

use App\Models\NewsArticle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NewsArticleApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_endpoint_only_returns_active_news_articles_newest_first(): void
    {
        $older = NewsArticle::query()->create([
            'article_url' => 'https://example.com/older-article',
            'title' => 'Older Active Article',
            'description' => 'Older description',
            'thumbnail_url' => 'https://example.com/older.jpg',
            'source_name' => 'example.com',
            'is_active' => true,
        ]);

        $newer = NewsArticle::query()->create([
            'article_url' => 'https://example.com/newer-article',
            'title' => 'Newer Active Article',
            'description' => 'Newer description',
            'thumbnail_url' => 'https://example.com/newer.jpg',
            'source_name' => 'example.com',
            'is_active' => true,
        ]);

        NewsArticle::query()->whereKey($older->id)->update([
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        NewsArticle::query()->whereKey($newer->id)->update([
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        NewsArticle::query()->create([
            'article_url' => 'https://example.com/inactive-article',
            'title' => 'Inactive Article',
            'description' => 'Inactive description',
            'thumbnail_url' => 'https://example.com/inactive.jpg',
            'source_name' => 'example.com',
            'is_active' => false,
        ]);

        $this->getJson('/api/public/news-articles')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $newer->id)
            ->assertJsonPath('data.1.id', $older->id);
    }

    public function test_admin_can_add_toggle_refresh_and_delete_news_articles(): void
    {
        $admin = $this->createUserWithRole(User::ROLE_ADMIN);
        Sanctum::actingAs($admin);

        Http::fake([
            'https://example.com/solar-article' => Http::sequence()
                ->push($this->articleHtml('Solar Savings Explained', 'Understand how solar savings work.', 'https://cdn.example.com/solar.jpg'), 200)
                ->push($this->articleHtml('Solar Savings Refreshed', 'Fresh metadata after refetch.', 'https://cdn.example.com/solar-refresh.jpg'), 200),
        ]);

        $createResponse = $this->postJson('/api/admin/news-articles', [
            'article_url' => 'https://example.com/solar-article',
        ])
            ->assertCreated()
            ->assertJsonPath('data.title', 'Solar Savings Explained')
            ->assertJsonPath('data.description', 'Understand how solar savings work.')
            ->assertJsonPath('data.thumbnail_url', 'https://cdn.example.com/solar.jpg')
            ->assertJsonPath('data.source_name', 'example.com')
            ->assertJsonPath('data.is_active', true);

        $articleId = $createResponse->json('data.id');

        $this->patchJson("/api/admin/news-articles/{$articleId}/toggle")
            ->assertOk()
            ->assertJsonPath('data.is_active', false);

        $this->postJson("/api/admin/news-articles/{$articleId}/refresh")
            ->assertOk()
            ->assertJsonPath('data.title', 'Solar Savings Refreshed')
            ->assertJsonPath('data.description', 'Fresh metadata after refetch.')
            ->assertJsonPath('data.thumbnail_url', 'https://cdn.example.com/solar-refresh.jpg');

        $this->deleteJson("/api/admin/news-articles/{$articleId}")
            ->assertOk()
            ->assertJsonPath('message', 'News article deleted successfully.');

        $this->assertDatabaseMissing('news_articles', [
            'id' => $articleId,
        ]);
    }

    public function test_metadata_fetch_failures_fall_back_without_crashing(): void
    {
        $admin = $this->createUserWithRole(User::ROLE_ADMIN);
        Sanctum::actingAs($admin);

        Http::fake([
            'https://fallback.example.com/article' => Http::response('', 500),
        ]);

        $this->postJson('/api/admin/news-articles', [
            'article_url' => 'https://fallback.example.com/article',
        ])
            ->assertCreated()
            ->assertJsonPath('data.source_name', 'fallback.example.com')
            ->assertJsonPath('data.title', 'fallback.example.com')
            ->assertJsonPath('data.thumbnail_url', null);
    }

    public function test_news_article_validation_duplicates_and_permissions_are_enforced(): void
    {
        $admin = $this->createUserWithRole(User::ROLE_ADMIN);
        $customer = $this->createUserWithRole(User::ROLE_CUSTOMER);
        $technician = $this->createUserWithRole(User::ROLE_TECHNICIAN);

        Sanctum::actingAs($admin);

        $this->postJson('/api/admin/news-articles', [
            'article_url' => 'not-a-url',
        ])->assertStatus(422)
            ->assertJsonValidationErrors('article_url');

        Http::fake([
            'https://duplicate.example.com/article' => Http::response($this->articleHtml('Duplicate Test', 'Description', null), 200),
        ]);

        $this->postJson('/api/admin/news-articles', [
            'article_url' => 'https://duplicate.example.com/article',
        ])->assertCreated();

        $this->postJson('/api/admin/news-articles', [
            'article_url' => 'https://duplicate.example.com/article',
        ])->assertStatus(422)
            ->assertJsonValidationErrors('article_url');

        Sanctum::actingAs($customer);
        $this->getJson('/api/admin/news-articles')->assertForbidden();
        $this->postJson('/api/admin/news-articles', [])->assertForbidden();

        Sanctum::actingAs($technician);
        $this->getJson('/api/admin/news-articles')->assertForbidden();
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

    private function articleHtml(string $title, string $description, ?string $imageUrl): string
    {
        $metaImage = $imageUrl ? '<meta property="og:image" content="' . e($imageUrl) . '">' : '';

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>{$title}</title>
    <meta property="og:title" content="{$title}">
    <meta property="og:description" content="{$description}">
    {$metaImage}
</head>
<body>Article</body>
</html>
HTML;
    }
}