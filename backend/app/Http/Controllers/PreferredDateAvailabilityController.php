<?php

namespace App\Http\Controllers;

use App\Services\PreferredDateLockService;
use Illuminate\Http\JsonResponse;

class PreferredDateAvailabilityController extends Controller
{
    public function __invoke(PreferredDateLockService $preferredDateLockService): JsonResponse
    {
        return response()->json([
            'unavailable_dates' => $preferredDateLockService->getUnavailableDates(),
        ]);
    }
}
