<?php

namespace App\Providers;

use App\Support\EnumCatalog;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

/**
 * Register application services and authorization rules.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->scoped(EnumCatalog::class);
    }

    /**
     * @return void
     */
    public function boot(): void
    {
        Paginator::useBootstrap();
        Gate::define('manage-shop', fn (User $user) => $user->is_admin);
    }
}
