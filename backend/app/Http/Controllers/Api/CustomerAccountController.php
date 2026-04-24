<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class CustomerAccountController extends Controller
{
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'address' => ['nullable', 'string', 'max:255'],
            'contact_number' => ['nullable', 'string', 'max:20'],
        ]);

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'address' => isset($validated['address']) ? trim($validated['address']) : null,
            'contact_number' => isset($validated['contact_number']) ? trim($validated['contact_number']) : null,
        ]);
        $user->save();

        return response()->json([
            'message' => 'Account information updated successfully.',
            'user' => $user->fresh(),
        ], 200);
    }

    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'confirmed', 'different:current_password', Password::min(8)],
        ]);

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
