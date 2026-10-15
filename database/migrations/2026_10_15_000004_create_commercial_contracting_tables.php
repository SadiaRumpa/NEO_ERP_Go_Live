<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for Milestone 4: Commercial Variations, Retentions & Valuations.
     */
    public function up(): void
    {
        // 1. Contract Variations (Change Orders)
        if (!Schema::hasTable('job_variations')) {
            Schema::create('job_variations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('business_id')->nullable()->index();
                $table->string('variation_number')->unique();
                $table->unsignedBigInteger('job_id')->index();
                $table->string('title');
                $table->text('description')->nullable();
                $table->decimal('cost_amount', 12, 2)->default(0.00);
                $table->decimal('markup_percentage', 5, 2)->default(20.00);
                $table->decimal('quoted_amount', 12, 2)->default(0.00);
                $table->string('status')->default('draft')->index(); // draft, submitted, approved, rejected
                $table->string('client_signer_name')->nullable();
                $table->string('client_signer_email')->nullable();
                $table->dateTime('signed_at')->nullable();
                $table->text('signature_data')->nullable(); // SVG or Base64 data URL
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->dateTime('approved_at')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->timestamps();

                $table->foreign('job_id', 'fk_jv_job_id')->references('id')->on('fjobs')->onDelete('cascade');
                $table->foreign('approved_by', 'fk_jv_usr_id')->references('id')->on('users')->nullOnDelete();
            });
        }

        // 2. Progress Valuations (Applications for Payment)
        if (!Schema::hasTable('job_valuations')) {
            Schema::create('job_valuations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('business_id')->nullable()->index();
                $table->string('application_number')->unique();
                $table->unsignedBigInteger('job_id')->index();
                $table->date('valuation_date');
                $table->date('period_end');
                $table->decimal('cumulative_work_value', 12, 2)->default(0.00);
                $table->decimal('materials_on_site_value', 12, 2)->default(0.00);
                $table->decimal('gross_valuation', 12, 2)->default(0.00);
                $table->decimal('previous_certified_amount', 12, 2)->default(0.00);
                $table->decimal('current_gross_amount', 12, 2)->default(0.00);
                $table->decimal('retention_rate', 5, 2)->default(5.00); // 3% or 5%
                $table->decimal('retention_deduction', 12, 2)->default(0.00);
                $table->decimal('net_claim_amount', 12, 2)->default(0.00);
                $table->string('status')->default('draft')->index(); // draft, submitted, certified, paid
                $table->decimal('certified_amount', 12, 2)->nullable();
                $table->dateTime('certified_at')->nullable();
                $table->unsignedBigInteger('certified_by')->nullable();
                $table->date('paid_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->foreign('job_id', 'fk_jval_job_id')->references('id')->on('fjobs')->onDelete('cascade');
                $table->foreign('certified_by', 'fk_jval_usr_id')->references('id')->on('users')->nullOnDelete();
            });
        }

        // 3. Retention Accounting Ledger
        if (!Schema::hasTable('job_retentions')) {
            Schema::create('job_retentions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('business_id')->nullable()->index();
                $table->unsignedBigInteger('job_id')->index();
                $table->unsignedBigInteger('valuation_id')->nullable()->index();
                $table->string('retention_reference')->unique();
                $table->decimal('total_retained_amount', 12, 2)->default(0.00);
                $table->date('practical_completion_date')->nullable();
                $table->decimal('pc_release_amount', 12, 2)->default(0.00);
                $table->dateTime('pc_released_at')->nullable();
                $table->date('defects_expiry_date')->nullable();
                $table->decimal('defects_release_amount', 12, 2)->default(0.00);
                $table->dateTime('defects_released_at')->nullable();
                $table->string('status')->default('held')->index(); // held, partially_released, fully_released
                $table->timestamps();

                $table->foreign('job_id', 'fk_jret_job_id')->references('id')->on('fjobs')->onDelete('cascade');
                $table->foreign('valuation_id', 'fk_jret_val_id')->references('id')->on('job_valuations')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_retentions');
        Schema::dropIfExists('job_valuations');
        Schema::dropIfExists('job_variations');
    }
};
