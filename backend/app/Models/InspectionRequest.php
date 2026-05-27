<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class InspectionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'technician_id',
        'details',
        'cancellation_note',
        'contact_number',
        'address',
        'address_details',
        'latitude',
        'longitude',
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

    public function completionReport()
    {
        return $this->hasOne(CompletionReport::class);
    }

    public function finalQuotation(): HasOne
    {
        return $this->hasOne(Quotation::class)->where('quotation_type', 'final');
    }
}
