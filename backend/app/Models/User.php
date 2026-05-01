<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // Define Role Constants
    const ROLE_ADMIN = 'admin';

    const ROLE_CUSTOMER = 'customer';

    const ROLE_TECHNICIAN = 'technician';

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

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Role checking method
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
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
