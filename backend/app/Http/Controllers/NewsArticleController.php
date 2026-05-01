<?php

namespace App\Http\Controllers;

use App\Models\NewsArticle;

class NewsArticleController extends Controller
{
    public function index()
    {
        return response()->json([
            'message' => 'News articles retrieved successfully.',
            'data' => NewsArticle::query()->active()->latest()->get(),
        ]);
    }
}