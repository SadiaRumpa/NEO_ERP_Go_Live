<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CustomerApiController;
use App\Http\Controllers\Api\V1\EnquiryApiController;
use App\Http\Controllers\Api\V1\JobApiController;
use App\Http\Controllers\Api\V1\LeadApiController;
use App\Http\Controllers\Api\V1\ScheduleApiController;
use App\Http\Controllers\Api\V1\StockItemApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Version 1
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // Public authentication routes
    Route::post('auth/token', [AuthController::class, 'token'])->name('api.v1.auth.token');

    // Authenticated API routes guarded by Laravel Sanctum
    Route::middleware('auth:sanctum')->group(function () {

        // Session / Token Management
        Route::get('auth/me', [AuthController::class, 'me'])->name('api.v1.auth.me');
        Route::post('auth/revoke', [AuthController::class, 'revoke'])->name('api.v1.auth.revoke');
        Route::post('auth/revoke-all', [AuthController::class, 'revokeAll'])->name('api.v1.auth.revoke-all');

        // Customers
        Route::apiResource('customers', CustomerApiController::class)->names('api.v1.customers');

        // Enquiries
        Route::apiResource('enquiries', EnquiryApiController::class)->except(['destroy', 'update'])->names('api.v1.enquiries');
        Route::patch('enquiries/{id}/status', [EnquiryApiController::class, 'updateStatus'])->name('api.v1.enquiries.status');
        Route::post('enquiries/{id}/convert-to-lead', [EnquiryApiController::class, 'convertToLead'])->name('api.v1.enquiries.convert-to-lead');

        // Leads
        Route::apiResource('leads', LeadApiController::class)->except(['destroy', 'update'])->names('api.v1.leads');
        Route::patch('leads/{id}/status', [LeadApiController::class, 'updateStatus'])->name('api.v1.leads.status');
        Route::post('leads/{id}/convert-to-job', [LeadApiController::class, 'convertToJob'])->name('api.v1.leads.convert-to-job');

        // Jobs
        Route::apiResource('jobs', JobApiController::class)->except(['destroy', 'update'])->names('api.v1.jobs');
        Route::patch('jobs/{id}/status', [JobApiController::class, 'updateStatus'])->name('api.v1.jobs.status');

        // Schedules
        Route::apiResource('schedules', ScheduleApiController::class)->only(['index', 'show', 'store'])->names('api.v1.schedules');

        // Stock Items
        Route::apiResource('stock-items', StockItemApiController::class)->only(['index', 'show'])->names('api.v1.stock-items');
    });
});
