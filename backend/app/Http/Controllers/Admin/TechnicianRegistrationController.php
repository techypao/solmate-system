<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class TechnicianRegistrationController extends Controller
{
    public function create(Request $request)
    {
        abort_unless($request->user()?->role === User::ROLE_ADMIN, 403);

        return view('admin.technicians.create', [
            'technicians' => User::query()
                ->where('role', User::ROLE_TECHNICIAN)
                ->latest()
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        abort_unless($request->user()?->role === User::ROLE_ADMIN, 403);

        $validated = $request->validate(
            [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email',
                'password' => ['required', 'confirmed', Password::min(8)],
            ],
            [
                'email.unique' => 'A user with this email already exists.',
            ]
        );

        User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => User::ROLE_TECHNICIAN,
        ]);

        return redirect()
            ->route('admin.technicians.create')
            ->with('status', 'Technician account created successfully.');
    }
}
