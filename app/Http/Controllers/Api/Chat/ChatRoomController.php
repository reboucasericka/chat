<?php

namespace App\Http\Controllers\Api\Chat;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoomRequest;
use App\Http\Resources\ChatRoomResource;
use App\Models\ChatRoom;
use App\Models\User;
use App\Services\Chat\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatRoomController extends Controller
{
    public function __construct(private readonly ChatService $chatService)
    {
    }

    public function index(Request $request)
    {
        $rooms = ChatRoom::query()->with('members')->latest('updated_at')->get();

        return ChatRoomResource::collection($rooms);
    }

    public function store(StoreRoomRequest $request): ChatRoomResource
    {
        $this->authorize('create', ChatRoom::class);

        $room = $this->chatService->createRoom($request->user(), $request->validated());

        return ChatRoomResource::make($room);
    }

    public function show(Request $request, ChatRoom $room): ChatRoomResource
    {
        $this->authorize('view', $room);

        return ChatRoomResource::make($room->load('members'));
    }

    public function storeMember(Request $request, ChatRoom $room): JsonResponse
    {
        $this->authorize('manageMembers', $room);

        $data = $request->validate(['member_ids' => ['required', 'array'], 'member_ids.*' => ['integer', 'exists:users,id']]);
        $this->chatService->addMembers($room, $data['member_ids']);

        return response()->json(['ok' => true]);
    }

    public function destroyMember(Request $request, ChatRoom $room, User $user): JsonResponse
    {
        $this->authorize('manageMembers', $room);
        $this->chatService->removeMember($room, $user);

        return response()->json(['ok' => true]);
    }

    public function join(Request $request, ChatRoom $room): JsonResponse
    {
        $room->members()->syncWithoutDetaching([$request->user()->id]);

        return response()->json([
            'message' => 'joined',
            'data' => ['room_id' => $room->id],
        ]);
    }

    public function destroy(Request $request, ChatRoom $room): JsonResponse
    {
        $this->authorize('delete', $room);
        $room->delete();

        return response()->json(['ok' => true]);
    }
}
