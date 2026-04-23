<?php

namespace App\Http\Controllers\Api\Chat;

use App\Http\Controllers\Controller;
use App\Http\Resources\SidebarConversationResource;
use App\Models\ChatRoom;
use App\Models\DirectConversation;
use App\Services\Chat\ChatService;
use Illuminate\Http\Request;

class ChatSidebarController extends Controller
{
    public function __construct(private readonly ChatService $chatService)
    {
    }

    public function __invoke(Request $request)
    {
        $user = $request->user();

        $rooms = ChatRoom::query()
            ->with([
                'messages' => fn ($q) => $q->latest('id')->limit(1),
            ])
            ->withCount([
                'members as is_member' => fn ($q) => $q->where('users.id', $user->id),
            ])
            ->latest('updated_at')
            ->get()
            ->map(function ($room) use ($user) {
                $isMember = (bool) $room->is_member;

                return [
                    'id' => $room->id,
                    'kind' => 'room',
                    'title' => $room->name,
                    'snippet' => $isMember ? $room->messages->first()?->body : null,
                    'unread_count' => $isMember ? $this->chatService->unreadCountFor($user, $room) : 0,
                    'is_member' => $isMember,
                    'updated_at' => $room->updated_at,
                ];
            });

        $directs = DirectConversation::query()
            ->where(fn ($q) => $q->where('user_one_id', $user->id)->orWhere('user_two_id', $user->id))
            ->with(['messages' => fn ($q) => $q->latest('id')->limit(1), 'userOne', 'userTwo'])
            ->get()
            ->map(function ($conversation) use ($user) {
                $counterpart = $conversation->counterpartFor($user);
                $isOnline = $counterpart?->last_seen_at?->gt(now()->subMinutes(2)) ?? false;

                return [
                    'id' => $conversation->id,
                    'kind' => 'direct',
                    'title' => $counterpart?->name,
                    'snippet' => $conversation->messages->first()?->body,
                    'unread_count' => $this->chatService->unreadCountFor($user, $conversation),
                    'counterpart_status' => $isOnline ? 'online' : 'offline',
                    'counterpart_is_online' => $isOnline,
                    'is_member' => true,
                    'updated_at' => $conversation->updated_at,
                ];
            });

        return SidebarConversationResource::collection($rooms->concat($directs)->sortByDesc('updated_at')->values());
    }
}
