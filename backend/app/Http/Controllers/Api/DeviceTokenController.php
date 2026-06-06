<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeviceTokenController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required_without:fcm_token', 'nullable', 'string', 'max:2048'],
            'fcm_token' => ['required_without:token', 'nullable', 'string', 'max:2048'],
        ]);

        /** @var User $authenticatedUser */
        $authenticatedUser = $request->user();
        $deviceToken = trim((string) ($validated['token'] ?? $validated['fcm_token'] ?? ''));

        abort_if($deviceToken === '', 422, 'A Firebase device token is required.');

        Log::info('FCM device token save request received.', [
            'authenticated_user_id' => $authenticatedUser->id,
            'token_suffix' => $this->tokenSuffix($deviceToken),
            'payload_keys' => array_keys($validated),
        ]);

        DB::transaction(function () use ($authenticatedUser, $deviceToken): void {
            User::query()
                ->where('id', '!=', $authenticatedUser->id)
                ->where('fcm_token', $deviceToken)
                ->update(['fcm_token' => null]);

            $authenticatedUser->forceFill([
                'fcm_token' => $deviceToken,
            ])->save();
        });

        Log::info('FCM device token saved successfully.', [
            'target_user_id' => $authenticatedUser->id,
            'token_suffix' => $this->tokenSuffix($deviceToken),
        ]);

        return response()->json([
            'message' => 'Device token saved successfully.',
            'user_id' => $authenticatedUser->id,
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        /** @var User $authenticatedUser */
        $authenticatedUser = $request->user();

        $authenticatedUser->forceFill([
            'fcm_token' => null,
        ])->save();

        Log::info('FCM device token removed successfully.', [
            'user_id' => $authenticatedUser->id,
        ]);

        return response()->json([
            'message' => 'Device token removed successfully.',
            'user_id' => $authenticatedUser->id,
        ]);
    }

    private function tokenSuffix(string $token): string
    {
        return substr(trim($token), -8) ?: trim($token);
    }
}
