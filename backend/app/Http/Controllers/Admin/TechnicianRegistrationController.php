<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\PasswordValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;

class TechnicianRegistrationController extends Controller
{
    private function technicianList()
    {
        return User::query()
            ->where('role', User::ROLE_TECHNICIAN)
            ->latest()
            ->get();
    }

    public function create(Request $request)
    {
        abort_unless($request->user()?->role === User::ROLE_ADMIN, 403);

        return view('admin.technicians.create', [
            'technicians' => $this->technicianList(),
        ]);
    }

    public function store(Request $request)
    {
        abort_unless($request->user()?->role === User::ROLE_ADMIN, 403);

        $validated = $request->validate($this->rules(), $this->messages());

        $firstName = trim($validated['first_name']);
        $lastName = trim($validated['last_name']);

        $user = User::query()->create([
            'name' => trim($firstName.' '.$lastName),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => trim($validated['email']),
            'contact_number' => trim($validated['contact_number']),
            'password' => $validated['password'],
            'role' => User::ROLE_TECHNICIAN,
        ]);

        $user->email_verified_at = null;
        $user->save();

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
            <div style="text-align:center; padding:20px;">
                <h2>Verify Your Account</h2>
                <p>An account was created for you. Please verify your email.</p>

                <a href="'.$verificationUrl.'" style="padding:12px 20px; background:#facc15; color:#000; text-decoration:none; border-radius:6px;">
                    Verify Email
                </a>

                <p style="font-size:12px; color:#888;">
                    If you did not expect this, contact admin.
                </p>
            </div>
        ');
        });

        return redirect()
            ->route('admin.technicians.create')
            ->with('status', 'Technician account created successfully.');
    }

    public function edit(Request $request, User $technician)
    {
        abort_unless($request->user()?->role === User::ROLE_ADMIN, 403);
        abort_unless($technician->role === User::ROLE_TECHNICIAN, 404);

        return view('admin.technicians.edit', [
            'technician'  => $technician,
            'technicians' => $this->technicianList(),
        ]);
    }

    public function update(Request $request, User $technician)
    {
        abort_unless($request->user()?->role === User::ROLE_ADMIN, 403);
        abort_unless($technician->role === User::ROLE_TECHNICIAN, 404);

        $validated = $request->validate(
            $this->rules($technician),
            $this->messages()
        );

        $firstName = trim($validated['first_name']);
        $lastName = trim($validated['last_name']);

        $technician->name = trim($firstName.' '.$lastName);
        $technician->first_name = $firstName;
        $technician->last_name = $lastName;
        $technician->email = $validated['email'];
        $technician->contact_number = trim($validated['contact_number']);

        if (! empty($validated['password'])) {
            $technician->password = $validated['password'];
        }

        $technician->save();

        return redirect()
            ->route('admin.technicians.create')
            ->with('status', 'Technician account updated successfully.');
    }

    public function destroy(Request $request, User $technician)
    {
        abort_unless($request->user()?->role === User::ROLE_ADMIN, 403);
        abort_unless($technician->role === User::ROLE_TECHNICIAN, 404);

        // FK columns use nullOnDelete() — safe to hard-delete.
        // Assignments on service_requests and inspection_requests
        // will automatically be set to NULL by the database.
        $technician->delete();

        return redirect()
            ->route('admin.technicians.create')
            ->with('status', 'Technician account removed. Existing request assignments have been cleared.');
    }

    private function rules(?User $technician = null): array
    {
        $emailRule = Rule::unique('users', 'email');

        if ($technician) {
            $emailRule = $emailRule->ignore($technician->id);
        }

        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', $emailRule],
            'contact_number' => ['required', 'regex:/^[0-9]{11}$/'],
            'password' => $technician ? PasswordValidation::nullable() : PasswordValidation::required(),
        ];
    }

    private function messages(): array
    {
        return array_merge([
            'email.unique' => 'A user with this email already exists.',
            'contact_number.regex' => 'Contact number must be exactly 11 digits.',
        ], PasswordValidation::messages());
    }
}
