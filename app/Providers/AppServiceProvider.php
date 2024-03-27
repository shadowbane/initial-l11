<?php

namespace App\Providers;

use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @param  UrlGenerator  $url
     *
     * @throws BindingResolutionException
     *
     * @return void
     */
    public function boot(UrlGenerator $url): void
    {
        $this->overrideMiddlewares();

        // prevent n+1 query
        Model::preventLazyLoading(! app()->isProduction());

        // Redirecting all requests to https
        if (config('system.default.redirect_https')) {
            $url->forceScheme('https');
            $this->app['request']->server->set('HTTPS', true);
        }

        Paginator::useBootstrap();

        // add paginate to collection
        if (! Collection::hasMacro('paginate')) {
            Collection::macro('paginate', function ($perPage = 15, $page = null, $options = []) {
                if (Paginator::resolveCurrentPage()) {
                    $page = $page ?: (Paginator::resolveCurrentPage());
                } else {
                    $page = $page ?: (1);
                }

                return (
                new LengthAwarePaginator($this->forPage($page, $perPage), $this->count(), $perPage, $page, $options)
                )->withPath('');
            });
        }
    }

    /**
     * Override default middlewares.
     *
     * @return void
     */
    private function overrideMiddlewares(): void
    {
        RedirectIfAuthenticated::redirectUsing(fn($request) => route('backpack.dashboard'));
    }
}
