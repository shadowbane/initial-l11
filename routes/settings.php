<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Extend;
use Illuminate\Support\Facades\Route;

// elfinder
Route::any('elfinder/connector', [Extend\ElfinderController::class, 'showConnector'])->name('elfinder.connector');
Route::get('elfinder', [Extend\ElfinderController::class, 'showIndex'])->name('elfinder.index');
Route::get('elfinder/ckeditor', [Extend\ElfinderController::class, 'showCKeditor4'])->name('elfinder.ckeditor');
Route::get('elfinder/filepicker/{input_id}',
    [Extend\ElfinderController::class, 'showFilePicker'])->name('elfinder.filepicker');
Route::get('elfinder/popup/{input_id}', [Extend\ElfinderController::class, 'showPopup'])->name('elfinder.popup');
Route::get('elfinder/tinymce', [Extend\ElfinderController::class, 'showTinyMCE'])->name('elfinder.tinymce');
Route::get('elfinder/tinymce4', [Extend\ElfinderController::class, 'showTinyMCE4'])->name('elfinder.tinymce4');
Route::get('elfinder/tinymce5', [Extend\ElfinderController::class, 'showTinyMCE5'])->name('elfinder.tinymce5');

// core route
Route::crud('permission', Extend\PermissionCrudController::class);
Route::crud('role', Extend\RoleCrudController::class);
Route::crud('user', Extend\UserCrudController::class);
Route::crud('menu-item', Admin\MenuItemCrudController::class);

// log files
Route::get('log', [\Backpack\LogManager\app\Http\Controllers\LogController::class, 'index'])
    ->name('log.index');
Route::get('log/preview/{file_name}',
    [\Backpack\LogManager\app\Http\Controllers\LogController::class, 'preview'])->name('log.show');
Route::get('log/download/{file_name}',
    [\Backpack\LogManager\app\Http\Controllers\LogController::class, 'download'])->name('log.download');
Route::delete('log/delete/{file_name}',
    [\Backpack\LogManager\app\Http\Controllers\LogController::class, 'delete'])->name('log.destroy');

// activity log
Route::crud('logdetail', Admin\LogDetailCrudController::class);

// backups
Route::get('backup', [Extend\BackupController::class, 'index'])->name('backup.index');
Route::put('backup/create', [Extend\BackupController::class, 'create'])->name('backup.store');
Route::get('backup/download/', [Extend\BackupController::class, 'download'])->name('backup.download');
Route::delete('backup/delete/', [Extend\BackupController::class, 'delete'])->name('backup.destroy');

// settings
Route::crud('setting', Admin\SettingCrudController::class);
