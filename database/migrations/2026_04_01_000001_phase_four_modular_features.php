<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for Phase 4 (Modular Monolith, Invoicing, Field PWA, Tenancy).
     */
    public function up(): void
    {
        // 1. Tenancy Scoping (business_id)
        $tenantedTables = ['users', 'customers', 'enquiries', 'leads', 'quotations', 'fjobs', 'stock_items'];
        foreach ($tenantedTables as $tableName) {
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'business_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->unsignedBigInteger('business_id')->nullable()->after('id')->index();
                });
            }
        }

        // 2. Invoicing (FR-026)
        if (!Schema::hasTable('invoices')) {
            Schema::create('invoices', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('business_id')->nullable()->index();
                $table->unsignedBigInteger('customer_id')->index();
                $table->unsignedBigInteger('job_id')->nullable()->index();
                $table->unsignedBigInteger('quotation_id')->nullable()->index();
                $table->string('invoice_number')->unique();
                $table->date('invoice_date');
                $table->date('due_date');
                $table->decimal('subtotal', 12, 2)->default(0.00);
                $table->decimal('tax_rate', 5, 2)->default(20.00); // Standard UK VAT (20%)
                $table->decimal('tax_amount', 12, 2)->default(0.00);
                $table->decimal('total_amount', 12, 2)->default(0.00);
                $table->boolean('cis_applicable')->default(false); // UK Construction Industry Scheme
                $table->decimal('cis_deduction_rate', 5, 2)->default(0.00); // typically 20% or 30% on labor
                $table->decimal('cis_deduction_amount', 12, 2)->default(0.00);
                $table->decimal('net_payable', 12, 2)->default(0.00);
                $table->string('status')->default('draft')->index(); // draft, sent, paid, overdue, void
                $table->string('payment_terms')->nullable()->default('30 Days Net');
                $table->text('notes')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->string('payment_reference')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('invoice_items')) {
            Schema::create('invoice_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('invoice_id')->index();
                $table->unsignedBigInteger('stock_item_id')->nullable();
                $table->string('description');
                $table->decimal('quantity', 10, 2)->default(1.00);
                $table->decimal('unit_price', 12, 2)->default(0.00);
                $table->decimal('tax_rate', 5, 2)->default(20.00);
                $table->decimal('total_price', 12, 2)->default(0.00);
                $table->boolean('is_labor')->default(false); // Used for CIS deduction calculations
                $table->timestamps();

                $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            });
        }

        // 3. Accounting Sync Logs (Xero / QuickBooks integration)
        if (!Schema::hasTable('accounting_sync_logs')) {
            Schema::create('accounting_sync_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('business_id')->nullable()->index();
                $table->string('syncable_type');
                $table->unsignedBigInteger('syncable_id');
                $table->string('provider')->index(); // 'xero', 'quickbooks'
                $table->string('action')->default('create'); // 'create', 'update', 'void'
                $table->string('status')->default('pending')->index(); // 'pending', 'synced', 'failed'
                $table->string('external_id')->nullable();
                $table->json('request_payload')->nullable();
                $table->json('response_payload')->nullable();
                $table->text('error_message')->nullable();
                $table->timestamp('synced_at')->nullable();
                $table->timestamps();

                $table->index(['syncable_type', 'syncable_id']);
            });
        }

        // 4. Field Engineer Mobile Visits, Safety RAMS & Site Photos (FR-022)
        if (!Schema::hasTable('job_site_visits')) {
            Schema::create('job_site_visits', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('business_id')->nullable()->index();
                $table->unsignedBigInteger('job_id')->index();
                $table->unsignedBigInteger('user_id')->index();
                $table->timestamp('arrived_at')->nullable();
                $table->timestamp('departed_at')->nullable();
                $table->decimal('latitude', 10, 7)->nullable();
                $table->decimal('longitude', 10, 7)->nullable();
                $table->string('status')->default('in_progress')->index(); // in_progress, completed
                $table->text('notes')->nullable();
                $table->longText('customer_signature')->nullable();
                $table->string('customer_signer_name')->nullable();
                $table->timestamp('signed_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('job_safety_checklists')) {
            Schema::create('job_safety_checklists', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('job_site_visit_id')->index();
                $table->unsignedBigInteger('job_id')->index();
                $table->unsignedBigInteger('user_id')->index();
                $table->boolean('working_at_height_passed')->default(true);
                $table->boolean('ppe_compliant')->default(true);
                $table->boolean('edge_protection_verified')->default(true);
                $table->boolean('weather_acceptable')->default(true);
                $table->text('risk_assessment_notes')->nullable();
                $table->string('signed_by_worker')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();

                $table->foreign('job_site_visit_id')->references('id')->on('job_site_visits')->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('job_site_photos')) {
            Schema::create('job_site_photos', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('job_site_visit_id')->nullable()->index();
                $table->unsignedBigInteger('job_id')->index();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('photo_path');
                $table->string('photo_name');
                $table->string('caption')->nullable();
                $table->string('stage')->default('during'); // before, during, after
                $table->decimal('latitude', 10, 7)->nullable();
                $table->decimal('longitude', 10, 7)->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_site_photos');
        Schema::dropIfExists('job_safety_checklists');
        Schema::dropIfExists('job_site_visits');
        Schema::dropIfExists('accounting_sync_logs');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');

        $tenantedTables = ['users', 'customers', 'enquiries', 'leads', 'quotations', 'fjobs', 'stock_items'];
        foreach ($tenantedTables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'business_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('business_id');
                });
            }
        }
    }
};
