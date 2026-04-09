<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ServiceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'request_type',
        'details',
        'date_needed',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}