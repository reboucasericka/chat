<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DirectConversation extends Model
{
    use HasFactory;

    protected $fillable = ['user_one_id', 'user_two_id', 'created_by'];

    public function userOne(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_one_id');
    }

    public function userTwo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_two_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function hasParticipant(User $user): bool
    {
        return $this->user_one_id === $user->id || $this->user_two_id === $user->id;
    }

    public function counterpartFor(User $user): ?User
    {
        if ($this->user_one_id === $user->id) {
            return $this->relationLoaded('userTwo') ? $this->userTwo : $this->userTwo()->first();
        }

        if ($this->user_two_id === $user->id) {
            return $this->relationLoaded('userOne') ? $this->userOne : $this->userOne()->first();
        }

        return null;
    }
}
