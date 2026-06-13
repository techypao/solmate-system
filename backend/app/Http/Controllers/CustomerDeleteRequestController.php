<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\InAppNotificationService;
use Illuminate\Http\Request;

class CustomerDeleteRequestController extends Controller
{
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

        $user->forceFill([
            'delete_requested_at' => now(),
            'delete_request_reason' => trim($validated['reason']),
        ])->save();

        $notificationService->notifyAdminsOfCustomerDeleteRequest($user);

        return redirect()
            ->route('dashboard')
            ->with('status', 'Your account deletion request was sent to the admin for review.');
    }
}
