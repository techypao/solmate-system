<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminCustomerController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()?->role === User::ROLE_ADMIN, 403);

        $customers = User::query()
            ->where('role', User::ROLE_CUSTOMER)
            ->orderBy('name')
            ->get();

        return view('admin.customers', compact('customers'));
    }

    public function edit(Request $request, User $customer)
    {
        abort_unless($request->user()?->role === User::ROLE_ADMIN, 403);
        abort_unless($customer->role === User::ROLE_CUSTOMER, 404);

        return view('admin.customers.edit', compact('customer'));
    }

    public function update(Request $request, User $customer)
    {
        abort_unless($request->user()?->role === User::ROLE_ADMIN, 403);
        abort_unless($customer->role === User::ROLE_CUSTOMER, 404);

        $validated = $request->validate(
            [
                'name'  => ['required', 'string', 'max:255'],
                'email' => [
                    'required',
                    'string',
                    'email',
                    'max:255',
                    Rule::unique('users', 'email')->ignore($customer->id),
                ],
            ],
            [
                'name.required'  => 'Customer name is required.',
                'name.max'       => 'Name must not exceed 255 characters.',
                'email.required' => 'Email address is required.',
                'email.email'    => 'Please enter a valid email address.',
                'email.unique'   => 'This email address is already used by another account.',
                'email.max'      => 'Email must not exceed 255 characters.',
            ]
        );

        $customer->fill($validated)->save();

        return redirect()
            ->route('admin.customers')
            ->with('status', "Customer \"{$customer->name}\" was updated successfully.");
    }
}
