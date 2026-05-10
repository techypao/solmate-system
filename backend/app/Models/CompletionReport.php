<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompletionReport extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';

    protected $fillable = [
        'service_request_id',
        'inspection_request_id',
        'technician_id',
        'approved_by',
        'report_text',
        'findings',
        'recommendations',
        'status',
        'completed_at',
        'submitted_at',
        'approved_at',
    ];

    public function photos()
    {
        return $this->hasMany(CompletionReportPhoto::class);
    }

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function inspectionRequest()
    {
        return $this->belongsTo(InspectionRequest::class);
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
