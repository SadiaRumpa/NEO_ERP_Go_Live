<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for Milestone 3: Plant & Equipment Hire Register.
     */
    public function up(): void
    {
        if (!Schema::hasTable('plant_hires')) {
            Schema::create('plant_hires', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('business_id')->nullable()->index();
                $table->string('hire_reference')->unique();
                $table->unsignedBigInteger('job_id')->index();
                $table->unsignedBigInteger('supplier_id')->index();
                $table->string('item_name');
                $table->string('category')->default('tools')->index(); // scaffolding, mewp, telehandler, skips, tools, other
                $table->string('serial_asset_tag')->nullable();
                $table->date('start_date');
                $table->date('expected_end_date');
                $table->date('actual_end_date')->nullable();
                $table->decimal('daily_rate', 10, 2)->default(0.00);
                $table->decimal('weekly_rate', 10, 2)->default(0.00);
                $table->decimal('delivery_charge', 10, 2)->default(0.00);
                $table->decimal('collection_charge', 10, 2)->default(0.00);
                $table->string('status')->default('active')->index(); // active, pending_off_hire, off_hired, cancelled
                $table->string('off_hire_reference')->nullable();
                $table->dateTime('off_hired_at')->nullable();
                $table->unsignedBigInteger('off_hired_by')->nullable();
                $table->decimal('accrued_hire_cost', 12, 2)->default(0.00);
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->foreign('job_id', 'fk_ph_job_id')->references('id')->on('fjobs')->onDelete('cascade');
                $table->foreign('supplier_id', 'fk_ph_sup_id')->references('id')->on('suppliers')->onDelete('cascade');
                $table->foreign('off_hired_by', 'fk_ph_usr_id')->references('id')->on('users')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plant_hires');
    }
};
