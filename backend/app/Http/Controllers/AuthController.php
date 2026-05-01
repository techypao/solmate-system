<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\PasswordValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return response(view('auth.login'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = [
            'email' => trim($validated['email']),
            'password' => $validated['password'],
        ];

        try {
            $authenticated = Auth::attempt($credentials, $request->boolean('remember'));
        } catch (RuntimeException) {
            $authenticated = false;
        }

        if (!$authenticated) {
            $legacyUser = User::query()
                ->where('email', $credentials['email'])
                ->first();

            if ($legacyUser && hash_equals((string) $legacyUser->getAuthPassword(), $credentials['password'])) {
                // Upgrade legacy plain-text passwords to hashed passwords after a verified login.
                $legacyUser->forceFill([
                    'password' => Hash::make($credentials['password']),
                ])->save();

                Auth::login($legacyUser, $request->boolean('remember'));
            } else {
                return back()
                    ->withErrors(['email' => 'Invalid email or password.'])
                    ->onlyInput('email');
            }
        }

        $request->session()->regenerate();

        return redirect($this->redirectPath($request->user()))
            ->with('login_success', 'Logged in successfully.');
    }

    public function showRegisterForm()
    {
        return response(view('auth.register'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'address' => 'required|string|max:255',
            'contact_number' => ['required', 'regex:/^[0-9]{11}$/'],
            'landline_number' => 'nullable|string|max:30',
            'password' => PasswordValidation::required(),
        ], array_merge(
            PasswordValidation::messages(),
            [
                'contact_number.regex' => 'Contact number must be exactly 11 digits.',
            ]
        ));

        $firstName = trim($validated['first_name']);
        $lastName = trim($validated['last_name']);

        User::create([
            'name' => trim($firstName.' '.$lastName),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => trim($validated['email']),
            'password' => $validated['password'],
            'role' => User::ROLE_CUSTOMER,
            'address' => trim($validated['address']),
            'contact_number' => trim($validated['contact_number']),
            'landline_number' => filled($validated['landline_number'] ?? null)
                ? trim($validated['landline_number'])
                : null,
        ]);

        return redirect()
            ->route('register')
            ->with('registration_success', 'Account successfully created! Redirecting to login page...');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('logout_success', 'Logged out successfully.');
    }

    private function redirectPath(User $user): string
    {
        if ($user->role === User::ROLE_ADMIN) {
            return route('dashboard');
        }

        if ($user->role === User::ROLE_CUSTOMER) {
            return route('home');
        }

        return route('dashboard');
    }
}
