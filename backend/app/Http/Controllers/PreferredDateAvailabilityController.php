<?php

namespace App\Http\Controllers;

use App\Services\PreferredDateLockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PreferredDateAvailabilityController extends Controller
{
    public function __invoke(Request $request, PreferredDateLockService $preferredDateLockService): JsonResponse
    {
        $type = strtolower((string) $request->query('type', PreferredDateLockService::REQUEST_TYPE_INSPECTION));

        return response()->json([
            'unavailable_dates' => $preferredDateLockService->getUnavailableDates($type),
        ]);
    }
}
