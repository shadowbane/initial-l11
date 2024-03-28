<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;

class DashboardController extends BaseController
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware(backpack_middleware());
    }

    /**
     * Show the basic dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function dashboard(): \Illuminate\View\View
    {
        $data['name'] = backpack_auth()->user()->name;

        return view('backpack.ui::dashboard', $data);
    }

    /**
     * Redirect to the dashboard.
     *
     * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
     */
    public function redirect()
    {
        return redirect(route(auth()->guest() ? 'auth.login' : 'backpack.dashboard'));
    }
}
