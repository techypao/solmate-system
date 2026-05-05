<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\PasswordValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class TechnicianAccountController extends Controller
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
        ]);

        $user->fill([
            'email' => $validated['email'],
        ]);
        $user->save();

        return response()->json([
            'message' => 'Technician account information updated successfully.',
            'user' => $user->fresh(),
        ], 200);
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
