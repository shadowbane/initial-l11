<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Traits\CrudControllerTrait;
use App\Models\Setting;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\Settings\app\Http\Controllers\SettingCrudController as SC;
use Exception;
use Illuminate\Support\Facades\DB;

/**
 * Class SettingCrudController.
 *
 * @property CrudPanel $crud
 */
class SettingCrudController extends SC
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use CrudControllerTrait;

    /**
     * @throws Exception
     */
    public function setup()
    {
        $this->crud->setModel(Setting::class);
        $this->crud->setRoute('/setting');
        $this->crud->setEntityNameStrings(trans('backpack::settings.setting_singular'),
            trans('backpack::settings.setting_plural'));

        $this->checkPermission();

        // set default ordering
        $this->crud->query->orderBy('key', 'ASC');

        // only show active config
        $this->crud->addClause('where', 'active', 1);

        $this->crud->addClause('where', 'active', 1);
        $this->crud->denyAccess(['create', 'delete']);
    }

    /**
     * Setup ListOperation.
     */
    public function setupListOperation()
    {
        $this->crud->setColumns([
            [
                'name' => 'name',
                'label' => trans('backpack::settings.name'),
            ],
            [
                'name' => 'description',
                'label' => trans('backpack::settings.description'),
                'limit' => 1000,
            ],
            [
                'name' => 'value',
                'label' => trans('backpack::settings.value'),
                'type' => 'backpack_setting_values',
            ],
        ]);

        $this->crud->addFilter([
            'name' => 'key',
            'label' => 'Type',
            'type' => 'select2',
        ], function () {
            return DB::table('settings')->selectRaw("DISTINCT SUBSTRING_INDEX(settings.`key`,'.',1) as keyName")
                ->get()->pluck('keyName', 'keyName')->toArray();
        }, function ($value) {
            $this->crud->addClause('where', 'key', 'LIKE', $value.'%');
        });

        $this->crud->addFilter([
            'name' => 'name',
            'label' => 'Name',
            'type' => 'text',
        ], '', function ($value) {
            $this->crud->addClause('where', 'name', 'LIKE', '%'.$value.'%');
        });
    }

    /**
     * Setup UpdateOperation.
     */
    public function setupUpdateOperation()
    {
        CRUD::addField([
            'name' => 'name',
            'label' => trans('backpack::settings.name'),
            'type' => 'text',
            'attributes' => [
                'disabled' => 'disabled',
            ],
        ]);

        CRUD::addField([
            'name' => 'description',
            'label' => trans('backpack::settings.description'),
            'type' => 'textarea',
            'attributes' => [
                'disabled' => 'disabled',
            ],
        ]);

        CRUD::addField(CRUD::getCurrentEntry()->field);
    }

    /**
     * @param  int  $id
     * @return \Backpack\CRUD\app\Http\Controllers\Operations\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
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

        $update_fields = $this->crud->settings();
        /*
         * If using select2_from_array
         * Data Sample: {"name":"value","label":"Value","type":"select2_from_array","options":"","class_func":{"class":"\\App\\Models\\Perkuliahan\\Semester"}}
         */
        if (isset($update_fields['update.fields']['value']['class_func'])) {
            $update_fields['update.fields']['value']['options'] =
                $this->getData($update_fields['update.fields']['value']['class_func']['class']);
        }

        $this->crud->set('update.fields', $update_fields['update.fields']);

        // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
        return view($this->crud->getEditView(), $this->data);
    }

    /**
     * @param  $class
     * @return mixed
     */
    private function getData($class)
    {
        return $class::orderBy('id', 'DESC')->pluck('name', 'id');
    }
}
