<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // Define Role Constants
    const ROLE_ADMIN = 'admin';

    const ROLE_CUSTOMER = 'customer';

    const ROLE_TECHNICIAN = 'technician';

    const DEFAULT_ARCHIVED_ACCOUNT_MESSAGE = 'Your account has been archived due to inactivity. Please contact support to reactivate.';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'password',
        'role',
        'archived_at',
        'is_archived',
        'last_login_at',
        'archive_warning_sent_at',
        'address',
        'contact_number',
        'landline_number',
        'profile_picture',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $user): void {
            if ($user->isDirty('name') && ! $user->isDirty('first_name') && ! $user->isDirty('last_name')) {
                [$firstName, $lastName] = self::splitName((string) $user->name);

                $user->first_name = $firstName !== '' ? $firstName : null;
                $user->last_name = $lastName !== '' ? $lastName : null;
            }

            if ($user->isDirty('first_name') || $user->isDirty('last_name')) {
                $firstName = trim((string) $user->first_name);
                $lastName = trim((string) $user->last_name);
                $fullName = trim(implode(' ', array_filter([$firstName, $lastName])));

                if ($fullName !== '') {
                    $user->name = $fullName;
                }
            }

            if ($user->isDirty('landline_number')) {
                $landlineNumber = trim((string) $user->landline_number);
                $user->landline_number = $landlineNumber !== '' ? $landlineNumber : null;
            }

            if ($user->isDirty('archived_at') && ! $user->isDirty('is_archived')) {
                $user->is_archived = $user->archived_at !== null;
            }

            if ($user->isDirty('is_archived') && ! $user->isDirty('archived_at')) {
                $user->archived_at = $user->is_archived ? ($user->archived_at ?? now()) : null;
            }
        });
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = [
        'profile_picture_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'archived_at' => 'datetime',
            'is_archived' => 'boolean',
            'last_login_at' => 'datetime',
            'archive_warning_sent_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Role checking method
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function isArchivedCustomer(): bool
    {
        return $this->role === self::ROLE_CUSTOMER && $this->isArchived();
    }

    public function isArchived(): bool
    {
        return (bool) $this->is_archived || $this->archived_at !== null;
    }

    public static function archivedAccountMessage(): string
    {
        return (string) config('customer_archiving.blocked_message', self::DEFAULT_ARCHIVED_ACCOUNT_MESSAGE);
    }

    public function markLoginRecorded(): void
    {
        $this->forceFill([
            'last_login_at' => now(),
            'archive_warning_sent_at' => null,
        ])->save();
    }

    public function archiveAccount(?int $performedByUserId = null, string $reason = 'manual_archive', array $context = []): void
    {
        if ($this->isArchivedCustomer()) {
            return;
        }

        DB::transaction(function () use ($performedByUserId, $reason, $context): void {
            $archivedAt = now();

            $this->forceFill([
                'is_archived' => true,
                'archived_at' => $archivedAt,
            ])->save();

            $this->tokens()->delete();

            DB::table('sessions')
                ->where('user_id', $this->id)
                ->delete();

            CustomerArchiveAudit::query()->create([
                'user_id' => $this->id,
                'performed_by_user_id' => $performedByUserId,
                'action' => 'archived',
                'reason' => $reason,
                'context' => array_merge($context, [
                    'archived_at' => $archivedAt->toDateTimeString(),
                ]),
            ]);
        });
    }

    public function restoreArchivedAccount(?int $performedByUserId = null, string $reason = 'manual_restore', array $context = []): void
    {
        if (! $this->isArchivedCustomer()) {
            return;
        }

        DB::transaction(function () use ($performedByUserId, $reason, $context): void {
            $restoredAt = now();

            $this->forceFill([
                'is_archived' => false,
                'archived_at' => null,
                'archive_warning_sent_at' => null,
                'last_login_at' => $restoredAt,
            ])->save();

            CustomerArchiveAudit::query()->create([
                'user_id' => $this->id,
                'performed_by_user_id' => $performedByUserId,
                'action' => 'restored',
                'reason' => $reason,
                'context' => array_merge($context, [
                    'restored_at' => $restoredAt->toDateTimeString(),
                ]),
            ]);
        });
    }

    public function customerArchiveAudits()
    {
        return $this->hasMany(CustomerArchiveAudit::class);
    }

    public function chatConversation()
    {
        return $this->hasOne(ChatConversation::class, 'customer_user_id');
    }

    public function adminChatConversations()
    {
        return $this->hasMany(ChatConversation::class, 'admin_user_id');
    }

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class, 'user_id');
    }

    public function assignedServiceRequests()
    {
        return $this->hasMany(ServiceRequest::class, 'technician_id');
    }

    public function inspectionRequests()
    {
        return $this->hasMany(InspectionRequest::class, 'user_id');
    }

    public function assignedInspectionRequests()
    {
        return $this->hasMany(InspectionRequest::class, 'technician_id');
    }

    public function testimonies()
    {
        return $this->hasMany(Testimony::class);
    }

    public function getProfilePictureUrlAttribute(): ?string
    {
        if (! $this->profile_picture) {
            return null;
        }

        return Storage::url($this->profile_picture);
    }

    /**
     * @return array{0: string, 1: string}
     */
    private static function splitName(string $name): array
    {
        $normalizedName = trim(preg_replace('/\s+/', ' ', $name) ?? '');

        if ($normalizedName === '') {
            return ['', ''];
        }

        $parts = explode(' ', $normalizedName);
        $firstName = array_shift($parts) ?? '';
        $lastName = implode(' ', $parts);

        return [$firstName, $lastName];
    }
}
