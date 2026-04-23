<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sender_id',
        'chat_room_id',
        'direct_conversation_id',
        'type',
        'body',
        'attachment_path',
        'edited_at',
    ];

    protected function casts(): array
    {
        return [
            'edited_at' => 'datetime',
        ];
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function chatRoom(): BelongsTo
    {
        return $this->belongsTo(ChatRoom::class);
    }

    public function directConversation(): BelongsTo
    {
        return $this->belongsTo(DirectConversation::class);
    }

    public function reads(): HasMany
    {
        return $this->hasMany(MessageRead::class);
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(MessageReaction::class);
    }
}
