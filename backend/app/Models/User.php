<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    // Define Role Constants
    const ROLE_ADMIN = 'admin';

    const ROLE_CUSTOMER = 'customer';

    const ROLE_TECHNICIAN = 'technician';

    const ADMIN_ROLE_SUPER_ADMIN = 'super_admin';

    const ADMIN_ROLE_OPERATIONS = 'operations_staff';

    const ADMIN_ROLE_SUPPORT = 'customer_support_staff';

    const ADMIN_ROLE_CONTENT = 'content_staff';

    const PERMISSION_MANAGE_STAFF = 'manage_staff';

    const PERMISSION_MANAGE_TECHNICIANS = 'manage_technicians';

    const PERMISSION_MANAGE_REQUESTS = 'manage_requests';

    const PERMISSION_MANAGE_WALKINS = 'manage_walkins';

    const PERMISSION_USE_ITEM_BUILDER = 'use_item_builder';

    const PERMISSION_MANAGE_PRICING = 'manage_pricing';

    const PERMISSION_VIEW_CUSTOMERS = 'view_customers';

    const PERMISSION_MANAGE_CUSTOMERS = 'manage_customers';

    const PERMISSION_MANAGE_SUPPORT_CHAT = 'manage_support_chat';

    const PERMISSION_MANAGE_CONTACT_MESSAGES = 'manage_contact_messages';

    const PERMISSION_MANAGE_CONTENT = 'manage_content';

    const PERMISSION_VIEW_REPORTS = 'view_reports';

    const PERMISSION_MANAGE_SETTINGS = 'manage_settings';

    const PERMISSION_VIEW_NOTIFICATIONS = 'view_notifications';

    const PERMISSION_MANAGE_OWN_PROFILE = 'manage_own_profile';

    const ADMIN_ROLE_LABELS = [
        self::ADMIN_ROLE_SUPER_ADMIN => 'Super Admin',
        self::ADMIN_ROLE_OPERATIONS => 'Operations Staff',
        self::ADMIN_ROLE_SUPPORT => 'Customer Support Staff',
        self::ADMIN_ROLE_CONTENT => 'Content Staff',
    ];

    const ADMIN_ROLE_PERMISSIONS = [
        self::ADMIN_ROLE_OPERATIONS => [
            self::PERMISSION_MANAGE_TECHNICIANS,
            self::PERMISSION_MANAGE_REQUESTS,
            self::PERMISSION_MANAGE_WALKINS,
            self::PERMISSION_USE_ITEM_BUILDER,
            self::PERMISSION_MANAGE_PRICING,
            self::PERMISSION_VIEW_NOTIFICATIONS,
            self::PERMISSION_MANAGE_OWN_PROFILE,
        ],
        self::ADMIN_ROLE_SUPPORT => [
            self::PERMISSION_VIEW_CUSTOMERS,
            self::PERMISSION_MANAGE_SUPPORT_CHAT,
            self::PERMISSION_MANAGE_CONTACT_MESSAGES,
            self::PERMISSION_VIEW_NOTIFICATIONS,
            self::PERMISSION_MANAGE_OWN_PROFILE,
        ],
        self::ADMIN_ROLE_CONTENT => [
            self::PERMISSION_MANAGE_CONTENT,
            self::PERMISSION_MANAGE_OWN_PROFILE,
        ],
    ];

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
        'admin_role',
        'fcm_token',
        'archived_at',
        'is_archived',
        'last_login_at',
        'archive_warning_sent_at',
        'cancellation_count',
        'delete_requested_at',
        'delete_request_reason',
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
        'fcm_token',
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
            'cancellation_count' => 'integer',
            'delete_requested_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Role checking method
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function isAdminUser(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isSuperAdmin(): bool
    {
        return $this->isAdminUser() && $this->adminRole() === self::ADMIN_ROLE_SUPER_ADMIN;
    }

    public function adminRole(): ?string
    {
        if (! $this->isAdminUser()) {
            return null;
        }

        return $this->admin_role ?: self::ADMIN_ROLE_SUPER_ADMIN;
    }

    public function adminRoleLabel(): string
    {
        return self::ADMIN_ROLE_LABELS[$this->adminRole()] ?? 'Admin Staff';
    }

    public static function adminRoleOptions(): array
    {
        return self::ADMIN_ROLE_LABELS;
    }

    public function hasAdminPermission(string $permission): bool
    {
        if (! $this->isAdminUser()) {
            return false;
        }

        if ($this->isSuperAdmin()) {
            return true;
        }

        return in_array($permission, self::ADMIN_ROLE_PERMISSIONS[$this->adminRole()] ?? [], true);
    }

    public function hasAnyAdminPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasAdminPermission($permission)) {
                return true;
            }
        }

        return false;
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

    public function incrementCancellationCount(?int $performedByUserId = null): int
    {
        return DB::transaction(function () use ($performedByUserId): int {
            $this->increment('cancellation_count');
            $this->refresh();

            if ($this->cancellation_count >= 3 && ! $this->isArchivedCustomer()) {
                $this->archiveAccount(
                    performedByUserId: $performedByUserId,
                    reason: 'cancellation_limit_reached',
                    context: ['cancellation_count' => $this->cancellation_count],
                );
            }

            return (int) $this->cancellation_count;
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
                'cancellation_count' => 0,
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
