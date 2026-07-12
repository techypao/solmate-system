<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\CustomerActivityLogger;
use App\Services\InAppNotificationService;
use Illuminate\Http\Request;

class CustomerDeleteRequestController extends Controller
{
    public function create(Request $request)
    {
        return view('public.delete-account', [
            'customerEmail' => $request->user()?->role === User::ROLE_CUSTOMER
                ? $request->user()->email
                : '',
        ]);
    }

    public function storePublic(Request $request, InAppNotificationService $notificationService)
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ], [
            'email.required' => 'Please enter the email address connected to your SolMate account.',
            'email.email' => 'Please enter a valid email address.',
            'reason.max' => 'The deletion request reason must not exceed 1000 characters.',
        ]);

        $customer = User::query()
            ->where('role', User::ROLE_CUSTOMER)
            ->whereRaw('LOWER(email) = ?', [strtolower(trim($validated['email']))])
            ->first();

        if ($customer) {
            $this->recordDeleteRequest(
                $customer,
                trim((string) ($validated['reason'] ?? '')),
                $notificationService,
            );
        }

        return redirect()
            ->route('delete-account')
            ->with('status', 'Your account deletion request was successfully submitted. Our admin team will review it and process the request.');
    }

    public function store(Request $request, InAppNotificationService $notificationService)
    {
        $user = $request->user();

        abort_unless($user?->role === User::ROLE_CUSTOMER, 403);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ], [
            'reason.required' => 'Please write a reason for deleting your account.',
            'reason.min' => 'Please provide a little more detail about your deletion request.',
            'reason.max' => 'The deletion request reason must not exceed 1000 characters.',
        ]);

        $this->recordDeleteRequest($user, trim($validated['reason']), $notificationService);

        return redirect()
            ->route('dashboard')
            ->with('status', 'Your account deletion request was sent to the admin for review.');
    }

    private function recordDeleteRequest(
        User $customer,
        string $reason,
        InAppNotificationService $notificationService
    ): void {
        $customer->forceFill([
            'delete_requested_at' => now(),
            'delete_request_reason' => $reason !== '' ? $reason : null,
        ])->save();

        app(CustomerActivityLogger::class)->record(
            customer: $customer,
            eventType: 'customer_delete_requested',
            title: 'Account deletion requested',
            description: $reason !== '' ? $reason : 'No reason provided.',
            actor: $customer,
            subject: $customer,
        );

        $notificationService->notifyAdminsOfCustomerDeleteRequest($customer);
    }
}
