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
        'email',
        'password',
        'role',
        'address',
        'contact_number',
        'profile_picture',
    ];

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
    public function hasRole($role)
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
}
