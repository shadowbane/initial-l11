<?php

namespace App\Http\Controllers\Admin\Operations;

use Illuminate\Support\Facades\Route;
use Prologue\Alerts\Facades\Alert;

trait ImpersonateOperation
{
    /**
     * Define which routes are needed for this operation.
     *
     * @param  string  $segment  Name of the current entity (singular). Used as first URL segment.
     * @param  string  $routeName  prefix of the route name
     * @param  string  $controller  name of the current CrudController
     */
    protected function setupImpersonateRoutes($segment, $routeName, $controller)
    {
        Route::get($segment.'/{id}/impersonate', [
            'as' => $routeName.'.impersonate',
            'uses' => $controller.'@impersonate',
            'operation' => 'impersonate',
        ]);

        Route::get('stop-impersonating', [
            'uses' => $controller.'@stopImpersonating',
        ]);
    }

    /**
     * Add the default settings, buttons, etc that this operation needs.
     */
    protected function setupImpersonateDefaults()
    {
        if (auth()->user()->can("App\Http\Controllers\Extend\UserCrudController@update")) {
            $this->crud->allowAccess('impersonate');
        }

        $this->crud->operation('impersonate', function () {
            $this->crud->loadDefaultOperationSettingsFromConfig();
        });

        $this->crud->operation('list', function () {
            $this->crud->addButton('line', 'impersonate', 'view', 'crud::buttons.impersonate', 'beginning');
        });
    }

    /**
     * Impersonate user.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function impersonate()
    {
        $this->crud->hasAccessOrFail('update');

        $entry = $this->crud->getCurrentEntry();

        auth()->user()->setImpersonating($entry->id);

        Alert::success('Impersonating '.$entry->name.' (id '.$entry->id.').')->flash();

        // load the view
        return redirect('dashboard');
    }

    /**
     * Stop impersonating user.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function stopImpersonating()
    {
        auth()->user()->stopImpersonating();
        Alert::success('Impersonating Stopped.')->flash();

        if (app()->isLocal()) {
            return redirect()->to('/user');
        }

        return redirect()->to('/dashboard');
    }
}
