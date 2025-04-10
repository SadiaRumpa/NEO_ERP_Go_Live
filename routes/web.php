<?php

use App\Http\Controllers\MailTemplateController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Redirect;
use App\Http\Controllers\EnquiryController;
use App\Http\Controllers\CustomerController;

Route::get('/', function () {
    return Redirect::to('/login');
});
  
Auth::routes();
  
Route::get('/', [HomeController::class, 'index'])->name('home');
  
Route::group(['middleware' => ['auth']], function() {
    Route::resource('roles', RoleController::class);
    Route::resource('permissions', PermissionController::class);
    Route::resource('users', UserController::class);
    Route::resource('mailtemplates', MailTemplateController::class);

    Route::resource('enquiries', EnquiryController::class);
    Route::resource('customers', CustomerController::class);
});
