<?php

namespace App\Http\Controllers\Extend;

use App\Http\Controllers\Admin\Operations\ImpersonateOperation;
use App\Http\Controllers\Traits\CrudControllerTrait;
use App\Http\Controllers\Traits\FetchOperation;
use App\Http\Requests\User\UserStoreRequest as StoreRequest;
use App\Http\Requests\User\UserUpdateRequest as UpdateRequest;
use App\Models\User;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use JetBrains\PhpStorm\ArrayShape;
use Prologue\Alerts\Facades\Alert;
use Throwable;

/**
 * Class UserCrudController.
 */
class UserCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation {
        edit as traitEdit;
        update as traitUpdate;
    }
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation {
        destroy as traitDestroy;
    }
    use FetchOperation;
    use ImpersonateOperation;
    use CrudControllerTrait;

    public function setup()
    {
        $this->crud->setModel(\App\Models\User::class);
        $this->crud->setRoute(config('backpack.base.route_prefix').'/user');
        $this->crud->setEntityNameStrings('user', 'users');

        $this->checkPermission();
    }

    public function setupListOperation()
    {
        $this->crud->setColumns([
            [
                'name' => 'name',
                'label' => __('backpack::permissionmanager.name'),
                'type' => 'text',
            ],
            [
                'name' => 'email',
                'label' => __('backpack::permissionmanager.email'),
                'type' => 'email',
            ],
            [
                'name' => 'username',
                'label' => 'Username',
                'type' => 'text',
            ],
            [ // n-n relationship (with pivot table)
                'label' => __('backpack::permissionmanager.roles'), // Table column heading
                'type' => 'select_multiple',
                'name' => 'roles', // the method that defines the relationship in your Model
                'entity' => 'roles', // the method that defines the relationship in your Model
                'attribute' => 'name', // foreign key attribute that is shown to user
                'model' => config('permission.models.role'), // foreign key model
            ],
        ]);

        // Role Filter
        $this->crud->addFilter(
            [
                'name' => 'role',
                'type' => 'dropdown',
                'label' => trans('backpack::permissionmanager.role'),
            ],
            config('permission.models.role')::all()->pluck('name', 'id')->toArray(),
            function ($value) { // if the filter is active
                $this->crud->addClause('whereHas', 'roles', function ($query) use ($value) {
                    $query->where('role_id', '=', $value);
                });
            }
        );
    }

    /**
     * @return void
     */
    protected function addUserCreateFields(): void
    {
        $this->crud->addFields([
            [
                'name' => 'email',
                'label' => __('backpack::permissionmanager.email'),
                'type' => 'text',
                'wrapper' => [
                    'class' => 'form-group col-md-4 col-xs-12',
                ],
                'suffix' => '<i class="las la-at"></i>',
            ],
            [
                'name' => 'username',
                'label' => 'Username',
                'type' => 'text',
                'wrapper' => [
                    'class' => 'form-group col-md-4 col-xs-12',
                ],
                'suffix' => '<i class="las la-person-booth"></i>',
            ],
        ]);
    }

    /**
     * @return void
     */
    protected function addUserUpdateFields(): void
    {
        $this->crud->addFields([
            [
                'name' => 'email',
                'label' => __('backpack::permissionmanager.email'),
                'type' => 'text',
                'attributes' => [
                    'readonly' => 'readonly',
                ],
                'wrapper' => [
                    'class' => 'form-group col-md-4 col-xs-12',
                ],
                'suffix' => '<i class="las la-at"></i>',
            ],
            [
                'name' => 'username',
                'label' => 'Username',
                'type' => 'text',
                'attributes' => [
                    'readonly' => 'readonly',
                ],
                'wrapper' => [
                    'class' => 'form-group col-md-4 col-xs-12',
                ],
                'suffix' => '<i class="las la-person-booth"></i>',
            ],
        ]);
    }

    protected function addUserFields()
    {
        $this->crud->addFields([
            [
                'name' => 'name',
                'label' => __('backpack::permissionmanager.name'),
                'type' => 'text',
                'wrapper' => [
                    'class' => 'form-group col-md-4 col-xs-12',
                ],
                'suffix' => '<i class="las la-signature"></i>',
            ],
            [
                'name' => 'password',
                'label' => __('backpack::permissionmanager.password'),
                'type' => 'password',
                'tab' => 'Credential',
            ],
            [
                'name' => 'password_confirmation',
                'label' => __('backpack::permissionmanager.password_confirmation'),
                'type' => 'password',
                'tab' => 'Credential',
            ],
            //            [
            //                'name' => 'must_change_password',
            //                'label' => 'Force User to Change Password at Logon',
            //                'type' => 'checkbox',
            //                'tab' => 'Credential',
            //                'default' => true,
            //            ],
            [
                // two interconnected entities
                'label' => '',
                'field_unique_name' => 'user_role_permission',
                'type' => 'custom_checklist_dependency',
                'name' => 'roles_and_permissions',
                'subfields' => [
                    'primary' => [
                        'label' => trans('backpack::permissionmanager.roles'),
                        'name' => 'roles', // the method that defines the relationship in your Model
                        'entity' => 'roles', // the method that defines the relationship in your Model
                        'entity_secondary' => 'permissions', // the method that defines the relationship in your Model
                        'attribute' => 'name', // foreign key attribute that is shown to user
                        'model' => config('permission.models.role'), // foreign key model
                        'pivot' => true, // on create&update, do you need to add/delete pivot table entries?]
                        'number_columns' => 2, // can be 1,2,5
                    ],
                    'secondary' => [
                        'label' => ucfirst(trans('backpack::permissionmanager.permission_singular')),
                        'name' => 'permissions', // the method that defines the relationship in your Model
                        'entity' => 'permissions', // the method that defines the relationship in your Model
                        'entity_primary' => 'roles', // the method that defines the relationship in your Model
                        'attribute' => 'name', // foreign key attribute that is shown to user
                        'model' => config('permission.models.permission'), // foreign key model
                        'pivot' => true, // on create&update, do you need to add/delete pivot table entries?]
                        'number_columns' => 3, // can be 1,2,3,4,6
                    ],
                ],
                'tab' => 'ACL',
            ],
        ]);
    }

    public function setupCreateOperation()
    {
        $this->addUserCreateFields();
        $this->addUserFields();
        $this->crud->setValidation(StoreRequest::class);
    }

    /**
     * Store a newly created resource in the database.
     *
     * @throws \Illuminate\Http\Client\RequestException
     * @throws Throwable
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function store()
    {
        $this->crud->hasAccessOrFail('create');

        // execute the FormRequest authorization and validation, if one is required
        $request = $this->handlePasswordInput($this->crud->validateRequest());

        try {
            DB::beginTransaction();
            // insert item in the db
            $item = User::create([
                'name' => ucwords($request->name),
                'email' => $request->email,
                'password' => $request->password,
                'personal_information' => [],
            ]);

            $item->roles()->sync($request->input('roles'));
            $item->permissions()->sync($request->input('permissions'));

            $this->data['entry'] = $this->crud->entry = $item;
        } catch (Exception $e) {
            DB::rollBack();

            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }

        DB::commit();

        // show a success message
        Alert::success(trans('backpack::crud.insert_success'))->flash();

        // save the redirect choice for next time
        $this->crud->setSaveAction();

        return $this->crud->performSaveAction($item->getKey());
    }

    public function setupUpdateOperation()
    {
        $this->addUserUpdateFields();
        $this->addUserFields();
        $this->crud->setValidation(UpdateRequest::class);
    }

    /**
     * @param  $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function edit($id)
    {
        $this->traitEdit($id);

        // get current edit fields
        $settings = $this->crud->settings();
        $update_fields = $settings['update.fields'];

        $roles = $this->crud->entry->roles;
        $permissions = $roles->pluck('permissions')->flatten();
        $permissions = $permissions->merge($this->crud->entry->permissions);

        $update_fields['roles_and_permissions']['value'][0] = $roles;
        $update_fields['roles_and_permissions']['value'][1] = $permissions;

        // set the value again
        $this->crud->set('update.fields', $update_fields);

        // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
        return view($this->crud->getEditView(), $this->data);
    }

    /**
     * Handle password input fields.
     *
     * @param  mixed  $request
     * @return Request
     */
    protected function handlePasswordInput(Request $request): Request
    {
        // Remove fields not present on the user.
        $request->request->remove('password_confirmation');
        $request->request->remove('roles_show');
        $request->request->remove('permissions_show');

        // Encrypt password if specified.
        if ($request->input('password')) {
            $request->request->set('password', Hash::make($request->input('password')));
        } else {
            $request->request->remove('password');
        }

        return $request;
    }

    /**
     * Update the specified resource in the database.
     *
     * @throws Throwable
     *
     * @return mixed
     */
    public function update()
    {
        $this->crud->hasAccessOrFail('update');
        $request = $this->handlePasswordInput($this->crud->validateRequest());

        DB::beginTransaction();

        try {
            $item = $this->crud->update(
                $request->get($this->crud->model->getKeyName()),
                $this->crud->getStrippedSaveRequest($request)
            );

            $item->roles()->sync($request->input('roles'));
            $item->permissions()->sync($request->input('permissions'));

            $this->data['entry'] = $this->crud->entry = $item;
        } catch (Exception $exception) {
            DB::rollBack();
            Alert::error($exception->getMessage())->flash();
            abort(back()->withErrors($exception->getMessage())->withInput($request->all()));
        }

        DB::commit();

        // show a success message
        Alert::success(trans('backpack::crud.update_success'))->flash();

        // save the redirect choice for next time
        $this->crud->setSaveAction();

        return $this->crud->performSaveAction($item->getKey());
    }

    #[ArrayShape(['string', Collection::class])]
    private function getRequestParameter(Request $request): array
    {
        $form = [];
        foreach ($request->form ?? [] as $data) {
            if (str_contains($data['name'], '[]')) {
                $name = str_replace('[]', '', $data['name']);
                $form[$name][] = $data['value'];
            } else {
                $form[$data['name']] = $data['value'];
            }
        }

        return [$request->q, collect($form)];
    }
}
