<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ConversationRead extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'conversation_type',
        'conversation_id',
        'last_read_message_id',
        'last_read_at',
    ];

    protected function casts(): array
    {
        return ['last_read_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function conversation(): MorphTo
    {
        return $this->morphTo();
    }

    public function lastReadMessage(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'last_read_message_id');
    }
}
