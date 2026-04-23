<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar')->nullable()->after('email');
            $table->enum('role', ['admin', 'user'])->default('user')->after('avatar');
            $table->enum('status', ['online', 'offline'])->default('offline')->after('role');
            $table->timestamp('last_seen_at')->nullable()->after('remember_token');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['avatar', 'role', 'status', 'last_seen_at']);
        });
    }
};
