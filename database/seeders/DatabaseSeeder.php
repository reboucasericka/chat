<?php

namespace Database\Seeders;

use App\Models\DirectConversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@chat.local'],
            [
                'name' => 'Chat Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'status' => 'online',
                'last_seen_at' => now(),
            ]
        );

        $userOne = User::query()->updateOrCreate(
            ['email' => 'user1@chat.local'],
            [
                'name' => 'Chat User 1',
                'password' => Hash::make('password'),
                'role' => 'user',
                'status' => 'online',
                'last_seen_at' => now(),
            ]
        );

        $userTwo = User::query()->updateOrCreate(
            ['email' => 'user2@chat.local'],
            [
                'name' => 'Chat User 2',
                'password' => Hash::make('password'),
                'role' => 'user',
                'status' => 'offline',
                'last_seen_at' => now()->subMinutes(10),
            ]
        );

        $userThree = User::query()->updateOrCreate(
            ['email' => 'user3@chat.local'],
            [
                'name' => 'Chat User 3',
                'password' => Hash::make('password'),
                'role' => 'user',
                'status' => 'offline',
                'last_seen_at' => now()->subMinutes(5),
            ]
        );

        User::factory(4)->create();

        $conversationOne = DirectConversation::query()->firstOrCreate(
            [
                'user_one_id' => min($admin->id, $userOne->id),
                'user_two_id' => max($admin->id, $userOne->id),
            ],
            ['created_by' => $admin->id]
        );

        $conversationTwo = DirectConversation::query()->firstOrCreate(
            [
                'user_one_id' => min($userOne->id, $userTwo->id),
                'user_two_id' => max($userOne->id, $userTwo->id),
            ],
            ['created_by' => $userOne->id]
        );

        Message::query()->firstOrCreate([
            'sender_id' => $admin->id,
            'direct_conversation_id' => $conversationOne->id,
            'type' => 'text',
            'body' => 'Bom dia equipa!',
        ]);

        Message::query()->firstOrCreate([
            'sender_id' => $userOne->id,
            'direct_conversation_id' => $conversationOne->id,
            'type' => 'text',
            'body' => 'Bom dia admin, tudo certo por aqui.',
        ]);

        Message::query()->firstOrCreate([
            'sender_id' => $userTwo->id,
            'direct_conversation_id' => $conversationTwo->id,
            'type' => 'text',
            'body' => 'Consegues rever o ticket da API?',
        ]);

        Message::query()->firstOrCreate([
            'sender_id' => $userOne->id,
            'direct_conversation_id' => $conversationTwo->id,
            'type' => 'text',
            'body' => 'Claro, vou tratar disso agora.',
        ]);
    }
}
