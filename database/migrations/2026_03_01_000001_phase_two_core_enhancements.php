<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. job_schedules: add is_provisional flag and composite index
        Schema::table('job_schedules', function (Blueprint $table) {
            if (!Schema::hasColumn('job_schedules', 'is_provisional')) {
                $table->boolean('is_provisional')->default(false)->after('status')->index();
            }
            $table->index(['start_date', 'end_date'], 'job_schedules_start_end_date_index');
        });

        // 2. stock_item_requisitions: add issued_by, approved_at, issued_at
        Schema::table('stock_item_requisitions', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_item_requisitions', 'issued_by')) {
                $table->foreignId('issued_by')->nullable()->after('approved_by')->constrained('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('stock_item_requisitions', 'approved_at')) {
                $table->dateTime('approved_at')->nullable()->after('issued_by');
            }
            if (!Schema::hasColumn('stock_item_requisitions', 'issued_at')) {
                $table->dateTime('issued_at')->nullable()->after('approved_at');
            }
        });

        // 3. Performance indexes for high-frequency queries
        Schema::table('enquiries', function (Blueprint $table) {
            $table->index('enquiry_status', 'enquiries_enquiry_status_index');
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->index('lead_status', 'leads_lead_status_index');
        });

        Schema::table('fjobs', function (Blueprint $table) {
            if (!Schema::hasColumn('fjobs', 'assigned_to')) {
                $table->json('assigned_to')->nullable()->after('job_status');
            }
            $table->index('job_status', 'fjobs_job_status_index');
        });

        Schema::table('job_schedule_workers', function (Blueprint $table) {
            $table->index(['user_id', 'assigned_date'], 'jsw_user_assigned_date_index');
        });

        Schema::table('stock_item_movements', function (Blueprint $table) {
            $table->index(['item_variant_id', 'movement_date'], 'sim_variant_movement_date_index');
        });

        Schema::table('vehicle_details', function (Blueprint $table) {
            $table->index('mot_expiry_date', 'vd_mot_expiry_index');
            $table->index('tax_expiry_date', 'vd_tax_expiry_index');
            $table->index('insurance_expiry_date', 'vd_insurance_expiry_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_details', function (Blueprint $table) {
            $table->dropIndex('vd_mot_expiry_index');
            $table->dropIndex('vd_tax_expiry_index');
            $table->dropIndex('vd_insurance_expiry_index');
        });

        Schema::table('stock_item_movements', function (Blueprint $table) {
            $table->dropIndex('sim_variant_movement_date_index');
        });

        Schema::table('job_schedule_workers', function (Blueprint $table) {
            $table->dropIndex('jsw_user_assigned_date_index');
        });

        Schema::table('fjobs', function (Blueprint $table) {
            $table->dropIndex('fjobs_job_status_index');
            if (Schema::hasColumn('fjobs', 'assigned_to')) {
                $table->dropColumn('assigned_to');
            }
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex('leads_lead_status_index');
        });

        Schema::table('enquiries', function (Blueprint $table) {
            $table->dropIndex('enquiries_enquiry_status_index');
        });

        Schema::table('stock_item_requisitions', function (Blueprint $table) {
            $table->dropForeign(['issued_by']);
            $table->dropColumn(['issued_by', 'approved_at', 'issued_at']);
        });

        Schema::table('job_schedules', function (Blueprint $table) {
            $table->dropIndex('job_schedules_start_end_date_index');
            $table->dropIndex(['is_provisional']);
            $table->dropColumn('is_provisional');
        });
    }
};
