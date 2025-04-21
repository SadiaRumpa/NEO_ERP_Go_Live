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
use App\Http\Controllers\GlobalController;

Route::get('/', function () {
    return Redirect::to('/login');
});
  
Auth::routes();
  
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/query', [GlobalController::class, 'query'])->name('query');
Route::get('/checkpostcode/{key}', [GlobalController::class, 'checkPostCode'])->name('query.checkpostcode');
Route::post('/submit-query', [GlobalController::class, 'storeQuery'])->name('query.submit');
  
Route::group(['middleware' => ['auth']], function() {
    Route::resource('roles', RoleController::class);
    Route::resource('permissions', PermissionController::class);
    Route::resource('users', UserController::class);
    Route::resource('mailtemplates', MailTemplateController::class);
    Route::get('/mail-template/{id}', [MailTemplateController::class, 'showmailtemplate'])->name('mailtemplate.showmail');

    Route::resource('enquiries', EnquiryController::class);
    Route::post('enquiries/add-note', [EnquiryController::class, 'enqueryNotes'])->name('enquiry.add-note');
    Route::resource('customers', CustomerController::class);

    
});
