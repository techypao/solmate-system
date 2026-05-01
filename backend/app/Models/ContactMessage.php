<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $fillable = [
        'full_name',
        'email',
        'phone_number',
        'subject',
        'message',
        'status',
    ];

    const STATUS_UNREAD   = 'unread';
    const STATUS_READ     = 'read';
    const STATUS_RESOLVED = 'resolved';
}
