<?php

use App\Models\ChatRoom;
use App\Models\Message;
use App\Models\User;

it('cria sala com user_ids e associa admin mais utilizadores no pivot', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $u1 = User::factory()->create();
    $u2 = User::factory()->create();

    $response = $this->actingAs($admin)->postJson('/api/chat/rooms', [
        'name' => 'Room User Ids',
        'user_ids' => [$u1->id, $u2->id],
    ]);

    $response->assertCreated();
    $roomId = $response->json('data.id');

    expect($roomId)->not->toBeNull();
    $this->assertDatabaseHas('chat_room_user', ['chat_room_id' => $roomId, 'user_id' => $admin->id]);
    $this->assertDatabaseHas('chat_room_user', ['chat_room_id' => $roomId, 'user_id' => $u1->id]);
    $this->assertDatabaseHas('chat_room_user', ['chat_room_id' => $roomId, 'user_id' => $u2->id]);
});

it('cria sala com member_ids e associa admin mais utilizadores no pivot', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $u1 = User::factory()->create();
    $u2 = User::factory()->create();

    $response = $this->actingAs($admin)->postJson('/api/chat/rooms', [
        'name' => 'Room Member Ids',
        'member_ids' => [$u1->id, $u2->id],
    ]);

    $response->assertCreated();
    $roomId = $response->json('data.id');

    expect($roomId)->not->toBeNull();
    $this->assertDatabaseHas('chat_room_user', ['chat_room_id' => $roomId, 'user_id' => $admin->id]);
    $this->assertDatabaseHas('chat_room_user', ['chat_room_id' => $roomId, 'user_id' => $u1->id]);
    $this->assertDatabaseHas('chat_room_user', ['chat_room_id' => $roomId, 'user_id' => $u2->id]);
});

it('deduplica ids enviados na criacao da sala', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $u1 = User::factory()->create();

    $response = $this->actingAs($admin)->postJson('/api/chat/rooms', [
        'name' => 'Room Dedupe',
        'user_ids' => [$u1->id, $u1->id, $admin->id],
        'member_ids' => [$u1->id, $admin->id],
    ]);

    $response->assertCreated();
    $roomId = $response->json('data.id');

    $memberIds = ChatRoom::query()->findOrFail($roomId)->members()->pluck('users.id')->sort()->values()->all();
    expect($memberIds)->toBe(collect([$admin->id, $u1->id])->sort()->values()->all());
});

it('sidebar mostra todas as salas e membership correto', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $included = User::factory()->create();
    $excluded = User::factory()->create();

    $createResponse = $this->actingAs($admin)->postJson('/api/chat/rooms', [
        'name' => 'Room Visibility',
        'user_ids' => [$included->id],
    ]);

    $createResponse->assertCreated();

    $includedSidebar = $this->actingAs($included)->getJson('/api/chat/sidebar')->assertOk();
    $includedRoom = collect($includedSidebar->json('data'))
        ->where('kind', 'room')
        ->firstWhere('title', 'Room Visibility');
    expect($includedRoom)->not->toBeNull();
    expect($includedRoom['is_member'])->toBeTrue();

    $excludedSidebar = $this->actingAs($excluded)->getJson('/api/chat/sidebar')->assertOk();
    $excludedRoom = collect($excludedSidebar->json('data'))
        ->where('kind', 'room')
        ->firstWhere('title', 'Room Visibility');
    expect($excludedRoom)->not->toBeNull();
    expect($excludedRoom['is_member'])->toBeFalse();
});

it('membro consegue abrir sala e ver mensagens', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $member = User::factory()->create();

    $room = ChatRoom::query()->create([
        'name' => 'Sala Produto',
        'created_by' => $admin->id,
    ]);
    $room->members()->sync([$admin->id, $member->id]);

    Message::query()->create([
        'sender_id' => $admin->id,
        'chat_room_id' => $room->id,
        'type' => 'text',
        'body' => 'Bem-vindo a sala',
    ]);

    $this->actingAs($member)
        ->getJson("/api/chat/rooms/{$room->id}/messages")
        ->assertOk()
        ->assertJsonPath('data.0.body', 'Bem-vindo a sala');
});

it('criacao de sala retorna payload suficiente para auto-selecao', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $member = User::factory()->create();

    $response = $this->actingAs($admin)->postJson('/api/chat/rooms', [
        'name' => 'Sala Auto Select',
        'avatar' => 'https://example.com/room.png',
        'member_ids' => [$member->id],
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'Sala Auto Select');

    expect($response->json('data.id'))->not->toBeNull();
    expect($response->json('data.avatar'))->toBe('https://example.com/room.png');
});
