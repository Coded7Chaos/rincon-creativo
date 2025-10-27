<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Gate::define('is-admin', function (User $user){
            return $user->role === 'admin';
        });
        
        Gate::define('is-fulfillment', function (User $user){
            return $user->role === 'fulfillment';
        });

        Gate::define('is-client', function (User $user){
            return $user->role === 'client';
        });
    }
}
