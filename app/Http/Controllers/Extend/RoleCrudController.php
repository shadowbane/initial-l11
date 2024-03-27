<?php

namespace App\Http\Controllers\Extend;

use App\Http\Controllers\Traits\CrudControllerTrait;
use Backpack\PermissionManager\app\Http\Controllers\RoleCrudController as RC;
use Backpack\PermissionManager\app\Http\Requests\RoleStoreCrudRequest as StoreRequest;
use Backpack\PermissionManager\app\Http\Requests\RoleUpdateCrudRequest as UpdateRequest;
use Illuminate\Support\Facades\Cache;

class RoleCrudController extends RC
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

        $this->crud->setModel($role_model);
        $this->crud->setEntityNameStrings(
            __('backpack::permissionmanager.role'),
            __('backpack::permissionmanager.roles')
        );
        $this->crud->setRoute(backpack_url('role'));
        //        $this->checkPermission();

        // deny access according to configuration file
        if (config('backpack.permissionmanager.allow_role_create') == false) {
            $this->crud->denyAccess(['create']);
        }
        if (config('backpack.permissionmanager.allow_role_update') == false) {
            $this->crud->denyAccess(['update']);
        }
        if (config('backpack.permissionmanager.allow_role_delete') == false) {
            $this->crud->denyAccess(['delete']);
        }
    }

    public function setupListOperation()
    {
        /*
         * Show a column for the name of the role.
         */
        $this->crud->addColumn([
            'name' => 'name',
            'label' => __('backpack::permissionmanager.name'),
            'type' => 'text',
        ]);

        /*
         * Show a column with the number of users that have that particular role.
         *
         * Note: To account for the fact that there can be thousands or millions
         * of users for a role, we did not use the `relationship_count` column,
         * but instead opted to append a fake `user_count` column to
         * the result, using Laravel's `withCount()` method.
         * That way, no users are loaded.
         */
        $this->crud->query->withCount('users');
        $this->crud->addColumn([
            'label' => __('backpack::permissionmanager.users'),
            'type' => 'text',
            'name' => 'users_count',
            'wrapper' => [
                'href' => function ($crud, $column, $entry, $related_key) {
                    return backpack_url('user?role='.$entry->getKey());
                },
            ],
            'suffix' => ' users',
        ]);

        /*
         * In case multiple guards are used, show a column for the guard.
         */
        if (config('backpack.permissionmanager.multiple_guards')) {
            $this->crud->addColumn([
                'name' => 'guard_name',
                'label' => __('backpack::permissionmanager.guard_type'),
                'type' => 'text',
            ]);
        }
    }

    public function setupCreateOperation()
    {
        $this->crud->setCreateContentClass('col-md-12');
        $this->addFields();
        $this->crud->setValidation(StoreRequest::class);

        // flush permission
        // otherwise, changes won't have effect
        Cache::forget(config('permission.cache.key'));
    }

    public function setupUpdateOperation()
    {
        $this->crud->setEditContentClass('col-md-12');
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
            'label' => trans('backpack::permissionmanager.name'),
            'type' => 'text',
        ]);

        $this->crud->addField([
            'label' => '',
            'type' => 'menutree',
            'name' => 'menuitems',
            'entity' => 'menuitems',
            'attribute' => 'name',
            'model' => '\App\Models\MenuItem',
            'pivot' => true,
            'tab' => 'Navigations',
        ]);
        $this->crud->addField([
            'label' => '',
            'type' => 'checklist_col',
            'name' => 'permissions',
            'entity' => 'permissions',
            'attribute' => 'name',
            'model' => 'App\Models\Permission',
            'pivot' => true,
            'number_columns' => 1,
            'tab' => 'Permissions',
        ]);
    }
}
