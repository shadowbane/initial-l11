<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Traits\CrudControllerTrait;
use App\Models\Activity;
use App\Models\User;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Exception;
use Illuminate\Support\Facades\DB;

/**
 * Class LogDetailCrudController.
 *
 * @property CrudPanel $crud
 */
class LogDetailCrudController extends CrudController
{
    use CrudControllerTrait;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;

    /**
     * @throws Exception
     */
    public function setup()
    {
        /*
        |--------------------------------------------------------------------------
        | BASIC CRUD INFORMATION
        |--------------------------------------------------------------------------
        */
        $this->crud->setModel(Activity::class);
        $this->crud->setRoute('/logdetail');
        $this->crud->setEntityNameStrings('Log Detail', 'Log Details');
        $this->crud->denyAccess(['create', 'update', 'reorder', 'delete']);
    }

    /**
     * Setup ListOperation.
     */
    public function setupListOperation()
    {
        $this->crud->removeAllButtons();
        $this->crud->addColumns([
            [
                'name' => 'created_at',
                'label' => 'Timestamps',
            ],
            [
                'name' => 'description',
                'label' => 'Description',
            ],
            [
                'name' => 'subject_type',
                'label' => 'Affected Model',
            ],
            [
                'name' => 'causer',
                'label' => 'Causer',
                'type' => 'model_function',
                'function_name' => 'getCauser',
            ],
        ]);

        $this->crud->denyAccess(['create', 'update', 'reorder', 'delete']);
        $this->crud->enableDetailsRow();
        $this->crud->orderBy('id', 'DESC');

        $this->crud->addFilter([
            'name' => 'causer',
            'label' => 'User',
            'type' => 'select2',
        ], function () {
            $users = User::select(['id', DB::raw("CONCAT(name,' (',email,')') as name")])
                ->orderBy('name', 'ASC')
                ->pluck('name', 'id');

            return $users->toArray();
        }, function ($value) {
            $this->crud->addClause('where', 'causer_id', $value);
        });

        $this->crud->addFilter([
            'name' => 'description',
            'label' => 'Description',
            'type' => 'dropdown',
        ], function () {
            return [
                'created' => 'Created',
                'updated' => 'Updated',
                'deleted' => 'Deleted',
                'Web / Api Access' => 'Web / Api Access',
                'Login' => 'Login',
                'Logout' => 'Logout',
            ];
        }, function ($value) {
            $this->crud->addClause('where', 'description', '=', $value);
        });

        $this->crud->addFilter([
            'name' => 'key',
            'label' => 'Affected Model',
            'type' => 'select2',
        ], function () {
            return DB::table('activity_log')
                ->selectRaw('DISTINCT subject_type')
                ->where('subject_type', '<>', '')
                ->orderBy('subject_type', 'ASC')
                ->get()
                ->pluck('subject_type', 'subject_type')->toArray();
        }, function ($value) {
            $value = str_replace('\\', '\\\\', $value);
            $this->crud->addClause('where', 'subject_type', 'LIKE', $value.'%');
        });

        $this->crud->addFilter([
            'name' => 'method',
            'label' => 'Request Method',
            'type' => 'dropdown',
        ], function () {
            $filter = collect([
                ['method' => 'DELETE'],
                ['method' => 'GET'],
                ['method' => 'HEAD'],
                ['method' => 'OPTIONS'],
                ['method' => 'POST'],
                ['method' => 'PUT'],
            ]);

            return $filter->pluck('method', 'method')->toArray();
        }, function ($value) {
            $this->crud->query->whereJsonContains('request_detail->method', $value);
        });
    }

    /**
     * @param  $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showDetailsRow($id)
    {
        $data['data'] = Activity::find($id);

        return view('layouts.activity-logs.activity_detail', $data);
    }
}
