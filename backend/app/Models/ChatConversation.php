<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatConversation extends Model
{
    use HasFactory;

    public const STATUS_BOT = 'bot';

    public const STATUS_ADMIN = 'admin';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'customer_user_id',
        'admin_user_id',
        'status',
        'bot_fallback_count',
        'takeover_requested_at',
        'escalated_at',
        'admin_joined_at',
        'last_message_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'takeover_requested_at' => 'datetime',
            'escalated_at' => 'datetime',
            'admin_joined_at' => 'datetime',
            'last_message_at' => 'datetime',
        ];
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_user_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class)->orderBy('id');
    }

    public function isUnderAdminControl(): bool
    {
        return $this->status === self::STATUS_ADMIN;
    }

    public function isAwaitingAdmin(): bool
    {
        return $this->isUnderAdminControl() && $this->admin_user_id === null;
    }
}