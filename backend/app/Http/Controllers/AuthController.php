<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\PasswordValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
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

        $user = User::query()
            ->where('email', $credentials['email'])
            ->first();

        $passwordMatches = false;
        $legacyPasswordMatches = false;

        if ($user) {
            try {
                $passwordMatches = Hash::check($credentials['password'], (string) $user->getAuthPassword());
            } catch (RuntimeException) {
                $passwordMatches = false;
            }

            if (! $passwordMatches && hash_equals((string) $user->getAuthPassword(), $credentials['password'])) {
                $passwordMatches = true;
                $legacyPasswordMatches = true;
            }
        }

        if (! $user || ! $passwordMatches) {
            return back()
                ->withErrors(['email' => 'Invalid email or password.'])
                ->onlyInput('email');
        }

        if (! $user->hasVerifiedEmail() && $user->role !== User::ROLE_ADMIN) {
            return back()
                ->withErrors([
                    'email' => 'Please verify your email before logging in.',
                ])
                ->onlyInput('email');
        }

        if ($legacyPasswordMatches) {
            // Upgrade legacy plain-text passwords to hashed passwords after a verified login.
            $user->forceFill([
                'password' => Hash::make($credentials['password']),
            ])->save();

            Auth::login($user, $request->boolean('remember'));
        } else {
            try {
                $authenticated = Auth::attempt($credentials, $request->boolean('remember'));
            } catch (RuntimeException) {
                $authenticated = false;
            }

            if (! $authenticated) {
                return back()
                    ->withErrors(['email' => 'Invalid email or password.'])
                    ->onlyInput('email');
            }
        }

        if ($request->user()?->role === User::ROLE_TECHNICIAN) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withErrors([
                    'email' => 'Technician accounts can only sign in through the SolMate mobile app.',
                ])
                ->onlyInput('email');
        }

        if ($request->user()?->isArchivedCustomer()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withErrors([
                    'email' => User::archivedAccountMessage(),
                ])
                ->onlyInput('email');
        }

        $request->session()->regenerate();
        $request->user()?->markLoginRecorded();

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

        $user = User::create([
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
