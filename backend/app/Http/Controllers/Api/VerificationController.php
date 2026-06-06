<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class VerificationController extends Controller
{
    public function verify(string $id, string $hash): JsonResponse
    {
        $user = User::findOrFail($id);

        abort_unless(hash_equals($hash, sha1($user->getEmailForVerification())), 403);

        $user->markEmailAsVerified();

        return response()->json([
            'message' => 'Email verified successfully',
        ]);
    }
}
