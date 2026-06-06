<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\PasswordValidation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;

class ProfilePageController extends Controller
{
    public function show(Request $request)
    {
        abort_unless($request->user()?->role === User::ROLE_ADMIN, 403);

        return view('admin.profile', [
            'user' => $request->user(),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->role === User::ROLE_ADMIN, 403);

        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
        ]);

        $emailChanged = $user->email !== $validated['email'];

        $user->fill([
            'name' => $validated['name'],
        ]);

        if ($emailChanged) {
            $user->email = $validated['email'];
            $user->email_verified_at = null;
        }

        $user->save();

        if ($emailChanged) {
            $this->sendNewEmailVerification($user);

            if ($user->role !== User::ROLE_ADMIN) {
                $user->tokens()->delete();

                Auth::logout();
                DB::table('sessions')->where('user_id', $user->id)->delete();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect('/login')
                    ->with('message', 'Please verify your new email before logging in.');
            }
        }

        return back()->with('status', 'Admin profile updated successfully.');
    }

    private function sendNewEmailVerification(User $user): void
    {
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ]
        );

        Mail::send([], [], function ($message) use ($user, $verificationUrl): void {
            $message->to($user->email)
                ->subject('Verify Your New Email - SolMate')
                ->html('
            <div style="font-family: Arial; text-align:center; padding:30px;">
                <h2>Verify Your New Email</h2>
                <p>You changed your email. Please verify it.</p>

                <a href="'.$verificationUrl.'" style="
                    display: inline-block;
                    padding: 12px 25px;
                    font-size: 16px;
                    color: white;
                    background-color: #f4b400;
                    border-radius: 6px;
                    text-decoration: none;
                    margin-top: 20px;
                ">
                    Verify Email
                </a>
            </div>
        ');
        });
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->role === User::ROLE_ADMIN, 403);

        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => array_merge(
                ['required', 'different:current_password'],
                PasswordValidation::rules()
            ),
        ], PasswordValidation::messages('new_password'));

        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors([
                'current_password' => 'The provided current password is incorrect.',
            ]);
        }

        $user->password = $validated['new_password'];
        $user->save();

        return back()->with('status', 'Admin password updated successfully.');
    }
}
