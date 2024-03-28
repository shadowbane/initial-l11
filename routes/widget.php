<?php

use App\Http\Controllers\Widgets;
use Illuminate\Support\Facades\Route;

Route::post('sysadmin/{action}', [Widgets\SysAdminController::class, 'index'])->middleware([
    'role:System Administrators',
]);
