<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\PasswordValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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

        $user->fill([
            'email' => $validated['email'],
            'address' => isset($validated['address']) ? trim($validated['address']) : null,
            'contact_number' => isset($validated['contact_number']) ? trim($validated['contact_number']) : null,
            'landline_number' => isset($validated['landline_number']) ? trim($validated['landline_number']) : null,
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
