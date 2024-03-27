<?php

namespace App\Http\Controllers\Admin\Operations;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;
use Prologue\Alerts\Facades\Alert;

/**
 * Trait CreateOrEditOperation.
 *
 * @package App\Http\Controllers\Admin\Operations
 *
 * @property \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
trait CreateOrEditOperation
{
    /**
     * Define which routes are needed for this operation.
     *
     * @param  string  $segment  Name of the current entity (singular). Used as first URL segment.
     * @param  string  $routeName  prefix of the route name
     * @param  string  $controller  name of the current CrudController
     */
    protected function setupCreateOrEditRoutes($segment, $routeName, $controller)
    {
        Route::get($segment.'/', [
            'as' => $routeName.'.index',
            'uses' => $controller.'@index',
            'operation' => 'createoredit',
        ]);

        Route::post($segment, [
            'as' => $routeName.'.store',
            'uses' => $controller.'@store',
            'operation' => 'createoredit',
        ]);

        Route::put($segment.'/{id}', [
            'as' => $routeName.'.update',
            'uses' => $controller.'@update',
            'operation' => 'createoredit',
        ]);
    }

    /**
     * Add the default settings, buttons, etc that this operation needs.
     */
    protected function setupCreateOrEditDefaults()
    {
        $this->crud->operation('createoredit', function () {
            $this->crud->loadDefaultOperationSettingsFromConfig();
            $this->crud->setupDefaultSaveActions();
        });
    }

    /**
     * @return Application|Factory|View
     */
    public function index()
    {
        if ($this->crud->query->count() > 0) {
            $this->crud->entry = $this->crud->query->first();
            $id = $this->crud->entry->id;

            $this->crud->setOperationSetting('fields', $this->crud->getUpdateFields());
            // get the info for that entry
            $this->data['entry'] = $this->crud->entry;
            $this->data['crud'] = $this->crud;
            $this->data['saveAction'] = $this->crud->getSaveAction();
            $this->data['title'] = $this->crud->getTitle() ?? trans('backpack::crud.edit').' '.$this->crud->entity_name;
            $this->data['id'] = $id;

            // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
            return view($this->crud->getEditView(), $this->data);
        }
        // prepare the fields you need to show
        $this->data['crud'] = $this->crud;
        $this->data['saveAction'] = $this->crud->getSaveAction();
        $this->data['title'] = $this->crud->getTitle() ?? trans('backpack::crud.add').' '.$this->crud->entity_name;

        // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
        return view($this->crud->getCreateView(), $this->data);

    }

    /**
     * Store a newly created resource in the database.
     *
     * @return Application|\Illuminate\Http\RedirectResponse|Redirector
     */
    public function store()
    {
        $this->crud->hasAccessOrFail('create');

        // execute the FormRequest authorization and validation, if one is required
        $request = $this->crud->validateRequest();

        // insert item in the db
        $item = $this->crud->create($this->crud->getRequest()->except([
            '_token', '_method', 'http_referrer', 'current_tab', 'save_action',
        ]));
        $this->data['entry'] = $this->crud->entry = $item;

        // show a success message
        Alert::success(trans('backpack::crud.insert_success'))->flash();

        return redirect($this->crud->getRoute());
    }

    /**
     * Update the specified resource in the database.
     *
     * @return Application|\Illuminate\Http\RedirectResponse|Redirector
     */
    public function update()
    {
        $this->crud->hasAccessOrFail('update');

        // execute the FormRequest authorization and validation, if one is required
        $request = $this->crud->validateRequest();

        // update the row in the db
        $item = $this->crud->update(
            $request->get($this->crud->model->getKeyName()),
            $this->crud->getRequest()->except(['_token', '_method', 'http_referrer', 'current_tab', 'save_action'])
        );
        $this->data['entry'] = $this->crud->entry = $item;

        // show a success message
        Alert::success(trans('backpack::crud.update_success'))->flash();

        return redirect($this->crud->getRoute());
    }
}
