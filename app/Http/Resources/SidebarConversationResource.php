<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SidebarConversationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this['id'],
            'kind' => $this['kind'],
            'title' => $this['title'],
            'snippet' => $this['snippet'],
            'unread_count' => $this['unread_count'],
            'counterpart_status' => $this['counterpart_status'] ?? null,
            'counterpart_is_online' => $this['counterpart_is_online'] ?? null,
            'is_member' => $this['is_member'] ?? true,
            'updated_at' => $this['updated_at'],
        ];
    }
}
