<?php

namespace App\Http\Controllers\Api\Chat;

use App\Events\PresenceUpdated;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PresenceController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->forceFill(['last_seen_at' => now()])->save();

        PresenceUpdated::dispatch($user);

        return response()->json(['ok' => true, 'last_seen_at' => $user->last_seen_at]);
    }
}
