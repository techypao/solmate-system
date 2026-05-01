<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsArticle;
use App\Services\NewsArticleMetadataService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class NewsArticleController extends Controller
{
    public function index()
    {
        return response()->json([
            'message' => 'News articles retrieved successfully.',
            'data' => NewsArticle::query()->latest()->get(),
        ]);
    }

    public function store(Request $request, NewsArticleMetadataService $metadataService)
    {
        $validated = $request->validate([
            'article_url' => ['required', 'string', 'url:http,https'],
        ], [
            'article_url.required' => 'An article URL is required.',
            'article_url.url' => 'Please provide a valid article URL.',
        ]);

        $normalizedUrl = $metadataService->normalizeUrl($validated['article_url']);

        $this->ensureUrlIsUnique($normalizedUrl);

        $metadata = $metadataService->fetch($normalizedUrl);

        $newsArticle = NewsArticle::query()->create([
            'article_url' => $normalizedUrl,
            'title' => $metadata['title'],
            'description' => $metadata['description'],
            'thumbnail_url' => $metadata['thumbnail_url'],
            'source_name' => $metadata['source_name'],
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'News article added successfully.',
            'data' => $newsArticle,
        ], 201);
    }

    public function toggle(NewsArticle $newsArticle)
    {
        $newsArticle->update([
            'is_active' => ! $newsArticle->is_active,
        ]);

        return response()->json([
            'message' => $newsArticle->is_active
                ? 'News article is now active.'
                : 'News article is now inactive.',
            'data' => $newsArticle->fresh(),
        ]);
    }

    public function refresh(NewsArticle $newsArticle, NewsArticleMetadataService $metadataService)
    {
        $metadata = $metadataService->fetch($newsArticle->article_url);

        $newsArticle->update([
            'title' => $metadata['title'],
            'description' => $metadata['description'],
            'thumbnail_url' => $metadata['thumbnail_url'],
            'source_name' => $metadata['source_name'],
        ]);

        return response()->json([
            'message' => 'News article metadata refreshed successfully.',
            'data' => $newsArticle->fresh(),
        ]);
    }

    public function destroy(NewsArticle $newsArticle)
    {
        $newsArticle->delete();

        return response()->json([
            'message' => 'News article deleted successfully.',
        ]);
    }

    private function ensureUrlIsUnique(string $articleUrl): void
    {
        if (NewsArticle::query()->where('article_url', $articleUrl)->exists()) {
            throw ValidationException::withMessages([
                'article_url' => 'This article URL has already been added.',
            ]);
        }
    }
}