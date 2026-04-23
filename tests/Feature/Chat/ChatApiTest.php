<?php

use App\Models\ChatRoom;
use App\Models\DirectConversation;
use App\Models\Message;
use App\Models\User;

it('permite admin criar sala', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->postJson('/api/chat/rooms', [
        'name' => 'Produto',
    ]);

    $response->assertCreated()->assertJsonPath('data.name', 'Produto');

    expect(ChatRoom::query()->count())->toBe(1);
});

it('impede user de criar sala', function () {
    $user = User::factory()->create(['role' => 'user']);

    $this->actingAs($user)
        ->postJson('/api/chat/rooms', ['name' => 'Nao pode'])
        ->assertForbidden();
});

it('permite admin apagar sala', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $room = ChatRoom::query()->create(['name' => 'Produto', 'created_by' => $admin->id]);

    $this->actingAs($admin)
        ->deleteJson("/api/chat/rooms/{$room->id}")
        ->assertOk();

    $this->assertDatabaseMissing('chat_rooms', ['id' => $room->id]);
});

it('permite membro enviar mensagem na sala', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $member = User::factory()->create();

    $room = ChatRoom::query()->create(['name' => 'Geral', 'created_by' => $admin->id]);
    $room->members()->sync([$admin->id, $member->id]);

    $this->actingAs($member)
        ->postJson("/api/chat/rooms/{$room->id}/messages", ['body' => 'Ola sala'])
        ->assertCreated()
        ->assertJsonPath('data.body', 'Ola sala');

    expect(Message::query()->where('chat_room_id', $room->id)->count())->toBe(1);
});

it('impede nao membro de enviar mensagem na sala', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $member = User::factory()->create();
    $outsider = User::factory()->create();

    $room = ChatRoom::query()->create(['name' => 'Engenharia', 'created_by' => $admin->id]);
    $room->members()->sync([$admin->id, $member->id]);

    $this->actingAs($outsider)
        ->postJson("/api/chat/rooms/{$room->id}/messages", ['body' => 'hack'])
        ->assertForbidden();
});

it('cria conversa direta e troca mensagens entre participantes', function () {
    $a = User::factory()->create();
    $b = User::factory()->create();

    $conversationResponse = $this->actingAs($a)->postJson("/api/chat/direct/{$b->id}");
    $conversationResponse->assertCreated();

    $conversationId = $conversationResponse->json('data.id');

    $this->actingAs($a)
        ->postJson("/api/chat/direct/{$conversationId}/messages", ['body' => 'ping'])
        ->assertCreated();

    $this->actingAs($b)
        ->getJson("/api/chat/direct/{$conversationId}/messages")
        ->assertOk()
        ->assertJsonPath('data.0.body', 'ping');

    expect(DirectConversation::query()->count())->toBe(1);
});

it('sidebar retorna todas as salas com indicador de membership', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();
    $other = User::factory()->create();

    $roomA = ChatRoom::query()->create(['name' => 'Sala A', 'created_by' => $admin->id]);
    $roomB = ChatRoom::query()->create(['name' => 'Sala B', 'created_by' => $admin->id]);
    $roomA->members()->sync([$admin->id, $user->id]);
    $roomB->members()->sync([$admin->id, $other->id]);

    $direct = DirectConversation::query()->create([
        'user_one_id' => min($user->id, $other->id),
        'user_two_id' => max($user->id, $other->id),
    ]);

    Message::query()->create([
        'sender_id' => $other->id,
        'direct_conversation_id' => $direct->id,
        'body' => 'mensagem privada',
    ]);

    $response = $this->actingAs($user)->getJson('/api/chat/sidebar')->assertOk();
    $rooms = collect($response->json('data'))->where('kind', 'room')->values();

    $roomA = $rooms->firstWhere('title', 'Sala A');
    $roomB = $rooms->firstWhere('title', 'Sala B');

    expect($roomA)->not->toBeNull();
    expect($roomA['is_member'])->toBeTrue();
    expect($roomB)->not->toBeNull();
    expect($roomB['is_member'])->toBeFalse();
});

it('lista conversas diretas do utilizador com unread_count', function () {
    $a = User::factory()->create();
    $b = User::factory()->create();
    $c = User::factory()->create();

    $conversationAB = DirectConversation::query()->create([
        'user_one_id' => min($a->id, $b->id),
        'user_two_id' => max($a->id, $b->id),
        'created_by' => $a->id,
    ]);

    DirectConversation::query()->create([
        'user_one_id' => min($b->id, $c->id),
        'user_two_id' => max($b->id, $c->id),
        'created_by' => $b->id,
    ]);

    Message::query()->create([
        'sender_id' => $b->id,
        'direct_conversation_id' => $conversationAB->id,
        'body' => 'ola A',
    ]);

    $response = $this->actingAs($a)->getJson('/api/chat/direct')->assertOk();
    $directs = collect($response->json('data'));

    expect($directs)->toHaveCount(1);
    expect($directs->first()['id'])->toBe($conversationAB->id);
    expect($directs->first()['unread_count'])->toBe(1);
    expect($directs->first()['counterpart']['id'])->toBe($b->id);
});

it('marca conversa direta como lida e zera unread_count', function () {
    $a = User::factory()->create();
    $b = User::factory()->create();

    $conversation = DirectConversation::query()->create([
        'user_one_id' => min($a->id, $b->id),
        'user_two_id' => max($a->id, $b->id),
        'created_by' => $a->id,
    ]);

    Message::query()->create([
        'sender_id' => $b->id,
        'direct_conversation_id' => $conversation->id,
        'body' => 'mensagem 1',
    ]);

    $before = $this->actingAs($a)->getJson('/api/chat/direct')->assertOk();
    expect(collect($before->json('data'))->first()['unread_count'])->toBe(1);

    $this->actingAs($a)
        ->postJson("/api/chat/direct/{$conversation->id}/read")
        ->assertOk();

    $after = $this->actingAs($a)->getJson('/api/chat/direct')->assertOk();
    expect(collect($after->json('data'))->first()['unread_count'])->toBe(0);
});
