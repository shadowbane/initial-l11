<?php

namespace App\Http\Controllers\Extend;

use App\Http\Controllers\Traits\CrudControllerTrait;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\PermissionManager\app\Http\Requests\PermissionStoreCrudRequest as StoreRequest;
use Backpack\PermissionManager\app\Http\Requests\PermissionUpdateCrudRequest as UpdateRequest;
use Cache;

class PermissionCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use CrudControllerTrait;

    public function setup()
    {
        $this->role_model = $role_model = config('permission.models.role');
        $this->permission_model = $permission_model = config('permission.models.permission');

        $this->crud->setModel($permission_model);
        $this->crud->setEntityNameStrings(
            __('backpack::permissionmanager.permission_singular'),
            __('backpack::permissionmanager.permission_plural')
        );
        $this->crud->setRoute(backpack_url('permission'));
        $this->checkPermission();

        // deny access according to configuration file
        if (config('backpack.permissionmanager.allow_permission_create') == false) {
            $this->crud->denyAccess(['create']);
        }
        if (config('backpack.permissionmanager.allow_permission_update') == false) {
            $this->crud->denyAccess(['update']);
        }
        if (config('backpack.permissionmanager.allow_permission_delete') == false) {
            $this->crud->denyAccess(['delete']);
        }

        // default sorting
        $this->crud->query->orderBy('shortname', 'ASC');
    }

    public function setupListOperation()
    {
        $this->crud->removeAllButtonsFromStack('line');
        $this->crud->addColumn([
            'label' => 'Shortname',
            'name' => 'shortname',
        ]);
        $this->crud->addColumn([
            'name' => 'name',
            'label' => __('backpack::permissionmanager.name'),
            'type' => 'text',
            'limit' => 1000,
        ]);
    }

    public function setupCreateOperation()
    {
        $this->addFields();
        $this->crud->setValidation(StoreRequest::class);

        // flush permission
        // otherwise, changes won't have effect
        Cache::forget(config('permission.cache.key'));
    }

    public function setupUpdateOperation()
    {
        $this->addFields();
        $this->crud->setValidation(UpdateRequest::class);

        // flush permission
        // otherwise, changes won't have effect
        Cache::forget(config('permission.cache.key'));
    }

    private function addFields()
    {
        $this->crud->addField([
            'name' => 'name',
            'label' => __('backpack::permissionmanager.name'),
            'type' => 'text',
        ]);
        $this->crud->addField([
            'label' => 'Shortname',
            'name' => 'shortname',
        ]);
        $this->crud->addField([
            'label' => 'Description',
            'name' => 'description',
        ]);
        $this->crud->addField([
            'label' => __('backpack::permissionmanager.roles'),
            'type' => 'checklist',
            'name' => 'roles',
            'entity' => 'roles',
            'attribute' => 'name',
            'model' => "App\Models\Role",
            'pivot' => true,
        ]);
    }
}
