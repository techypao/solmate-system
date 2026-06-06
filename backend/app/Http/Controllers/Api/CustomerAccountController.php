<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\PasswordValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;

class CustomerAccountController extends Controller
{
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'address' => ['nullable', 'string', 'max:255'],
            'contact_number' => ['nullable', 'string', 'max:20'],
            'landline_number' => ['nullable', 'string', 'max:30', 'regex:/^(?=(?:.*\\d){7,})[0-9()+\\-\\s]+$/'],
        ], [
            'landline_number.regex' => 'Please enter a valid landline number using digits, spaces, parentheses, or hyphens.',
        ]);

        $emailChanged = $user->email !== $validated['email'];

        $user->fill([
            'address' => isset($validated['address']) ? trim($validated['address']) : null,
            'contact_number' => isset($validated['contact_number']) ? trim($validated['contact_number']) : null,
            'landline_number' => isset($validated['landline_number']) ? trim($validated['landline_number']) : null,
        ]);

        if ($emailChanged) {
            $user->email = $validated['email'];
            $user->email_verified_at = null;
        }

        $user->save();

        if ($emailChanged) {
            $this->sendNewEmailVerification($user);
            $user->tokens()->delete();
            DB::table('sessions')->where('user_id', $user->id)->delete();

            return response()->json([
                'message' => 'Email updated. Please verify your new email and log in again.',
            ], 403);
        }

        return response()->json([
            'message' => 'Account information updated successfully.',
            'user' => $user->fresh(),
        ], 200);
    }

    private function sendNewEmailVerification($user): void
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

    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => array_merge(
                ['required', 'different:current_password'],
                PasswordValidation::rules()
            ),
        ], PasswordValidation::messages('new_password'));

        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'message' => 'The provided password is incorrect.',
                'errors' => [
                    'current_password' => ['The provided password is incorrect.'],
                ],
            ], 422);
        }

        $user->password = $validated['new_password'];
        $user->save();

        return response()->json([
            'message' => 'Password updated successfully.',
        ], 200);
    }
}
