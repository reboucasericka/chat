<?php

namespace App\Http\Controllers\Api\Chat;

use App\Http\Controllers\Controller;
use App\Models\ChatRoom;
use App\Services\Chat\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoomReadController extends Controller
{
    public function __construct(private readonly ChatService $chatService)
    {
    }

    public function __invoke(Request $request, ChatRoom $room): JsonResponse
    {
        $this->authorize('sendMessage', $room);
        $this->chatService->markConversationAsRead($request->user(), $room);

        return response()->json(['ok' => true]);
    }
}
