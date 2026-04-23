<?php

namespace App\Services\Chat;

use App\Models\ChatRoom;
use App\Models\ConversationRead;
use App\Models\DirectConversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;

class ChatService
{
    public function createRoom(User $admin, array $data): ChatRoom
    {
        $room = ChatRoom::query()->create([
            'name' => $data['name'],
            'avatar' => $data['avatar'] ?? null,
            'created_by' => $admin->id,
        ]);

        $memberIds = collect($data['member_ids'] ?? [])
            ->merge($data['user_ids'] ?? [])
            ->push($admin->id)
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $room->members()->sync($memberIds);

        return $room->load('members');
    }

    public function addMembers(ChatRoom $room, array $memberIds): void
    {
        $room->members()->syncWithoutDetaching($memberIds);
    }

    public function removeMember(ChatRoom $room, User $member): void
    {
        $room->members()->detach($member->id);
    }

    public function findOrCreateDirectConversation(User $authUser, User $otherUser): DirectConversation
    {
        [$oneId, $twoId] = collect([$authUser->id, $otherUser->id])->sort()->values()->all();

        return DirectConversation::query()->firstOrCreate(
            [
                'user_one_id' => $oneId,
                'user_two_id' => $twoId,
            ],
            [
                'created_by' => $authUser->id,
            ]
        );
    }

    public function sendRoomMessage(User $sender, ChatRoom $room, array $data): Message
    {
        return $this->createMessage($sender, [
            'chat_room_id' => $room->id,
            'type' => 'text',
            'body' => $data['body'] ?? null,
            'attachment_path' => $this->storeAttachment($data['attachment'] ?? $data['file'] ?? null),
        ]);
    }

    public function sendDirectMessage(User $sender, DirectConversation $conversation, array $data): Message
    {
        return $this->createMessage($sender, [
            'direct_conversation_id' => $conversation->id,
            'type' => 'text',
            'body' => $data['body'],
            'attachment_path' => null,
        ]);
    }

    public function markConversationAsRead(User $user, ChatRoom|DirectConversation $conversation): void
    {
        $latestMessage = Message::query()
            ->when($conversation instanceof ChatRoom, fn (Builder $q) => $q->where('chat_room_id', $conversation->id))
            ->when($conversation instanceof DirectConversation, fn (Builder $q) => $q->where('direct_conversation_id', $conversation->id))
            ->latest('id')
            ->first();

        ConversationRead::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'conversation_type' => $conversation::class,
                'conversation_id' => $conversation->id,
            ],
            [
                'last_read_message_id' => $latestMessage?->id,
                'last_read_at' => now(),
            ]
        );
    }

    public function unreadCountFor(User $user, ChatRoom|DirectConversation $conversation): int
    {
        $read = ConversationRead::query()
            ->where('user_id', $user->id)
            ->where('conversation_type', $conversation::class)
            ->where('conversation_id', $conversation->id)
            ->first();

        return Message::query()
            ->when($conversation instanceof ChatRoom, fn (Builder $q) => $q->where('chat_room_id', $conversation->id))
            ->when($conversation instanceof DirectConversation, fn (Builder $q) => $q->where('direct_conversation_id', $conversation->id))
            ->when($read?->last_read_message_id, fn (Builder $q, int $id) => $q->where('id', '>', $id))
            ->where('sender_id', '!=', $user->id)
            ->count();
    }

    private function createMessage(User $sender, array $data): Message
    {
        return Message::query()->create([
            'sender_id' => $sender->id,
            'chat_room_id' => $data['chat_room_id'] ?? null,
            'direct_conversation_id' => $data['direct_conversation_id'] ?? null,
            'type' => $data['type'] ?? 'text',
            'body' => $data['body'],
            'attachment_path' => $data['attachment_path'],
        ])->load('sender');
    }

    private function storeAttachment(?UploadedFile $file): ?string
    {
        if (! $file) {
            return null;
        }

        return $file->store('chat-attachments', 'public');
    }
}
