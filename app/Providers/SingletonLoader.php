<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class SingletonLoader extends ServiceProvider
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
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     *
     * @return void
     */
    public function boot(): void
    {
        $ms = app()->make(\App\Services\MenuService::class);
        app()->instance(\App\Services\MenuService::class, $ms);
    }
}
