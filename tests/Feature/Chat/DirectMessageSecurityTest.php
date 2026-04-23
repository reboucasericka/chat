<?php

use App\Models\ChatRoom;
use App\Models\DirectConversation;
use App\Models\User;

it('impede que um utilizador aceda a conversa direta de outros', function () {
    $a = User::factory()->create();
    $b = User::factory()->create();
    $c = User::factory()->create();

    $conversation = DirectConversation::query()->create([
        'user_one_id' => min($a->id, $b->id),
        'user_two_id' => max($a->id, $b->id),
    ]);

    $this->actingAs($c)
        ->getJson("/api/chat/direct/{$conversation->id}")
        ->assertForbidden();
});

it('impede envio de mensagem por utilizador que nao pertence a conversa', function () {
    $a = User::factory()->create();
    $b = User::factory()->create();
    $c = User::factory()->create();

    $conversation = DirectConversation::query()->create([
        'user_one_id' => min($a->id, $b->id),
        'user_two_id' => max($a->id, $b->id),
    ]);

    $this->actingAs($c)
        ->postJson("/api/chat/direct/{$conversation->id}/messages", [
            'body' => 'hack',
        ])
        ->assertForbidden();
});

it('impede acesso a mensagens da sala por nao membro', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $member = User::factory()->create();
    $outsider = User::factory()->create();

    $room = ChatRoom::query()->create([
        'name' => 'Seguranca',
        'created_by' => $admin->id,
    ]);

    $room->members()->sync([$admin->id, $member->id]);

    $this->actingAs($outsider)
        ->getJson("/api/chat/rooms/{$room->id}/messages")
        ->assertForbidden();
});
