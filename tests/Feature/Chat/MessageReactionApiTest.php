<?php

use App\Models\ChatRoom;
use App\Models\Message;
use App\Models\MessageReaction;
use App\Models\User;

it('membro adiciona reacao a mensagem de sala', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $member = User::factory()->create();

    $room = ChatRoom::query()->create(['name' => 'Geral', 'created_by' => $admin->id]);
    $room->members()->sync([$admin->id, $member->id]);

    $message = Message::query()->create([
        'sender_id' => $admin->id,
        'chat_room_id' => $room->id,
        'type' => 'text',
        'body' => 'ola',
    ]);

    $this->actingAs($member)
        ->postJson("/api/chat/messages/{$message->id}/reactions", ['emoji' => '👍'])
        ->assertOk()
        ->assertJson(['ok' => true]);

    expect(MessageReaction::query()->count())->toBe(1);
});

it('utilizador remove a sua reacao', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $member = User::factory()->create();

    $room = ChatRoom::query()->create(['name' => 'Geral', 'created_by' => $admin->id]);
    $room->members()->sync([$admin->id, $member->id]);

    $message = Message::query()->create([
        'sender_id' => $admin->id,
        'chat_room_id' => $room->id,
        'type' => 'text',
        'body' => 'ola',
    ]);

    MessageReaction::query()->create([
        'message_id' => $message->id,
        'user_id' => $member->id,
        'emoji' => '👍',
    ]);

    $this->actingAs($member)
        ->deleteJson("/api/chat/messages/{$message->id}/reactions", [
            'emoji' => '👍',
        ])
        ->assertOk();

    expect(MessageReaction::query()->count())->toBe(0);
});

it('fora da sala nao adiciona reacao', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $outsider = User::factory()->create();

    $room = ChatRoom::query()->create(['name' => 'Geral', 'created_by' => $admin->id]);
    $room->members()->sync([$admin->id]);

    $message = Message::query()->create([
        'sender_id' => $admin->id,
        'chat_room_id' => $room->id,
        'type' => 'text',
        'body' => 'ola',
    ]);

    $this->actingAs($outsider)
        ->postJson("/api/chat/messages/{$message->id}/reactions", ['emoji' => '👍'])
        ->assertForbidden();
});
