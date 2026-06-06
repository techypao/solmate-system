<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;

class VerificationController extends Controller
{
    public function verify(string $id, string $hash): View
    {
        $user = User::findOrFail($id);

        abort_unless(hash_equals($hash, sha1($user->getEmailForVerification())), 403);

        if ($user->hasVerifiedEmail()) {
            return view('auth.verify-success', ['already' => true]);
        }

        $user->markEmailAsVerified();

        return view('auth.verify-success');
    }
}
