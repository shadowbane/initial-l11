<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Traits\CrudControllerTrait;
use App\Http\Requests\MenuItemRequest;
use App\Models\MenuItem;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Exception;
use Prologue\Alerts\Facades\Alert;

/**
 * Class MenuItemCrudController.
 *
 * @property CrudPanel $crud
 */
class MenuItemCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ReorderOperation;
    use CrudControllerTrait;

    /**
     * @throws Exception
     */
    public function setup()
    {
        $this->crud->setModel(MenuItem::class);
        $this->crud->setRoute('/menu-item');
        $this->crud->setEntityNameStrings('Side Menu', 'Side Menu');

        $this->checkPermission();

        $this->crud->setReorderView('vendor.backpack.crud.reorder-menu');
    }

    /**
     * Setup ListOperation.
     */
    protected function setupListOperation()
    {
        $this->crud->addColumns([
            [
                'name' => 'name',
                'label' => 'Label',
            ],
            [
                'label' => 'Grouping',
                'name' => 'grouping',
            ],
            [
                'label' => 'Parent',
                'type' => 'select_from_array',
                'name' => 'parent_id',
                'options' => MenuItem::pluck('name', 'id'),
            ],
        ]);
    }

    /**
     * @throws Exception
     */
    protected function setupCreateOperation()
    {
        $this->crud->setValidation(MenuItemRequest::class);

        $this->crud->addFields([
            [
                'name' => 'name',
                'label' => 'Label',
            ],
            [
                'name' => 'grouping',
                'label' => 'Grouping',
            ],
            [
                'label' => 'Parent',
                'type' => 'select2_from_array',
                'name' => 'parent_id',
                'entity' => 'parent',
                'attribute' => 'name',
                'options' => MenuItem::getParent(),
                'allows_null' => true,
            ],
            [
                'name' => 'type',
                'type' => 'hidden',
                'value' => 'internal_link',
            ],
            [
                'label' => 'URL',
                'name' => 'link',
                'type' => 'text',
            ],
            [
                'label' => 'Icon',
                'name' => 'icon',
                'type' => 'icon_picker',
                'iconset' => 'lineawesome',
            ],
            [
                'label' => 'Roles',
                'type' => 'checklist',
                'name' => 'roles',
                'entity' => 'roles',
                'attribute' => 'name',
                'model' => "\App\Models\Role",
                'pivot' => true,
            ],
        ]);
    }

    /**
     * @throws Exception
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    /**
     * Setup ReorderOperation.
     */
    protected function setupReorderOperation()
    {
        // define which model attribute will be shown on draggable elements
        $this->crud->set('reorder.label', 'name');
        // define how deep the admin is allowed to nest the items
        // for infinite levels, set it to 0
        $this->crud->set('reorder.max_level', 3);
    }

    /**
     * @return array|\Illuminate\Http\Response
     */
    public function store()
    {
        $this->crud->hasAccessOrFail('create');

        // execute the FormRequest authorization and validation, if one is required
        $request = $this->crud->validateRequest();
        $request->request->set('icon', str_replace('fa', 'la', str_replace('fas ', '', $request->icon)));
        $this->crud->setRequest($request);

        // insert item in the db
        $item = $this->crud->create($this->crud->getStrippedSaveRequest(
            $request
        ));
        $this->data['entry'] = $this->crud->entry = $item;

        // show a success message
        Alert::success(trans('backpack::crud.insert_success'))->flash();

        // save the redirect choice for next time
        $this->crud->setSaveAction();

        return $this->crud->performSaveAction($item->getKey());
    }

    /**
     * @param  $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($id)
    {
        $this->crud->hasAccessOrFail('update');
        // get entry ID from Request (makes sure its the last ID for nested resources)
        $id = $this->crud->getCurrentEntryId() ?? $id;
        $this->crud->setOperationSetting('fields', $this->crud->getUpdateFields());
        // get the info for that entry
        $this->data['entry'] = $this->crud->getEntry($id);
        $this->data['crud'] = $this->crud;
        $this->data['saveAction'] = $this->crud->getSaveAction();
        $this->data['title'] = $this->crud->getTitle() ?? trans('backpack::crud.edit').' '.$this->crud->entity_name;

        $this->data['id'] = $id;

        // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
        return view($this->crud->getEditView(), $this->data);
    }

    /**
     * @return array|\Illuminate\Http\Response
     */
    public function update()
    {
        $this->crud->hasAccessOrFail('update');

        // execute the FormRequest authorization and validation, if one is required
        $request = $this->crud->validateRequest();
        $request->request->set('icon', str_replace('fa', 'la', str_replace('fas ', '', $request->icon)));
        $this->crud->setRequest($request);

        // update the row in the db
        $item = $this->crud->update(
            $request->get($this->crud->model->getKeyName()),
            $this->crud->getStrippedSaveRequest($request)
        );
        $this->data['entry'] = $this->crud->entry = $item;

        // show a success message
        Alert::success(trans('backpack::crud.update_success'))->flash();

        // save the redirect choice for next time
        $this->crud->setSaveAction();

        return $this->crud->performSaveAction($item->getKey());
    }
}
