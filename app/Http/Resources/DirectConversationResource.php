<?php

namespace App\Http\Resources;

use App\Services\Chat\ChatService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DirectConversationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $viewer = $request->user();
        $counterpart = $viewer ? $this->counterpartFor($viewer) : null;
        $lastMessage = $this->resource->relationLoaded('messages')
            ? $this->resource->messages->first()
            : null;
        $unreadCount = 0;

        if ($viewer) {
            $unreadCount = app(ChatService::class)->unreadCountFor($viewer, $this->resource);
        }

        return [
            'id' => $this->id,
            'created_by' => $this->created_by,
            'user_one' => UserResource::make($this->whenLoaded('userOne')),
            'user_two' => UserResource::make($this->whenLoaded('userTwo')),
            'counterpart' => $counterpart ? UserResource::make($counterpart) : null,
            'last_message' => $lastMessage ? MessageResource::make($lastMessage) : null,
            'unread_count' => $unreadCount,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
