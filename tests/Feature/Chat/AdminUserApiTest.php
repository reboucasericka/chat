<?php

use App\Models\User;

it('admin pode criar utilizador', function () {
    /** @var \Tests\TestCase $this */
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->postJson('/api/chat/users', [
        'name' => 'Novo Colaborador',
        'email' => 'novo@chat.local',
        'role' => 'user',
        'status' => 'offline',
    ]);

    $response->assertCreated();

    $this->assertDatabaseHas('users', [
        'email' => 'novo@chat.local',
        'role' => 'user',
    ]);
});

it('utilizador comum nao pode criar utilizador', function () {
    /** @var \Tests\TestCase $this */
    $user = User::factory()->create(['role' => 'user']);

    $this->actingAs($user)->postJson('/api/chat/users', [
        'name' => 'Nao Pode',
        'email' => 'naopode@chat.local',
        'role' => 'user',
        'status' => 'offline',
    ])->assertForbidden();
});
