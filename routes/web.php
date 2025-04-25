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
    Route::post('/enquiry/updatedescription/{id}', [EnquiryController::class, 'updateEnquiryDetails'])->name('enquiry.updatedescription');
    Route::post('/enquiry/updateaddress/{id}', [EnquiryController::class, 'updateEnquiryAddress'])->name('enquiry.updateaddress');
    Route::post('/enquiry/updatestatus/{id}', [EnquiryController::class, 'updateEnquiryStatus'])->name('enquiry.updatestatus');
    
    Route::post('enquiries/add-note', [EnquiryController::class, 'enqueryNotes'])->name('enquiry.add-note');
    Route::delete('enquiries/delete-note/{id}', [EnquiryController::class, 'deleteenqueryNotes'])->name('enquiry.destroynote');

    Route::post('enquiries/add-images', [EnquiryController::class, 'enqueryImages'])->name('enquiry.add-images');
    Route::delete('enquiries/delete-image/{id}', [EnquiryController::class, 'destroyEnqueryImage'])->name('enquiry.destroy-image');

    
    Route::resource('customers', CustomerController::class);
    Route::post('/customer/updatefinancialdetails/{id}', [CustomerController::class, 'updateFinancialDetails'])->name('customer.updatefinancialdetails');

    
});
