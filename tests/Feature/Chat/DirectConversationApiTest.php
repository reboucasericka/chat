<?php

use App\Models\DirectConversation;
use App\Models\Message;
use App\Models\User;

it('utilizador pode abrir conversa direta com outro utilizador', function () {
    $a = User::factory()->create();
    $b = User::factory()->create();

    $response = $this->actingAs($a)->postJson('/api/chat/direct-conversations', [
        'user_id' => $b->id,
    ]);

    $response->assertCreated();
    $this->assertDatabaseHas('direct_conversations', [
        'user_one_id' => min($a->id, $b->id),
        'user_two_id' => max($a->id, $b->id),
        'created_by' => $a->id,
    ]);
});

it('conversa direta nao duplica para o mesmo par', function () {
    $a = User::factory()->create();
    $b = User::factory()->create();

    $this->actingAs($a)->postJson('/api/chat/direct-conversations', ['user_id' => $b->id])->assertCreated();
    $this->actingAs($a)->postJson('/api/chat/direct-conversations', ['user_id' => $b->id])->assertOk();

    expect(DirectConversation::query()->count())->toBe(1);
});

it('participante pode ver mensagens da conversa', function () {
    $a = User::factory()->create();
    $b = User::factory()->create();

    $conversation = DirectConversation::query()->create([
        'user_one_id' => min($a->id, $b->id),
        'user_two_id' => max($a->id, $b->id),
        'created_by' => $a->id,
    ]);

    Message::query()->create([
        'sender_id' => $a->id,
        'direct_conversation_id' => $conversation->id,
        'type' => 'text',
        'body' => 'Mensagem direta',
    ]);

    $this->actingAs($b)
        ->getJson("/api/chat/direct-conversations/{$conversation->id}/messages")
        ->assertOk()
        ->assertJsonPath('data.0.body', 'Mensagem direta');
});

it('utilizador externo nao pode ver conversa de outros', function () {
    $a = User::factory()->create();
    $b = User::factory()->create();
    $outsider = User::factory()->create();

    $conversation = DirectConversation::query()->create([
        'user_one_id' => min($a->id, $b->id),
        'user_two_id' => max($a->id, $b->id),
        'created_by' => $a->id,
    ]);

    $this->actingAs($outsider)
        ->getJson("/api/chat/direct-conversations/{$conversation->id}")
        ->assertForbidden();
});

it('participante pode enviar mensagem', function () {
    $a = User::factory()->create();
    $b = User::factory()->create();

    $conversation = DirectConversation::query()->create([
        'user_one_id' => min($a->id, $b->id),
        'user_two_id' => max($a->id, $b->id),
        'created_by' => $a->id,
    ]);

    $this->actingAs($a)
        ->postJson("/api/chat/direct-conversations/{$conversation->id}/messages", [
            'body' => 'Oi direto',
        ])
        ->assertCreated()
        ->assertJsonPath('data.type', 'text')
        ->assertJsonPath('data.body', 'Oi direto');
});

it('utilizador externo nao pode enviar mensagem', function () {
    $a = User::factory()->create();
    $b = User::factory()->create();
    $outsider = User::factory()->create();

    $conversation = DirectConversation::query()->create([
        'user_one_id' => min($a->id, $b->id),
        'user_two_id' => max($a->id, $b->id),
        'created_by' => $a->id,
    ]);

    $this->actingAs($outsider)
        ->postJson("/api/chat/direct-conversations/{$conversation->id}/messages", [
            'body' => 'tentativa externa',
        ])
        ->assertForbidden();
});

it('participante pode apagar conversa direta', function () {
    $a = User::factory()->create();
    $b = User::factory()->create();

    $conversation = DirectConversation::query()->create([
        'user_one_id' => min($a->id, $b->id),
        'user_two_id' => max($a->id, $b->id),
        'created_by' => $a->id,
    ]);

    $this->actingAs($a)
        ->deleteJson("/api/chat/direct-conversations/{$conversation->id}")
        ->assertOk();

    $this->assertDatabaseMissing('direct_conversations', ['id' => $conversation->id]);
});

it('utilizador externo nao pode apagar conversa direta', function () {
    $a = User::factory()->create();
    $b = User::factory()->create();
    $outsider = User::factory()->create();

    $conversation = DirectConversation::query()->create([
        'user_one_id' => min($a->id, $b->id),
        'user_two_id' => max($a->id, $b->id),
        'created_by' => $a->id,
    ]);

    $this->actingAs($outsider)
        ->deleteJson("/api/chat/direct-conversations/{$conversation->id}")
        ->assertForbidden();
});
