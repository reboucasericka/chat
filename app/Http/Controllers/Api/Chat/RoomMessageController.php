<?php

namespace App\Http\Controllers\Api\Chat;

use App\Events\RoomMessageSent;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMessageRequest;
use App\Http\Resources\MessageResource;
use App\Models\ChatRoom;
use App\Services\Chat\ChatService;
use Illuminate\Http\Request;

class RoomMessageController extends Controller
{
    public function __construct(private readonly ChatService $chatService)
    {
    }

    public function index(Request $request, ChatRoom $room)
    {
        $this->authorize('sendMessage', $room);

        return MessageResource::collection(
            $room->messages()->with(['sender', 'reactions'])->latest('id')->limit(100)->get()->reverse()->values()
        );
    }

    public function store(StoreMessageRequest $request, ChatRoom $room): MessageResource
    {
        $this->authorize('sendMessage', $room);

        $message = $this->chatService->sendRoomMessage($request->user(), $room, $request->validated());
        RoomMessageSent::dispatch($message);

        $message->load('reactions');

        return MessageResource::make($message);
    }
}
