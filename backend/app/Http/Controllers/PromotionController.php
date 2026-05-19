<?php

namespace App\Http\Controllers;

use App\Models\Promotion;

class PromotionController extends Controller
{
    public function index()
    {
        return response()->json([
            'message' => 'Active promotions retrieved successfully.',
            'data' => Promotion::query()->currentlyLive()->orderByDesc('created_at')->get(),
        ]);
    }
}
