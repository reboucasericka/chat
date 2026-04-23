<?php

use App\Http\Controllers\Api\Chat\ChatRoomController;
use App\Http\Controllers\Api\Chat\ChatSidebarController;
use App\Http\Controllers\Api\Chat\ChatUserController;
use App\Http\Controllers\Api\Chat\DirectConversationController;
use App\Http\Controllers\Api\Chat\DirectMessageController;
use App\Http\Controllers\Api\Chat\DirectReadController;
use App\Http\Controllers\Api\Chat\MessageReactionController;
use App\Http\Controllers\Api\Chat\PresenceController;
use App\Http\Controllers\Api\Chat\RoomMessageController;
use App\Http\Controllers\Api\Chat\RoomReadController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('chat')->group(function (): void {
    Route::get('/rooms', [ChatRoomController::class, 'index']);
    Route::post('/rooms', [ChatRoomController::class, 'store'])->middleware('role:admin');
    Route::get('/rooms/{room}', [ChatRoomController::class, 'show']);
    Route::post('/rooms/{room}/members', [ChatRoomController::class, 'storeMember'])->middleware('role:admin');
    Route::delete('/rooms/{room}/members/{user}', [ChatRoomController::class, 'destroyMember'])->middleware('role:admin');
    Route::get('/rooms/{room}/messages', [RoomMessageController::class, 'index']);
    Route::post('/rooms/{room}/messages', [RoomMessageController::class, 'store']);
    Route::post('/rooms/{room}/read', RoomReadController::class);
    Route::post('/rooms/{room}/join', [ChatRoomController::class, 'join']);
    Route::delete('/rooms/{room}', [ChatRoomController::class, 'destroy']);

    Route::get('/users', [ChatUserController::class, 'index']);
    Route::get('/admin/users', [ChatUserController::class, 'adminIndex'])->middleware('role:admin');
    Route::post('/users', [ChatUserController::class, 'store'])->middleware('role:admin');

    Route::get('/direct-conversations', [DirectConversationController::class, 'index']);
    Route::post('/direct-conversations', [DirectConversationController::class, 'store']);
    Route::get('/direct-conversations/{conversation}', [DirectConversationController::class, 'show']);
    Route::get('/direct-conversations/{conversation}/messages', [DirectMessageController::class, 'index']);
    Route::post('/direct-conversations/{conversation}/messages', [DirectMessageController::class, 'store']);
    Route::post('/direct-conversations/{conversation}/read', DirectReadController::class);
    Route::delete('/direct-conversations/{conversation}', [DirectConversationController::class, 'destroy']);

    Route::get('/direct', [DirectConversationController::class, 'index']);
    Route::post('/direct/{user}', [DirectConversationController::class, 'storeLegacy']);
    Route::get('/direct/{conversation}', [DirectConversationController::class, 'show']);
    Route::get('/direct/{conversation}/messages', [DirectMessageController::class, 'index']);
    Route::post('/direct/{conversation}/messages', [DirectMessageController::class, 'store']);
    Route::post('/direct/{conversation}/read', DirectReadController::class);

    Route::post('/presence/ping', PresenceController::class);
    Route::get('/sidebar', ChatSidebarController::class);

    Route::post('/messages/{message}/reactions', [MessageReactionController::class, 'store']);
    Route::delete('/messages/{message}/reactions', [MessageReactionController::class, 'destroy']);
});
