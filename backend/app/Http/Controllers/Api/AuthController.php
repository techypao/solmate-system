<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\PasswordValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => PasswordValidation::required(),
            'address' => 'required|string|max:255',
            'contact_number' => ['required', 'regex:/^[0-9]{11}$/'],
            'landline_number' => 'nullable|string|max:30',
        ], array_merge(
            PasswordValidation::messages(),
            [
                'contact_number.regex' => 'Contact number must be exactly 11 digits.',
            ]
        ));

        $firstName = trim($validated['first_name']);
        $lastName = trim($validated['last_name']);

        $user = User::create([
            'name' => trim($firstName.' '.$lastName),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => trim($validated['email']),
            'password' => Hash::make($validated['password']),
            'role' => $request->role ?? 'customer',
            'address' => trim($validated['address']),
            'contact_number' => trim($validated['contact_number']),
            'landline_number' => filled($validated['landline_number'] ?? null)
                ? trim($validated['landline_number'])
                : null,
        ]);

        $user->sendEmailVerificationNotification();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'token' => $token,
            'user' => $user,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        if ($user->isArchivedCustomer()) {
            return response()->json([
                'message' => User::archivedAccountMessage(),
            ], 403);
        }

        if (! $user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Please verify your email before logging in.',
            ], 403);
        }

        $user->markLoginRecorded();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user,
        ], 200);
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
            'profile_picture' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
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
