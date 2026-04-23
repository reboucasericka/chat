<?php

namespace App\Http\Controllers\Api\Chat;

use App\Events\DirectMessageSent;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDirectMessageRequest;
use App\Http\Resources\MessageResource;
use App\Models\DirectConversation;
use App\Services\Chat\ChatService;
use Illuminate\Http\Request;

class DirectMessageController extends Controller
{
    public function __construct(private readonly ChatService $chatService)
    {
    }

    public function index(Request $request, DirectConversation $conversation)
    {
        $this->authorize('view', $conversation);

        return MessageResource::collection(
            $conversation->messages()->with(['sender', 'reactions'])->latest('id')->limit(100)->get()->reverse()->values()
        );
    }

    public function store(StoreDirectMessageRequest $request, DirectConversation $conversation): MessageResource
    {
        $this->authorize('sendMessage', $conversation);

        $message = $this->chatService->sendDirectMessage($request->user(), $conversation, $request->validated());
        DirectMessageSent::dispatch($message);

        $message->load('reactions');

        return MessageResource::make($message);
    }
}
