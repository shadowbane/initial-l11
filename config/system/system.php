<?php

return [
    'trusted_proxies' => explode(',', env('TRUSTED_PROXIES', '')),

    'controllers' => [
        'path' => [
            app_path('Http'.DIRECTORY_SEPARATOR.'Controllers') => 'App\Http\Controllers',
        ],
        'valid_actions' => [
            'view',
            'create',
            'update',
            'delete',
        ],
        'action_groups' => [
            'index' => 'view',
            'show' => 'view',
            'search' => 'view',
            'showDetailsRow' => 'view',
            'download' => 'view',
            'ajaxFind' => 'view',
            'listRevisions' => 'view',
            'getFile' => 'view',
            'export' => 'view',
            'exportTestResult' => 'view',
            'report' => 'view',
            'showIndex' => 'view',
            'showCKeditor4' => 'view',
            'showFilePicker' => 'view',
            'showPopup' => 'view',
            'showTinyMCE' => 'view',
            'showTinyMCE4' => 'view',
            'showTinyMCE5' => 'view',
            'fetchCategory' => 'view',
            'fetchTags' => 'view',
            'exportCsv' => 'view',
            'preview' => 'view',
            'downloadPdf' => 'view',

            'create' => 'create',
            'store' => 'create',
            'translateItem' => 'create',
            'clone' => 'create',
            'bulkClone' => 'create',
            'showConnector' => 'create',
            'impersonate' => 'create',
            'stopImpersonating' => 'create',
            'getInlineCreateModal' => 'create',
            'storeInlineCreate' => 'create',

            'edit' => 'update',
            'update' => 'update',
            'reorder' => 'update',
            'saveReorder' => 'update',
            'restoreRevision' => 'update',
            'uploadDocument' => 'update',
            'saveUploadDocument' => 'update',
            'restore' => 'update',

            'destroy' => 'delete',
            'bulkDelete' => 'delete',
        ],

        'skipped_controllers' => [
        ],
    ],
];
