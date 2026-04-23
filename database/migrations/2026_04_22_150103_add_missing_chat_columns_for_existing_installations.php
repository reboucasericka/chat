<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'status')) {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('status', ['online', 'offline'])->default('offline')->after('role');
            });
        }

        if (Schema::hasTable('chat_rooms') && ! Schema::hasColumn('chat_rooms', 'avatar')) {
            Schema::table('chat_rooms', function (Blueprint $table) {
                $table->string('avatar')->nullable()->after('id');
            });
        }

        if (Schema::hasTable('direct_conversations') && ! Schema::hasColumn('direct_conversations', 'created_by')) {
            Schema::table('direct_conversations', function (Blueprint $table) {
                $table->foreignId('created_by')->nullable()->after('user_two_id')->constrained('users')->nullOnDelete();
            });
        }

        if (Schema::hasTable('messages') && ! Schema::hasColumn('messages', 'type')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->string('type')->default('text')->after('direct_conversation_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('messages') && Schema::hasColumn('messages', 'type')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->dropColumn('type');
            });
        }

        if (Schema::hasTable('direct_conversations') && Schema::hasColumn('direct_conversations', 'created_by')) {
            Schema::table('direct_conversations', function (Blueprint $table) {
                $table->dropConstrainedForeignId('created_by');
            });
        }

        if (Schema::hasTable('chat_rooms') && Schema::hasColumn('chat_rooms', 'avatar')) {
            Schema::table('chat_rooms', function (Blueprint $table) {
                $table->dropColumn('avatar');
            });
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'status')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
