<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\NameValidation;
use App\Support\PasswordValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'first_name' => NameValidation::rules(),
            'last_name' => NameValidation::rules(),
            'email' => 'required|email|unique:users,email',
            'password' => PasswordValidation::required(),
            'address' => 'required|string|max:255',
            'contact_number' => ['required', 'regex:/^[0-9]{11}$/'],
            'landline_number' => 'nullable|string|max:30',
        ], array_merge(
            PasswordValidation::messages(),
            NameValidation::messages(),
            [
                'contact_number.regex' => 'Contact number must be exactly 11 digits.',
            ]
        ));

        $firstName = NameValidation::normalize($validated['first_name']);
        $lastName = NameValidation::normalize($validated['last_name']);

        $user = User::create([
            'name' => trim($firstName.' '.$lastName),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => trim($validated['email']),
            'password' => Hash::make($validated['password']),
            'role' => User::ROLE_CUSTOMER,
            'address' => trim($validated['address']),
            'contact_number' => trim($validated['contact_number']),
            'landline_number' => filled($validated['landline_number'] ?? null)
                ? trim($validated['landline_number'])
                : null,
        ]);

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
                ->subject('Verify Your Email - SolMate')
                ->html('
            <div style="font-family: Arial, sans-serif; background-color: #f9f9f9; padding: 40px;">
                
                <div style="max-width: 500px; margin: auto; background: #ffffff; padding: 30px; border-radius: 10px; text-align: center;">
                    
                    <h2 style="color: #333;">Verify Your Email ✅</h2>

                    <p style="color: #555;">
                        Please click the button below to verify your account.
                    </p>

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

                    <p style="margin-top: 20px; font-size: 12px; color: #888;">
                        If you did not create this account, you can ignore this email.
                    </p>

                </div>
            </div>
        ');
        });

        \Log::info('EMAIL SENT MANUALLY', [
            'email' => $user->email,
            'url' => $verificationUrl,
        ]);

        Log::info('REGISTER USER', ['email' => $user->email]);

        return response()->json([
            'message' => 'Registered successfully. Please verify your email.',
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        Log::info('LOGIN ATTEMPT START', [
            'email' => $request->email,
            'user_found' => (bool) $user,
            'verified' => $user ? $user->hasVerifiedEmail() : null,
        ]);

        if (! $user) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        if (! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        if (! $user->hasVerifiedEmail() && $user->role !== User::ROLE_ADMIN) {
            return response()->json([
                'message' => 'EMAIL_NOT_VERIFIED',
                'error' => 'Please verify your email before logging in.',
            ], 403);
        }

        if ($user->isArchivedCustomer()) {
            return response()->json([
                'message' => User::archivedAccountMessage(),
            ], 403);
        }

        $user->markLoginRecorded();

        $token = $user->createToken('authToken')->plainTextToken;

        Log::info('LOGIN SUCCESS', [
            'email' => $user->email,
        ]);

        return response()->json([
            'token' => $token,
            'user' => $user,
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        $user->forceFill([
            'fcm_token' => null,
        ])->save();

        $user->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ], 200);
    }

    public function updateProfilePicture(Request $request)
    {
        $request->validate([
            'profile_picture' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $user = $request->user();

        if ($request->hasFile('profile_picture')) {
            if ($user->profile_picture) {
                Storage::disk('public')->delete($user->profile_picture);
            }

            $user->profile_picture = $request->file('profile_picture')->store('profile_pictures', 'public');
            $user->save();
        }

        return response()->json([
            'message' => $request->hasFile('profile_picture')
                ? 'Profile picture updated successfully.'
                : 'No profile picture uploaded.',
            'user' => $user->fresh(),
        ], 200);
    }
}
