<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [DashboardController::class, 'redirect'])->name('backpack');
Route::get(
    'login',
    [LoginController::class, 'showLoginForm']
)->name('backpack.auth.login');
Route::post('login', [LoginController::class, 'login']);

Route::middleware(['app_auth'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'dashboard'])->name('backpack.dashboard');
});
