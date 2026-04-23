<?php

namespace App\Http\Controllers\Api\Chat;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\MessageReaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageReactionController extends Controller
{
    public function store(Request $request, Message $message): JsonResponse
    {
        $this->authorize('view', $message);

        $request->validate([
            'emoji' => 'required|string|max:16',
        ]);

        MessageReaction::query()->firstOrCreate([
            'message_id' => $message->id,
            'user_id' => $request->user()->id,
            'emoji' => $request->input('emoji'),
        ]);

        return response()->json(['ok' => true]);
    }

    public function destroy(Request $request, Message $message): JsonResponse
    {
        $this->authorize('view', $message);

        $request->validate([
            'emoji' => 'required|string|max:16',
        ]);

        MessageReaction::query()->where([
            'message_id' => $message->id,
            'user_id' => $request->user()->id,
            'emoji' => $request->input('emoji'),
        ])->delete();

        return response()->json(['ok' => true]);
    }
}
