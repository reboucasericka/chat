<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => $this->avatar,
            'role' => $this->role,
            'last_seen_at' => $this->last_seen_at,
            'is_online' => $this->last_seen_at?->gt(now()->subMinutes(2)) ?? false,
        ];
    }
}
