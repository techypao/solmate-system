<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

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

        $validated = $request->validate(
            [
                'name'     => 'required|string|max:255',
                'email'    => 'required|email|max:255|unique:users,email',
                'password' => ['required', 'confirmed', Password::min(8)],
            ],
            [
                'email.unique' => 'A user with this email already exists.',
            ]
        );

        User::query()->create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => $validated['password'],
            'role'     => User::ROLE_TECHNICIAN,
        ]);

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
            [
                'name'     => 'required|string|max:255',
                'email'    => 'required|email|max:255|unique:users,email,' . $technician->id,
                'password' => ['nullable', 'confirmed', Password::min(8)],
            ],
            [
                'email.unique' => 'This email is already used by another account.',
            ]
        );

        $technician->name  = $validated['name'];
        $technician->email = $validated['email'];

        if (!empty($validated['password'])) {
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
}
