<?php

namespace App\Providers;

use App\Models\MenuItem;
use App\Models\Permission;
use App\Models\Role;
use App\Observers\DeleteSidemenuCacheObserver;
use Illuminate\Support\ServiceProvider;

class ObserverServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        MenuItem::observe(DeleteSidemenuCacheObserver::class);
        Role::observe(DeleteSidemenuCacheObserver::class);
        Permission::observe(DeleteSidemenuCacheObserver::class);
    }
}
