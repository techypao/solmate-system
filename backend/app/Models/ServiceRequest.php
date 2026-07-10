<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceRequest extends Model
{
    use HasFactory;

    public const MANUAL_INSPECTION_REQUEST_TYPE = 'Manual Inspection Request';

    protected $fillable = [
        'user_id',
        'customer_name',
        'customer_email',
        'technician_id',
        'request_type',
        'service_request_option_id',
        'service_request_option_label',
        'details',
        'cancellation_note',
        'contact_number',
        'address',
        'address_details',
        'latitude',
        'longitude',
        'date_needed',
        'status',
        'technician_marked_done_at',
    ];

    protected function casts(): array
    {
        return [
            'date_needed' => 'date',
            'technician_marked_done_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function serviceRequestOption()
    {
        return $this->belongsTo(ServiceRequestOption::class);
    }

    public function testimonies(): HasMany
    {
        return $this->hasMany(Testimony::class);
    }

    public function completionReport()
    {
        return $this->hasOne(CompletionReport::class);
    }

    public function isManualInspectionRequest(): bool
    {
        return strcasecmp((string) $this->request_type, self::MANUAL_INSPECTION_REQUEST_TYPE) === 0;
    }

    public function displayCustomerName(): string
    {
        return $this->customer?->name
            ?: ($this->customer_name ?: 'Unknown customer');
    }

    public function displayCustomerEmail(): string
    {
        return $this->customer?->email
            ?: ($this->customer_email ?: 'Not available');
    }
}
