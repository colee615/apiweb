<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminPageController;
use App\Http\Controllers\AdminUserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::redirect('/', '/admin');

Route::get('/admin/login', [AdminAuthController::class, 'create'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'store'])->middleware('throttle:admin-login')->name('admin.login.store');
Route::post('/admin/logout', [AdminAuthController::class, 'destroy'])->name('admin.logout');

Route::middleware('admin.session')->group(function () {
   Route::get('/admin', [AdminPageController::class, 'index'])->name('admin.dashboard');
   Route::get('/admin/pages/{page}/edit', [AdminPageController::class, 'edit'])->name('admin.pages.edit');
   Route::put('/admin/pages/{page}', [AdminPageController::class, 'update'])->name('admin.pages.update');
   Route::post('/admin/pages/{page}/versions/{version}/restore', [AdminPageController::class, 'restore'])->name('admin.pages.restore');
   Route::middleware('admin.role:Administrador')->group(function () {
      Route::get('/admin/users', [AdminUserController::class, 'index'])->name('admin.users.index');
      Route::get('/admin/users/create', [AdminUserController::class, 'create'])->name('admin.users.create');
      Route::post('/admin/users', [AdminUserController::class, 'store'])->name('admin.users.store');
      Route::get('/admin/users/{user}/edit', [AdminUserController::class, 'edit'])->name('admin.users.edit');
      Route::put('/admin/users/{user}', [AdminUserController::class, 'update'])->name('admin.users.update');
   });
});
