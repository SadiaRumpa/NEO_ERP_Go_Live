<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for Milestone 2: Labor Timesheets & Subcontractor CIS.
     */
    public function up(): void
    {
        // 1. Labor Rate Cards (Worker Loaded Rates)
        if (!Schema::hasTable('labor_rate_cards')) {
            Schema::create('labor_rate_cards', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('business_id')->nullable()->index();
                $table->unsignedBigInteger('user_id')->index();
                $table->decimal('base_hourly_rate', 10, 2);
                $table->decimal('overtime_multiplier_1_5', 4, 2)->default(1.50);
                $table->decimal('overtime_multiplier_2_0', 4, 2)->default(2.00);
                $table->decimal('ni_burden_percent', 5, 2)->default(13.80); // Employer NI (13.8%)
                $table->decimal('pension_burden_percent', 5, 2)->default(3.00); // Employer Pension (3%)
                $table->decimal('overhead_burden_percent', 5, 2)->default(10.00); // Overhead markup (10%)
                $table->date('effective_from');
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->foreign('user_id', 'fk_lrc_usr_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // 2. Operative Timesheets
        if (!Schema::hasTable('labor_timesheets')) {
            Schema::create('labor_timesheets', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('business_id')->nullable()->index();
                $table->string('timesheet_number')->unique();
                $table->unsignedBigInteger('user_id')->index();
                $table->unsignedBigInteger('job_id')->index();
                $table->date('work_date');
                $table->time('start_time')->nullable();
                $table->time('end_time')->nullable();
                $table->decimal('travel_hours', 5, 2)->default(0.00);
                $table->decimal('standard_hours', 5, 2)->default(0.00);
                $table->decimal('overtime_1_5_hours', 5, 2)->default(0.00);
                $table->decimal('overtime_2_0_hours', 5, 2)->default(0.00);
                $table->decimal('total_hours', 5, 2)->default(0.00);
                $table->decimal('cost_rate_per_hour', 10, 2)->default(0.00);
                $table->decimal('burden_multiplier', 6, 4)->default(1.0000);
                $table->decimal('direct_pay_amount', 10, 2)->default(0.00);
                $table->decimal('fully_burdened_cost', 10, 2)->default(0.00);
                $table->string('status')->default('submitted')->index(); // submitted, approved, rejected
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->dateTime('approved_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->foreign('user_id', 'fk_lts_usr_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('job_id', 'fk_lts_job_id')->references('id')->on('fjobs')->onDelete('cascade');
            });
        }

        // 3. Subcontractors Directory & CIS Verification
        if (!Schema::hasTable('subcontractors')) {
            Schema::create('subcontractors', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('business_id')->nullable()->index();
                $table->string('company_name');
                $table->string('trading_name')->nullable();
                $table->string('contact_name')->nullable();
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->text('address')->nullable();
                $table->string('utr_number', 15)->index(); // HMRC Unique Taxpayer Reference
                $table->string('company_registration_number')->nullable();
                $table->string('national_insurance_number')->nullable();
                $table->string('cis_tax_status')->default('standard')->index(); // gross (0%), standard (20%), higher (30%)
                $table->string('cis_verification_number')->nullable(); // HMRC Verification reference
                $table->date('verified_at')->nullable();
                $table->string('trade_specialism')->nullable();
                $table->string('status')->default('active')->index(); // active, inactive
                $table->timestamps();
            });
        }

        // 4. Subcontractor Payment Certificates & CIS Deductions
        if (!Schema::hasTable('subcontractor_payment_certificates')) {
            Schema::create('subcontractor_payment_certificates', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('business_id')->nullable()->index();
                $table->string('certificate_number')->unique();
                $table->unsignedBigInteger('subcontractor_id')->index();
                $table->unsignedBigInteger('job_id')->index();
                $table->date('period_start');
                $table->date('period_end');
                $table->string('tax_month', 7)->index(); // e.g. '2026-10' for HMRC CIS300
                $table->decimal('gross_amount', 12, 2)->default(0.00);
                $table->decimal('materials_cost', 12, 2)->default(0.00); // Exempt from CIS deduction
                $table->decimal('labor_amount', 12, 2)->default(0.00); // Subject to CIS deduction
                $table->decimal('cis_tax_rate', 5, 2)->default(20.00); // 0.00, 20.00, or 30.00
                $table->decimal('cis_deduction_amount', 12, 2)->default(0.00);
                $table->decimal('net_payable', 12, 2)->default(0.00);
                $table->string('payment_status')->default('draft')->index(); // draft, certified, paid
                $table->date('paid_at')->nullable();
                $table->unsignedBigInteger('certified_by')->nullable();
                $table->dateTime('certified_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->foreign('subcontractor_id', 'fk_spc_sub_id')->references('id')->on('subcontractors')->onDelete('cascade');
                $table->foreign('job_id', 'fk_spc_job_id')->references('id')->on('fjobs')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subcontractor_payment_certificates');
        Schema::dropIfExists('subcontractors');
        Schema::dropIfExists('labor_timesheets');
        Schema::dropIfExists('labor_rate_cards');
    }
};
