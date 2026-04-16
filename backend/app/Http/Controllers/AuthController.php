<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use RuntimeException;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
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

        return redirect()->intended($this->redirectPath($request->user()))
            ->with('status', 'Logged in successfully.');
    }

    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => User::ROLE_CUSTOMER,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended($this->redirectPath($user))
            ->with('status', 'Account created successfully.');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('status', 'Logged out successfully.');
    }

    private function redirectPath(User $user): string
    {
        if ($user->role === User::ROLE_ADMIN) {
            return route('admin.quotation-settings');
        }

        return route('dashboard');
    }
}
