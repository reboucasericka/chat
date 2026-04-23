<?php

namespace App\Providers;

use App\Models\DirectConversation;
use App\Models\Message;
use App\Models\User;
use App\Policies\DirectConversationPolicy;
use App\Policies\MessagePolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(DirectConversation::class, DirectConversationPolicy::class);
        Gate::policy(Message::class, MessagePolicy::class);

        Vite::prefetch(concurrency: 3);
    }
}
