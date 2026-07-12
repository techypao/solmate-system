<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\CustomerActivityLog;
use App\Models\InspectionRequest;
use App\Models\Quotation;
use App\Models\ServiceRequest;
use App\Models\Testimony;
use App\Models\User;
use App\Services\CustomerActivityLogger;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminCustomerController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()?->role === User::ROLE_ADMIN, 403);

        $customers = User::query()
            ->where('role', User::ROLE_CUSTOMER)
            ->whereNull('archived_at')
            ->latest()
            ->get();

        $archivedCustomers = User::query()
            ->where('role', User::ROLE_CUSTOMER)
            ->whereNotNull('archived_at')
            ->latest('archived_at')
            ->get();

        return view('admin.customers', compact('customers', 'archivedCustomers'));
    }

    public function edit(Request $request, User $customer)
    {
        abort_unless($request->user()?->role === User::ROLE_ADMIN, 403);
        abort_unless($customer->role === User::ROLE_CUSTOMER, 404);

        return view('admin.customers.edit', compact('customer'));
    }

    public function show(Request $request, User $customer)
    {
        abort_unless($request->user()?->role === User::ROLE_ADMIN, 403);
        abort_unless($customer->role === User::ROLE_CUSTOMER && ! $customer->isArchivedCustomer(), 404);

        $quotations = Quotation::query()
            ->with(['inspectionRequest', 'appliedPromo'])
            ->where('user_id', $customer->id)
            ->latest()
            ->get();

        $inspectionRequests = InspectionRequest::query()
            ->with(['technician', 'completionReport'])
            ->where('user_id', $customer->id)
            ->latest()
            ->get();

        $serviceRequests = ServiceRequest::query()
            ->with(['technician', 'serviceRequestOption', 'completionReport'])
            ->where('user_id', $customer->id)
            ->latest()
            ->get();

        $testimonies = Testimony::query()
            ->with(['serviceRequest', 'inspectionRequest'])
            ->where('user_id', $customer->id)
            ->latest()
            ->get();

        $activityLogs = CustomerActivityLog::query()
            ->with('actor')
            ->where('customer_user_id', $customer->id)
            ->latest('occurred_at')
            ->limit(50)
            ->get();

        $archiveAudits = $customer->customerArchiveAudits()
            ->with('performedBy')
            ->latest()
            ->get();

        $chatConversation = ChatConversation::query()
            ->with('admin')
            ->where('customer_user_id', $customer->id)
            ->first();

        $chatMessages = $chatConversation
            ? $chatConversation->messages()->with('sender')->latest()->limit(10)->get()->reverse()->values()
            : collect();

        $timeline = $this->buildCustomerTimeline(
            $customer,
            $quotations,
            $inspectionRequests,
            $serviceRequests,
            $testimonies,
            $activityLogs,
            $archiveAudits,
            $chatConversation,
        );

        return view('admin.customers.show', compact(
            'customer',
            'quotations',
            'inspectionRequests',
            'serviceRequests',
            'testimonies',
            'activityLogs',
            'archiveAudits',
            'chatConversation',
            'chatMessages',
            'timeline',
        ));
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

    public function destroy(Request $request, User $customer)
    {
        abort_unless($request->user()?->role === User::ROLE_ADMIN, 403);
        abort_unless($customer->role === User::ROLE_CUSTOMER, 404);

        $customerName = $customer->name;

        // Customer-owned quotations, requests, inspections, and testimonies
        // are configured with cascade deletes in the database schema.
        $customer->delete();

        return redirect()
            ->route('admin.customers')
            ->with('status', "Customer \"{$customerName}\" was deleted successfully.");
    }

    public function archive(Request $request, User $customer)
    {
        abort_unless($request->user()?->role === User::ROLE_ADMIN, 403);
        abort_unless($customer->role === User::ROLE_CUSTOMER, 404);

        if ($customer->archived_at === null) {
            $customer->archiveAccount(
                performedByUserId: $request->user()?->id,
                reason: 'manual_archive',
            );

            app(CustomerActivityLogger::class)->record(
                customer: $customer,
                eventType: 'admin_archived_customer',
                title: 'Customer archived by admin',
                description: 'Login access was revoked while keeping customer records.',
                actor: $request->user(),
                subject: $customer,
            );
        }

        return redirect()
            ->route('admin.customers')
            ->with('status', "Customer \"{$customer->name}\" was archived successfully.");
    }

    public function restore(Request $request, User $customer)
    {
        abort_unless($request->user()?->role === User::ROLE_ADMIN, 403);
        abort_unless($customer->role === User::ROLE_CUSTOMER, 404);

        if ($customer->isArchivedCustomer()) {
            $customer->restoreArchivedAccount(
                performedByUserId: $request->user()?->id,
                reason: 'manual_restore',
            );

            app(CustomerActivityLogger::class)->record(
                customer: $customer,
                eventType: 'admin_restored_customer',
                title: 'Customer restored by admin',
                description: 'Login access was re-enabled and cancellation count was reset.',
                actor: $request->user(),
                subject: $customer,
            );
        }

        return redirect()
            ->route('admin.customers')
            ->with('status', "Customer \"{$customer->name}\" was restored successfully.");
    }

    private function buildCustomerTimeline(
        User $customer,
        Collection $quotations,
        Collection $inspectionRequests,
        Collection $serviceRequests,
        Collection $testimonies,
        Collection $activityLogs,
        Collection $archiveAudits,
        ?ChatConversation $chatConversation,
    ): Collection {
        $items = collect([
            [
                'occurred_at' => $customer->created_at,
                'label' => 'Account created',
                'description' => 'Customer registered with email '.$customer->email.'.',
                'badge' => 'Account',
            ],
        ]);

        foreach ($quotations as $quotation) {
            $items->push([
                'occurred_at' => $quotation->created_at,
                'label' => ucfirst((string) $quotation->quotation_type).' quotation created',
                'description' => 'Status: '.ucfirst((string) $quotation->status).($quotation->project_cost ? ' · Project cost: ₱'.number_format((float) $quotation->project_cost, 2) : ''),
                'badge' => 'Quotation',
            ]);
        }

        foreach ($inspectionRequests as $inspectionRequest) {
            $items->push([
                'occurred_at' => $inspectionRequest->created_at,
                'label' => 'Inspection request submitted',
                'description' => 'Status: '.ucfirst((string) $inspectionRequest->status).($inspectionRequest->date_needed ? ' · Preferred date: '.$inspectionRequest->date_needed : ''),
                'badge' => 'Inspection',
            ]);
        }

        foreach ($serviceRequests as $serviceRequest) {
            $items->push([
                'occurred_at' => $serviceRequest->created_at,
                'label' => ($serviceRequest->service_request_option_label ?: $serviceRequest->request_type ?: 'Service').' request submitted',
                'description' => 'Status: '.ucfirst((string) $serviceRequest->status).($serviceRequest->date_needed ? ' · Preferred date: '.$serviceRequest->date_needed->format('M d, Y') : ''),
                'badge' => 'Service',
            ]);
        }

        foreach ($testimonies as $testimony) {
            $items->push([
                'occurred_at' => $testimony->created_at,
                'label' => 'Feedback submitted',
                'description' => 'Rating: '.$testimony->rating.'/5 · Status: '.ucfirst((string) $testimony->status),
                'badge' => 'Feedback',
            ]);
        }

        foreach ($activityLogs as $log) {
            $items->push([
                'occurred_at' => $log->occurred_at,
                'label' => $log->title,
                'description' => $log->description,
                'badge' => 'Activity',
            ]);
        }

        foreach ($archiveAudits as $audit) {
            $items->push([
                'occurred_at' => $audit->created_at,
                'label' => ucfirst((string) $audit->action).' account',
                'description' => 'Reason: '.($audit->reason ?: 'Not specified'),
                'badge' => 'Account',
            ]);
        }

        if ($customer->delete_requested_at) {
            $items->push([
                'occurred_at' => $customer->delete_requested_at,
                'label' => 'Account deletion requested',
                'description' => $customer->delete_request_reason ?: 'No reason provided.',
                'badge' => 'Delete request',
            ]);
        }

        if ($chatConversation?->last_message_at) {
            $items->push([
                'occurred_at' => $chatConversation->last_message_at,
                'label' => 'Latest chat activity',
                'description' => 'Conversation status: '.ucfirst((string) $chatConversation->status),
                'badge' => 'Chat',
            ]);
        }

        return $items
            ->filter(fn (array $item): bool => $item['occurred_at'] !== null)
            ->sortByDesc('occurred_at')
            ->values()
            ->take(80);
    }
}
