<?php

namespace App\Http\Controllers\Api\Chat;

use App\Http\Controllers\Controller;
use App\Models\DirectConversation;
use App\Services\Chat\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DirectReadController extends Controller
{
    public function __construct(private readonly ChatService $chatService)
    {
    }

    public function __invoke(Request $request, DirectConversation $conversation): JsonResponse
    {
        $this->authorize('view', $conversation);
        $this->chatService->markConversationAsRead($request->user(), $conversation);

        return response()->json(['ok' => true]);
    }
}
