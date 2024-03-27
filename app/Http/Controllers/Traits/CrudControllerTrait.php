<?php

namespace App\Http\Controllers\Traits;

use Route;

trait CrudControllerTrait
{
    public function checkPermission(): void
    {
        //        $this->checkView();
        //        $this->checkCreate();
        //        $this->checkUpdate();
        //        $this->checkDelete();
        //
        //        if (
        //            auth()->user()->cannot($this->getController().'@update')
        //            && auth()->user()->cannot($this->getController().'@delete')
        //        ) {
        //            $this->crud->removeAllButtonsFromStack('line');
        //            $this->crud->removeColumn('actions');
        //        }
    }

    private function checkView()
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

    private function checkUpdate()
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

    private function checkDelete()
    {
        if (auth()->user()->cannot($this->getController().'@delete')) {
            $this->crud->denyAccess(['delete']);
            $this->crud->removeButton('delete');
        }
    }

    private function checkCreate()
    {
        if (auth()->user()->cannot($this->getController().'@create')) {
            $this->crud->denyAccess(['create']);
            $this->crud->removeButton('create');
        } else {
            $this->crud->allowAccess('create');
        }
    }

    private function getController()
    {
        return explode('@', Route::currentRouteAction())[0];
    }

    private function removeTabIfMobile()
    {
        if ($this->data['crud']['is_mobile']) {
            $this->crud->disableTabs();
        }
    }
}
