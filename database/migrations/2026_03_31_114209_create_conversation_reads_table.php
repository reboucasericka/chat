<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversation_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->morphs('conversation');
            $table->foreignId('last_read_message_id')->nullable()->constrained('messages')->nullOnDelete();
            $table->timestamp('last_read_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'conversation_type', 'conversation_id'], 'conversation_reads_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversation_reads');
    }
};
