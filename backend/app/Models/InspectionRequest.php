<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InspectionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'technician_id',
        'details',
        'contact_number',
        'address',
        'date_needed',
        'status',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function testimonies(): HasMany
    {
        return $this->hasMany(Testimony::class);
    }
}
