<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('chat_room_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('direct_conversation_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('type')->default('text');
            $table->text('body')->nullable();
            $table->string('attachment_path')->nullable();
            $table->timestamp('edited_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['chat_room_id', 'created_at']);
            $table->index(['direct_conversation_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
