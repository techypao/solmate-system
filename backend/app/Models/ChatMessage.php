<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use HasFactory;

    public const SENDER_USER = 'user';

    public const SENDER_BOT = 'bot';

    public const SENDER_ADMIN = 'admin';

    public const SENDER_SYSTEM = 'system';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'chat_conversation_id',
        'sender_user_id',
        'sender_type',
        'body',
        'metadata',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function conversation()
    {
        return $this->belongsTo(ChatConversation::class, 'chat_conversation_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }
}