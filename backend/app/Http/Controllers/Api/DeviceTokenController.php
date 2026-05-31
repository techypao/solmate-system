<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DeviceTokenController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'fcm_token' => ['required', 'string', 'max:2048'],
        ]);

        /** @var User $authenticatedUser */
        $authenticatedUser = $request->user();

        Log::info('FCM device token save request received.', [
            'authenticated_user_id' => $authenticatedUser->id,
            'requested_user_id' => $validated['user_id'] ?? null,
            'token_suffix' => $this->tokenSuffix($validated['fcm_token']),
            'payload_keys' => array_keys($validated),
        ]);

        $targetUser = $authenticatedUser;

        if (! empty($validated['user_id']) && (int) $validated['user_id'] !== $authenticatedUser->id) {
            Log::warning('FCM device token save rejected for non-admin user.', [
                'authenticated_user_id' => $authenticatedUser->id,
                'requested_user_id' => (int) $validated['user_id'],
            ]);

            abort_unless($authenticatedUser->hasRole(User::ROLE_ADMIN), 403, 'You are not allowed to update another user device token.');

            $targetUser = User::query()->findOrFail($validated['user_id']);
        }

        $targetUser->forceFill([
            'fcm_token' => trim($validated['fcm_token']),
        ])->save();

        Log::info('FCM device token saved successfully.', [
            'target_user_id' => $targetUser->id,
            'token_suffix' => $this->tokenSuffix($validated['fcm_token']),
        ]);

        return response()->json([
            'message' => 'Device token saved successfully.',
            'user_id' => $targetUser->id,
        ]);
    }

    private function tokenSuffix(string $token): string
    {
        return substr(trim($token), -8) ?: trim($token);
    }
}