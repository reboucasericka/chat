<?php

namespace App\Http\Controllers\Api\Chat;

use App\Http\Controllers\Controller;
use App\Http\Resources\DirectConversationResource;
use App\Models\DirectConversation;
use App\Models\User;
use App\Services\Chat\ChatService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DirectConversationController extends Controller
{
    public function __construct(private readonly ChatService $chatService)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        $conversations = DirectConversation::query()
            ->where(fn ($q) => $q->where('user_one_id', $user->id)->orWhere('user_two_id', $user->id))
            ->with([
                'userOne',
                'userTwo',
                'messages' => fn ($q) => $q->with('sender')->latest('id')->limit(1),
            ])
            ->latest('updated_at')
            ->get();

        return DirectConversationResource::collection($conversations);
    }

    public function store(Request $request): DirectConversationResource|JsonResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $user = User::query()->findOrFail($data['user_id']);

        if ($request->user()->is($user)) {
            return response()->json(['message' => 'Nao e permitido abrir conversa consigo mesmo.'], 422);
        }

        $conversation = $this->chatService->findOrCreateDirectConversation($request->user(), $user);

        return DirectConversationResource::make($conversation->load(['userOne', 'userTwo', 'messages.sender']));
    }

    public function storeLegacy(Request $request, User $user): DirectConversationResource|JsonResponse
    {
        if ($request->user()->is($user)) {
            return response()->json(['message' => 'Nao e permitido abrir conversa consigo mesmo.'], 422);
        }

        $conversation = $this->chatService->findOrCreateDirectConversation($request->user(), $user);

        return DirectConversationResource::make($conversation->load(['userOne', 'userTwo', 'messages.sender']));
    }

    public function show(Request $request, DirectConversation $conversation): DirectConversationResource
    {
        $this->authorize('view', $conversation);

        return DirectConversationResource::make($conversation->load(['userOne', 'userTwo']));
    }

    public function destroy(Request $request, DirectConversation $conversation): JsonResponse
    {
        $this->authorize('delete', $conversation);
        $conversation->delete();

        return response()->json(['ok' => true]);
    }
}
