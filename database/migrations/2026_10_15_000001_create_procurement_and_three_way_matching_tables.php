<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for Milestone 1: Procurement & 3-Way Matching.
     */
    public function up(): void
    {
        // 1. Suppliers Directory
        if (!Schema::hasTable('suppliers')) {
            Schema::create('suppliers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('business_id')->nullable()->index();
                $table->string('name');
                $table->string('code')->nullable()->index();
                $table->string('contact_name')->nullable();
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->text('address')->nullable();
                $table->string('tax_number')->nullable(); // VAT / Tax ID
                $table->string('payment_terms')->default('30 Days Net');
                $table->string('status')->default('active')->index(); // active, inactive
                $table->timestamps();
            });
        }

        // 2. Purchase Orders (POs)
        if (!Schema::hasTable('purchase_orders')) {
            Schema::create('purchase_orders', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('business_id')->nullable()->index();
                $table->string('po_number')->unique();
                $table->unsignedBigInteger('supplier_id')->index();
                $table->unsignedBigInteger('job_id')->nullable()->index();
                $table->unsignedBigInteger('warehouse_id')->nullable()->index();
                $table->date('order_date');
                $table->date('expected_delivery_date')->nullable();
                $table->string('status')->default('draft')->index(); // draft, sent, partially_received, received, cancelled
                $table->decimal('subtotal', 12, 2)->default(0.00);
                $table->decimal('tax_rate', 5, 2)->default(20.00);
                $table->decimal('tax_amount', 12, 2)->default(0.00);
                $table->decimal('total_amount', 12, 2)->default(0.00);
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->foreign('supplier_id', 'fk_po_sup_id')->references('id')->on('suppliers')->onDelete('cascade');
            });
        }

        // 3. Purchase Order Items
        if (!Schema::hasTable('purchase_order_items')) {
            Schema::create('purchase_order_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('purchase_order_id')->index();
                $table->unsignedBigInteger('stock_item_id')->nullable()->index();
                $table->string('description');
                $table->decimal('quantity', 10, 2)->default(1.00);
                $table->decimal('received_quantity', 10, 2)->default(0.00);
                $table->decimal('unit_price', 12, 2)->default(0.00);
                $table->decimal('tax_rate', 5, 2)->default(20.00);
                $table->decimal('tax_amount', 12, 2)->default(0.00);
                $table->decimal('total_price', 12, 2)->default(0.00);
                $table->timestamps();

                $table->foreign('purchase_order_id', 'fk_poi_po_id')->references('id')->on('purchase_orders')->onDelete('cascade');
            });
        }

        // 4. Goods Received Notes (GRNs / Delivery Notes)
        if (!Schema::hasTable('goods_received_notes')) {
            Schema::create('goods_received_notes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('business_id')->nullable()->index();
                $table->string('grn_number')->unique();
                $table->unsignedBigInteger('purchase_order_id')->index();
                $table->unsignedBigInteger('job_id')->nullable()->index();
                $table->date('received_date');
                $table->string('delivery_note_number')->nullable();
                $table->unsignedBigInteger('received_by')->nullable();
                $table->string('delivery_photo_path')->nullable();
                $table->string('status')->default('received')->index(); // received, disputed
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->foreign('purchase_order_id', 'fk_grn_po_id')->references('id')->on('purchase_orders')->onDelete('cascade');
            });
        }

        // 5. Goods Received Note Items
        if (!Schema::hasTable('goods_received_note_items')) {
            Schema::create('goods_received_note_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('goods_received_note_id')->index();
                $table->unsignedBigInteger('purchase_order_item_id')->index();
                $table->decimal('quantity_received', 10, 2)->default(0.00);
                $table->string('condition')->default('good'); // good, damaged, shortage
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->foreign('goods_received_note_id', 'fk_grni_grn_id')->references('id')->on('goods_received_notes')->onDelete('cascade');
                $table->foreign('purchase_order_item_id', 'fk_grni_poi_id')->references('id')->on('purchase_order_items')->onDelete('cascade');
            });
        }

        // 6. Supplier Bills (Invoices received from suppliers for 3-way matching)
        if (!Schema::hasTable('supplier_bills')) {
            Schema::create('supplier_bills', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('business_id')->nullable()->index();
                $table->string('bill_number'); // Supplier's invoice number
                $table->string('internal_reference')->unique(); // e.g. BILL-2026-0001
                $table->unsignedBigInteger('purchase_order_id')->index();
                $table->unsignedBigInteger('supplier_id')->index();
                $table->unsignedBigInteger('job_id')->nullable()->index();
                $table->date('bill_date');
                $table->date('due_date');
                $table->decimal('subtotal', 12, 2)->default(0.00);
                $table->decimal('tax_amount', 12, 2)->default(0.00);
                $table->decimal('total_amount', 12, 2)->default(0.00);
                $table->string('match_status')->default('pending')->index(); // pending, matched, variance_detected, approved_override, rejected
                $table->string('approval_status')->default('pending')->index(); // pending, approved, held
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->dateTime('approved_at')->nullable();
                $table->text('variance_notes')->nullable();
                $table->timestamps();

                $table->foreign('purchase_order_id', 'fk_sb_po_id')->references('id')->on('purchase_orders')->onDelete('cascade');
                $table->foreign('supplier_id', 'fk_sb_sup_id')->references('id')->on('suppliers')->onDelete('cascade');
            });
        }

        // 7. Supplier Bill Items
        if (!Schema::hasTable('supplier_bill_items')) {
            Schema::create('supplier_bill_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('supplier_bill_id')->index();
                $table->unsignedBigInteger('purchase_order_item_id')->index();
                $table->string('description');
                $table->decimal('billed_quantity', 10, 2);
                $table->decimal('billed_unit_price', 12, 2);
                $table->decimal('billed_total', 12, 2);
                $table->decimal('po_quantity', 10, 2);
                $table->decimal('po_unit_price', 12, 2);
                $table->decimal('grn_received_quantity', 10, 2)->default(0.00);
                $table->decimal('quantity_variance', 10, 2)->default(0.00);
                $table->decimal('price_variance_amount', 12, 2)->default(0.00);
                $table->decimal('price_variance_percentage', 5, 2)->default(0.00);
                $table->string('variance_type')->default('none'); // none, qty_variance, price_variance, both
                $table->timestamps();

                $table->foreign('supplier_bill_id', 'fk_sbi_sb_id')->references('id')->on('supplier_bills')->onDelete('cascade');
                $table->foreign('purchase_order_item_id', 'fk_sbi_poi_id')->references('id')->on('purchase_order_items')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_bill_items');
        Schema::dropIfExists('supplier_bills');
        Schema::dropIfExists('goods_received_note_items');
        Schema::dropIfExists('goods_received_notes');
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('suppliers');
    }
};
