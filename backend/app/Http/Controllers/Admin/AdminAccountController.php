<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\NameValidation;
use App\Support\PasswordValidation;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminAccountController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()?->role === User::ROLE_ADMIN, 403);

        return view('admin.admins', [
            'admins' => $this->adminList(),
            'adminRoleOptions' => User::adminRoleOptions(),
        ]);
    }

    public function store(Request $request)
    {
        abort_unless($request->user()?->role === User::ROLE_ADMIN, 403);

        $validated = $request->validate($this->rules(), $this->messages());

        $firstName = NameValidation::normalize($validated['first_name']);
        $lastName = NameValidation::normalize($validated['last_name']);

        $admin = User::query()->create([
            'name' => trim($firstName.' '.$lastName),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => trim($validated['email']),
            'contact_number' => trim($validated['contact_number']),
            'password' => $validated['password'],
            'role' => User::ROLE_ADMIN,
            'admin_role' => $validated['admin_role'],
        ]);

        $admin->email_verified_at = now();
        $admin->save();

        return redirect()
            ->route('admin.admins')
            ->with('status', 'Staff account created successfully.');
    }

    public function destroy(Request $request, User $admin)
    {
        abort_unless($request->user()?->role === User::ROLE_ADMIN, 403);
        abort_unless($admin->role === User::ROLE_ADMIN, 404);

        if ($request->user()->is($admin)) {
            return redirect()
                ->route('admin.admins')
                ->withErrors(['admin' => 'You cannot delete your own staff account.']);
        }

        if ($admin->isSuperAdmin() && User::query()
            ->where('role', User::ROLE_ADMIN)
            ->where(function ($query): void {
                $query->where('admin_role', User::ADMIN_ROLE_SUPER_ADMIN)
                    ->orWhereNull('admin_role');
            })
            ->count() <= 1) {
            return redirect()
                ->route('admin.admins')
                ->withErrors(['admin' => 'At least one Super Admin account must remain.']);
        }

        $adminName = $admin->name;
        $admin->delete();

        return redirect()
            ->route('admin.admins')
            ->with('status', "Staff account \"{$adminName}\" was deleted successfully.");
    }

    private function adminList()
    {
        return User::query()
            ->where('role', User::ROLE_ADMIN)
            ->latest()
            ->get();
    }

    private function rules(): array
    {
        return [
            'first_name' => NameValidation::rules(),
            'last_name' => NameValidation::rules(),
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'contact_number' => ['required', 'regex:/^[0-9]{11}$/'],
            'admin_role' => ['required', Rule::in(array_keys(User::adminRoleOptions()))],
            'password' => PasswordValidation::required(),
        ];
    }

    private function messages(): array
    {
        return array_merge([
            'email.unique' => 'A user with this email already exists.',
            'contact_number.regex' => 'Contact number must be exactly 11 digits.',
            'admin_role.required' => 'Choose a staff role.',
            'admin_role.in' => 'Choose a valid staff role.',
        ], NameValidation::messages(), PasswordValidation::messages());
    }
}
