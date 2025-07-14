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
use App\Http\Controllers\FjobController;
use App\Http\Controllers\GlobalController;
use App\Http\Controllers\JobAttributeController;
use App\Http\Controllers\JobTypeController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\QuotationTemplateController;
use App\Http\Controllers\SchedulerController;

Route::get('/', function () {
    return Redirect::to('/login');
});

Auth::routes();

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/query', [GlobalController::class, 'query'])->name('query');
Route::get('/checkpostcode/{key}', [GlobalController::class, 'checkPostCode'])->name('query.checkpostcode');
Route::get('/checkposttown/{key}', [GlobalController::class, 'checkPostTown'])->name('query.checkposttown');
Route::post('/submit-query', [GlobalController::class, 'storeQuery'])->name('query.submit');

Route::middleware(['auth'])->group(function () {
    Route::resource('roles', RoleController::class);
    Route::resource('permissions', PermissionController::class);
    Route::resource('users', UserController::class);
    Route::resource('mailtemplates', MailTemplateController::class);
    Route::get('/mail-template/{id}', [MailTemplateController::class, 'showmailtemplate'])->name('mailtemplate.showmail');

    Route::resource('enquiries', EnquiryController::class);
    Route::get('enquiry/completed', [EnquiryController::class, 'showCompleted'])->name('enquiry.show.completed');
    Route::get('enquiry/rejected', [EnquiryController::class, 'showRejected'])->name('enquiry.show.rejected');
    Route::post('/enquiry/updatedescription/{id}', [EnquiryController::class, 'updateEnquiryDetails'])->name('enquiry.updatedescription');
    Route::post('/enquiry/updateaddress/{id}', [EnquiryController::class, 'updateEnquiryAddress'])->name('enquiry.updateaddress');
    Route::post('/enquiry/updatestatus/{id}', [EnquiryController::class, 'updateEnquiryStatus'])->name('enquiry.updatestatus');

    Route::post('enquiries/add-note/{enquiry_id}', [EnquiryController::class, 'enqueryNotes'])->name('enquiry.add-note');
    Route::delete('enquiries/delete-note/{id}', [EnquiryController::class, 'deleteenqueryNotes'])->name('enquiry.destroynote');

    Route::post('enquiries/add-images', [EnquiryController::class, 'enqueryImages'])->name('enquiry.add-images');
    Route::delete('enquiries/delete-image/{id}', [EnquiryController::class, 'destroyEnqueryImage'])->name('enquiry.destroy-image');

    Route::post('enquiry-to-lead-conversion', [EnquiryController::class, 'enquerytoLeadConversion'])->name('enquiry.leadconversion');

    Route::resource('customers', CustomerController::class);
    Route::post('/customers/{id}/update-basic-info', [CustomerController::class, 'updateBasicInfo']);
    Route::post('/customer/storeaddress/{id}', [CustomerController::class, 'storeCustomerAddress'])->name('customer.storeaddress');
    Route::delete('/customer/deleteaddress/{id}', [CustomerController::class, 'deleteCustomerAddress'])->name('customer.deleteaddress');
    Route::post('/customer/address-update/{id}', [CustomerController::class, 'updateCustomerAddress'])->name('customer.address-update');
    Route::post('/customer/address-delete/{id}', [CustomerController::class, 'deleteCustomerAddress'])->name('customer.address-delete');
    Route::post('/customer/updatefinancialdetails/{id}', [CustomerController::class, 'updateFinancialDetails'])->name('customer.updatefinancialdetails');

    Route::resource('leads', LeadController::class);
    Route::get('lead/completed', [LeadController::class, 'showCompleted'])->name('lead.show.completed');
    Route::get('lead/rejected', [LeadController::class, 'showRejected'])->name('lead.show.rejected');
    Route::post('lead/updatedescription/{id}', [LeadController::class, 'updateLeadDetails'])->name('leads.updatedescription');
    Route::post('/leads/updateaddress/{id}', [LeadController::class, 'updateLeadAddress'])->name('leads.updateaddress');
    Route::post('/leads/updatestatus/{id}', [LeadController::class, 'updateLeadStatus'])->name('leads.updatestatus');

    Route::post('lead-to-job-conversion', [LeadController::class, 'leadtoJobConversion'])->name('lead.jobconversion');

    Route::post('lead/add-note/{leadid}', [LeadController::class, 'leadNotes'])->name('leads.add-note');
    Route::delete('lead/delete-note/{id}', [LeadController::class, 'deleteLeadNotes'])->name('leads.destroynote');

    Route::post('leads/add-images', [LeadController::class, 'addLeadImages'])->name('leads.add-images');
    Route::delete('leads/delete-image/{id}', [LeadController::class, 'destroyLeadImage'])->name('leads.destroy-image');

    Route::post('leads/attachments-upload', [LeadController::class, 'uploadLeadAttachments'])->name('leads.attachments-upload');
    Route::delete('leads/delete-attachment/{id}', [LeadController::class, 'destroyLeadAttachment'])->name('leads.destroy-attachment');

    Route::resource('jobtypes', JobTypeController::class);

    Route::resource('quotationtemplates', QuotationTemplateController::class);
    Route::post('quotationtemplate/addsection/', [QuotationTemplateController::class, 'addSection'])->name('quotationtemplate.add-section');
    Route::post('/quotationtemplate/section/populate', [QuotationTemplateController::class, 'populateSections'])->name('quotationtemplate.section-populate');
    Route::delete('quotationtemplate/deletesection/{id}', [QuotationTemplateController::class, 'deleteSection'])->name('quotationtemplate.delete-section');

    Route::get('lead-quotations/{leadid}', [QuotationController::class, 'index'])->name('quotations.lead');
    Route::get('quotation/{id}', [QuotationController::class, 'show'])->name('quotations.show');
    Route::get('quotation/create/{leadid}/{templateid}', [QuotationController::class, 'create'])->name('quotations.create');
    Route::post('quotation/store', [QuotationController::class, 'store'])->name('quotations.store');
    Route::get('quotation/edit/{id}', [QuotationController::class, 'edit'])->name('quotations.edit');
    Route::post('quotation/update/{id}', [QuotationController::class, 'update'])->name('quotations.update');
    Route::delete('quotation/destroy/{id}', [QuotationController::class, 'destroy'])->name('quotations.destroy');
    
    Route::post('quotation/updatestatus/{id}', [QuotationController::class, 'updateQuotationStatus'])->name('quotations.updatestatus');
    
    Route::post('quotationsections/reorder', [QuotationController::class, 'reorderQuotationSections'])->name('quotationsections.reorder');
    Route::post('quotationsections/update/{id}', [QuotationController::class, 'updateQuotationSectionsContent'])->name('quotationsections.update');
    Route::post('quotationsections/upload-attachments/{id}', [QuotationController::class, 'uploadQuotationSectionsAttachments'])->name('quotationsections.upload-attachments');
    Route::delete('quotationsections/delete-attachments/{id}', [QuotationController::class, 'destroyQuotationSectionsAttachments'])->name('quotationsections.destroy-attachments');

    Route::get('quotation/pdf/{quotationId}', [QuotationController::class, 'downloadQuotationPdf'])->name('quotations.download-pdf');

    Route::get('jobs', [FjobController::class, 'index'])->name('job.index');
    Route::get('jobs/completed', [FjobController::class, 'showCompleted'])->name('job.show.completed');
    Route::get('jobs/rejected', [FjobController::class, 'showRejected'])->name('job.show.rejected');
    Route::get('job/{id}', [FjobController::class, 'show'])->name('job.show');
    Route::post('job/updatedescription/{id}', [FjobController::class, 'updateJobDetails'])->name('job.updatedescription');
    Route::post('job/updateaddress/{id}', [FjobController::class, 'updateJobAddress'])->name('job.updateaddress');
    Route::post('job/updatestatus/{id}', [FjobController::class, 'updateJobStatus'])->name('job.updatestatus');
    Route::post('job/updatetitle/{id}', [FjobController::class, 'updateJobTitle'])->name('job.updatetitle');

    Route::post('job/add-note', [FjobController::class, 'jobNotes'])->name('job.add-note');
    Route::delete('job/delete-note/{id}', [FjobController::class, 'deleteJobNotes'])->name('job.destroynote');

    Route::post('job/add-images', [FjobController::class, 'addJobImages'])->name('job.add-images');
    Route::delete('job/delete-image/{id}', [FjobController::class, 'destroyJobImage'])->name('job.destroy-image');

    Route::post('job/attachments-upload', [FjobController::class, 'uploadJobAttachments'])->name('job.attachments-upload');
    Route::delete('job/delete-attachment/{id}', [FjobController::class, 'destroyJobAttachment'])->name('job.destroy-attachment');

    Route::get('job-add-attributes/{jobid}', [FjobController::class, 'jobAddAttributes'])->name('job-attributes.add');
    Route::post('job-add-attributes/{jobid}/store', [FjobController::class, 'storeJobAddAttributes'])->name('job-attributes.add-store');

    Route::get('scheduler', [SchedulerController::class, 'index'])->name('scheduler.index');
    Route::post('scheduler/workers', [SchedulerController::class, 'getSchedulerCalendarData'])->name('scheduler.workers-calendar');
    Route::get('scheduler/job-schedule/{id}', [SchedulerController::class, 'showSchedule'])->name('scheduler.job-schedule');

    Route::post('scheduler/fake-workers', [SchedulerController::class, 'getFakeSchedulerCalendarData'])->name('scheduler.fake-workers-calendar');
    Route::get('scheduler/job-schedule/{id}', [SchedulerController::class, 'showSchedule'])->name('scheduler.job-schedule');
    
    Route::post('scheduler/store-job-schedule', [SchedulerController::class, 'storeJobSchedule'])->name('scheduler.store-job-schedule');
    Route::put('scheduler/update-job-schedule/{id}', [SchedulerController::class, 'updateJobSchedule'])->name('scheduler.update-job-schedule');
    Route::delete('scheduler/delete-schedule/{id}', [SchedulerController::class, 'deleteSchedule'])->name('scheduler.delete-schedule');

    Route::post('scheduler/store-fake-job-schedule', [SchedulerController::class, 'storeFakeJobSchedule'])->name('scheduler.store-fake-job-schedule');
    Route::put('scheduler/update-fake-job-schedule/{id}', [SchedulerController::class, 'updateFakeJobSchedule'])->name('scheduler.update-fake-job-schedule');
    Route::delete('scheduler/delete-fake-schedule/{id}', [SchedulerController::class, 'deleteFakeSchedule'])->name('scheduler.delete-fake-schedule');

    Route::get('job-attributes', [JobAttributeController::class, 'index'])->name('job-attributes.index');
    Route::post('job-attributes/store', [JobAttributeController::class, 'storeJobAttribute'])->name('job-attributes.store');
    Route::get('job-attributes/{id}/edit', [JobAttributeController::class, 'editJobAttribute'])->name('job-attributes.edit');
    Route::put('job-attributes/{id}/update', [JobAttributeController::class, 'updateJobAttribute'])->name('job-attributes.update');
    
    Route::post('job-attributes/{jobAttribute}/storedetails', [JobAttributeController::class, 'storeAttributeDetails'])->name('job-attributes.store-details');
    Route::put('job-attributes/{jobAttributedetails}/updatedetails', [JobAttributeController::class, 'updateAttributeDetails'])->name('job-attributes.update-details');

    
});
