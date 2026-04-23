<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'body' => $this->body,
            'attachment_path' => $this->attachment_path,
            'edited_at' => $this->edited_at,
            'created_at' => $this->created_at,
            'deleted_at' => $this->deleted_at,
            'sender' => UserResource::make($this->whenLoaded('sender')),
            'reactions' => $this->whenLoaded(
                'reactions',
                fn () => $this->reactions
                    ->groupBy('emoji')
                    ->map(function ($items) {
                        return [
                            'emoji' => $items->first()->emoji,
                            'count' => $items->count(),
                            'reacted' => $items->contains('user_id', auth()->id()),
                        ];
                    })
                    ->values(),
                [],
            ),
        ];
    }
}
