<?php

namespace App\Services;

use App\Models\CustomerActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class CustomerActivityLogger
{
    public function record(
        User|int $customer,
        string $eventType,
        string $title,
        ?string $description = null,
        User|int|null $actor = null,
        ?Model $subject = null,
        array $metadata = [],
    ): CustomerActivityLog {
        $customerId = $customer instanceof User ? $customer->id : $customer;
        $actorId = $actor instanceof User ? $actor->id : $actor;
        $actorRole = $actor instanceof User ? $actor->role : null;

        return CustomerActivityLog::query()->create([
            'customer_user_id' => $customerId,
            'actor_user_id' => $actorId,
            'actor_role' => $actorRole,
            'event_type' => $eventType,
            'title' => $title,
            'description' => $description,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'metadata' => $metadata !== [] ? $metadata : null,
            'occurred_at' => now(),
        ]);
    }
}
