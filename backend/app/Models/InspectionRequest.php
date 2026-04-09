<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InspectionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'details',
        'date_needed',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}