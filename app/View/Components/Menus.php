<?php

namespace App\View\Components;

use App\Services\MenuService;
use Closure;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Menus extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @throws BindingResolutionException
     */
    public function render(): View|Closure|string
    {
        $data['navigations'] = app()->make(MenuService::class)->tree();

        return view('components.menus', $data);
    }
}
