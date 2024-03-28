<?php

namespace App\Http\Controllers\Traits;

use Route;

trait CrudControllerTrait
{
    /**
     * Check the RBAC for permissions.
     * It will disable the buttons automatically when needed,
     * and block request from being processed.
     *
     * @return void
     */
    public function checkPermission(): void
    {
        $this->checkView();
        $this->checkCreate();
        $this->checkUpdate();
        $this->checkDelete();

        if (
            auth()->user()->cannot($this->getController().'@update')
            && auth()->user()->cannot($this->getController().'@delete')
        ) {
            $this->crud->removeAllButtonsFromStack('line');
            $this->crud->removeColumn('actions');
        }
    }

    /**
     * Check if the user has permission to vire resources.
     *
     * @return void
     */
    private function checkView(): void
    {
        if (auth()->user()->cannot($this->getController().'@view')) {
            $this->crud->denyAccess(['show', 'list']);
            $this->crud->removeButton('revisions');
        } else {
            // allow show
            $this->crud->allowAccess('show');
            $this->crud->allowAccess('revisions');
        }
    }

    /**
     * Check if the user has permission to update a resource.
     *
     * @return void
     */
    private function checkUpdate(): void
    {
        if (auth()->user()->cannot($this->getController().'@update')) {
            $this->crud->denyAccess(['update']);
            $this->crud->removeButton('update');
            $this->crud->removeButton('revisions');
        } else {
            $this->crud->allowAccess('update');
            $this->crud->allowAccess('revisions');
        }
    }

    /**
     * Check if the user has permission to delete a resource.
     *
     * @return void
     */
    private function checkDelete(): void
    {
        if (auth()->user()->cannot($this->getController().'@delete')) {
            $this->crud->denyAccess(['delete']);
            $this->crud->removeButton('delete');
        }
    }

    /**
     * Check if the user has permission to create a resource.
     *
     * @return void
     */
    private function checkCreate(): void
    {
        if (auth()->user()->cannot($this->getController().'@create')) {
            $this->crud->denyAccess(['create']);
            $this->crud->removeButton('create');
        } else {
            $this->crud->allowAccess('create');
        }
    }

    /**
     * Get the controller name.
     *
     * @return string
     */
    private function getController(): string
    {
        return explode('@', Route::currentRouteAction())[0];
    }

    /**
     * Remove tabbed page if on mobile.
     *
     * @return void
     */
    private function removeTabIfMobile(): void
    {
        if ($this->data['crud']['is_mobile']) {
            $this->crud->disableTabs();
        }
    }
}
