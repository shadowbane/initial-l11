<?php

namespace App\Observers;

use App\Services\MenuService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class DeleteSidemenuCacheObserver implements ShouldHandleEventsAfterCommit
{
    /**
     * @throws BindingResolutionException
     */
    public function saved(): void
    {
        app()->make(MenuService::class)->deleteAllCache();
    }

    /**
     * @throws BindingResolutionException
     */
    public function updated(): void
    {
        app()->make(MenuService::class)->deleteAllCache();
    }

    /**
     * @throws BindingResolutionException
     */
    public function deleted(): void
    {
        app()->make(MenuService::class)->deleteAllCache();
    }
}
