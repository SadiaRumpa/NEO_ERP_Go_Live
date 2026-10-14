<?php

namespace Tests\Feature;

use App\Models\BusinessInfo;
use App\Models\Fjob;
use App\Models\GoodsReceivedNote;
use App\Models\PurchaseOrder;
use App\Models\StockItem;
use App\Models\StockItemMovement;
use App\Models\StockItemVariant;
use App\Models\StockWarehouse;
use App\Models\Supplier;
use App\Models\SupplierBill;
use App\Models\User;
use App\Services\Procurement\ThreeWayMatchingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProcurementThreeWayMatchingTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected BusinessInfo $business;
    protected Supplier $supplier;
    protected ThreeWayMatchingService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::firstOrCreate(['name' => 'super_admin']);
        $this->admin = User::factory()->create([
            'email' => 'commercial@neoerp.local',
        ]);
        $this->admin->assignRole($role);

        $this->business = BusinessInfo::firstOrCreate(
            ['business_name' => 'Neoteric Construction Ltd'],
            ['business_status' => 'Active']
        );

        $this->admin->update(['business_id' => $this->business->id]);

        $this->supplier = Supplier::create([
            'business_id' => $this->business->id,
            'name' => 'Marley Roofing Supplies Ltd',
            'code' => 'SUP-MARLEY-01',
            'contact_name' => 'John Trade',
            'email' => 'sales@marleyroofing.co.uk',
            'payment_terms' => '30 Days Net',
            'status' => 'active',
        ]);

        $this->service = app(ThreeWayMatchingService::class);
    }

    public function test_can_create_supplier_and_purchase_order_with_calculated_totals(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('procurement.purchase-orders.store'), [
            'supplier_id' => $this->supplier->id,
            'order_date' => now()->toDateString(),
            'expected_delivery_date' => now()->addDays(5)->toDateString(),
            'notes' => 'Deliver to front drive',
            'items' => [
                [
                    'description' => 'Modern Roof Tiles (Anthracite)',
                    'quantity' => 500,
                    'unit_price' => 1.50, // 500 * 1.50 = 750
                ],
                [
                    'description' => 'Dry Ridge Vent Kit 6m',
                    'quantity' => 10,
                    'unit_price' => 45.00, // 10 * 45 = 450
                ],
            ],
        ]);

        $response->assertRedirect();

        $po = PurchaseOrder::latest('id')->first();
        $this->assertNotNull($po);
        $this->assertEquals('draft', $po->status);
        $this->assertEquals(1200.00, (float)$po->subtotal); // 750 + 450
        $this->assertEquals(240.00, (float)$po->tax_amount); // 20% of 1200
        $this->assertEquals(1440.00, (float)$po->total_amount); // 1200 + 240
        $this->assertCount(2, $po->items);
    }

    public function test_purchase_order_lifecycle_draft_to_sent(): void
    {
        $this->actingAs($this->admin);

        $po = $this->service->createPurchaseOrder([
            'business_id' => $this->business->id,
            'supplier_id' => $this->supplier->id,
            'order_date' => now()->toDateString(),
        ], [
            ['description' => 'Breather Membrane 50m Roll', 'quantity' => 5, 'unit_price' => 60.00]
        ], $this->admin);

        $this->assertEquals('draft', $po->status);

        $response = $this->post(route('procurement.purchase-orders.send', $po));
        $response->assertRedirect();

        $this->assertEquals('sent', $po->fresh()->status);
    }

    public function test_goods_received_note_partial_receipt_updates_po_status(): void
    {
        $this->actingAs($this->admin);

        $po = $this->service->createPurchaseOrder([
            'business_id' => $this->business->id,
            'supplier_id' => $this->supplier->id,
            'order_date' => now()->toDateString(),
        ], [
            ['description' => 'Timber Battens 25x50mm 4.8m', 'quantity' => 100, 'unit_price' => 3.20]
        ], $this->admin);

        $po->update(['status' => 'sent']);
        $item = $po->items->first();

        // Log partial receipt of 40 out of 100
        $grn = $this->service->receiveGoods($po, [
            'received_date' => now()->toDateString(),
            'delivery_note_number' => 'DN-4401',
        ], [
            [
                'purchase_order_item_id' => $item->id,
                'quantity_received' => 40,
                'condition' => 'good',
            ]
        ], null, $this->admin);

        $this->assertNotNull($grn);
        $this->assertEquals(40.00, (float)$item->fresh()->received_quantity);
        $this->assertEquals('partially_received', $po->fresh()->status);
    }

    public function test_goods_received_note_full_receipt_marks_po_received(): void
    {
        $this->actingAs($this->admin);

        $po = $this->service->createPurchaseOrder([
            'business_id' => $this->business->id,
            'supplier_id' => $this->supplier->id,
            'order_date' => now()->toDateString(),
        ], [
            ['description' => 'Lead Flashing Code 4 6m', 'quantity' => 4, 'unit_price' => 120.00]
        ], $this->admin);

        $item = $po->items->first();

        // Deliver full quantity
        $this->service->receiveGoods($po, [
            'received_date' => now()->toDateString(),
            'delivery_note_number' => 'DN-9921',
        ], [
            [
                'purchase_order_item_id' => $item->id,
                'quantity_received' => 4,
                'condition' => 'good',
            ]
        ], null, $this->admin);

        $this->assertEquals(4.00, (float)$item->fresh()->received_quantity);
        $this->assertEquals('received', $po->fresh()->status);
    }

    public function test_three_way_matching_happy_path_approves_bill(): void
    {
        $this->actingAs($this->admin);

        $po = $this->service->createPurchaseOrder([
            'business_id' => $this->business->id,
            'supplier_id' => $this->supplier->id,
            'order_date' => now()->toDateString(),
        ], [
            ['description' => 'Fascia Board 5m White', 'quantity' => 20, 'unit_price' => 18.50]
        ], $this->admin);

        $item = $po->items->first();

        // 1. Physically receive 20
        $this->service->receiveGoods($po, [
            'received_date' => now()->toDateString(),
            'delivery_note_number' => 'DEL-101',
        ], [
            ['purchase_order_item_id' => $item->id, 'quantity_received' => 20]
        ], null, $this->admin);

        // 2. Supplier bills exactly 20 @ £18.50
        $bill = $this->service->createSupplierBill($po, [
            'bill_number' => 'INV-2026-001',
            'bill_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
        ], [
            [
                'purchase_order_item_id' => $item->id,
                'billed_quantity' => 20,
                'billed_unit_price' => 18.50,
            ]
        ]);

        $this->assertEquals('matched', $bill->match_status);
        $this->assertEquals('approved', $bill->approval_status);
        $this->assertEquals('none', $bill->items->first()->variance_type);
    }

    public function test_three_way_matching_detects_price_variance_over_tolerance_and_holds_bill(): void
    {
        $this->actingAs($this->admin);

        $po = $this->service->createPurchaseOrder([
            'business_id' => $this->business->id,
            'supplier_id' => $this->supplier->id,
            'order_date' => now()->toDateString(),
        ], [
            ['description' => 'Galvanized Nails 50mm 1kg', 'quantity' => 50, 'unit_price' => 10.00]
        ], $this->admin);

        $item = $po->items->first();

        // Receive all 50
        $this->service->receiveGoods($po, [
            'received_date' => now()->toDateString(),
        ], [
            ['purchase_order_item_id' => $item->id, 'quantity_received' => 50]
        ], null, $this->admin);

        // Supplier bills at £12.00 (20% price increase, exceeds 5% tolerance)
        $bill = $this->service->createSupplierBill($po, [
            'bill_number' => 'INV-OVERPRICE-01',
            'bill_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
        ], [
            [
                'purchase_order_item_id' => $item->id,
                'billed_quantity' => 50,
                'billed_unit_price' => 12.00, // +20%
            ]
        ]);

        $this->assertEquals('variance_detected', $bill->match_status);
        $this->assertEquals('held', $bill->approval_status);
        $this->assertEquals('price_variance', $bill->items->first()->variance_type);
        $this->assertStringContainsString('Price Tolerance Exceeded', $bill->variance_notes);
    }

    public function test_three_way_matching_detects_quantity_variance_when_billed_exceeds_received(): void
    {
        $this->actingAs($this->admin);

        $po = $this->service->createPurchaseOrder([
            'business_id' => $this->business->id,
            'supplier_id' => $this->supplier->id,
            'order_date' => now()->toDateString(),
        ], [
            ['description' => 'Soffit Board 5m', 'quantity' => 30, 'unit_price' => 15.00]
        ], $this->admin);

        $item = $po->items->first();

        // Only 10 physically delivered on site
        $this->service->receiveGoods($po, [
            'received_date' => now()->toDateString(),
        ], [
            ['purchase_order_item_id' => $item->id, 'quantity_received' => 10]
        ], null, $this->admin);

        // Supplier attempts to invoice for all 30
        $bill = $this->service->createSupplierBill($po, [
            'bill_number' => 'INV-OVERBILL-01',
            'bill_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
        ], [
            [
                'purchase_order_item_id' => $item->id,
                'billed_quantity' => 30, // 30 billed vs 10 received
                'billed_unit_price' => 15.00,
            ]
        ]);

        $this->assertEquals('variance_detected', $bill->match_status);
        $this->assertEquals('held', $bill->approval_status);
        $this->assertEquals('qty_variance', $bill->items->first()->variance_type);
        $this->assertEquals(20.00, (float)$bill->items->first()->quantity_variance);
        $this->assertStringContainsString('Quantity Mismatch', $bill->variance_notes);
    }

    public function test_commercial_manager_can_override_variance_hold(): void
    {
        $this->actingAs($this->admin);

        $po = $this->service->createPurchaseOrder([
            'business_id' => $this->business->id,
            'supplier_id' => $this->supplier->id,
            'order_date' => now()->toDateString(),
        ], [
            ['description' => 'Emergency Slate Tiles', 'quantity' => 100, 'unit_price' => 2.00]
        ], $this->admin);

        $item = $po->items->first();

        $this->service->receiveGoods($po, [
            'received_date' => now()->toDateString(),
        ], [
            ['purchase_order_item_id' => $item->id, 'quantity_received' => 100]
        ], null, $this->admin);

        $bill = $this->service->createSupplierBill($po, [
            'bill_number' => 'INV-SURCHARGE',
            'bill_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
        ], [
            [
                'purchase_order_item_id' => $item->id,
                'billed_quantity' => 100,
                'billed_unit_price' => 2.50, // +25% emergency delivery surcharge
            ]
        ]);

        $this->assertEquals('held', $bill->approval_status);

        // Apply Commercial Override via controller endpoint
        $response = $this->post(route('procurement.supplier-bills.override', $bill), [
            'justification' => 'Authorized by Commercial Director: Weekend expedited freight surcharge confirmed against client variation #2.',
        ]);

        $response->assertRedirect();

        $updatedBill = $bill->fresh();
        $this->assertEquals('approved_override', $updatedBill->match_status);
        $this->assertEquals('approved', $updatedBill->approval_status);
        $this->assertEquals($this->admin->id, $updatedBill->approved_by);
        $this->assertNotNull($updatedBill->approved_at);
        $this->assertStringContainsString('Weekend expedited freight surcharge', $updatedBill->variance_notes);
    }

    public function test_grn_receipt_integrates_with_stock_movement_when_warehouse_specified(): void
    {
        $this->actingAs($this->admin);

        $warehouse = StockWarehouse::create([
            'name' => 'Main Depot',
            'is_active' => true,
        ]);

        $category = \App\Models\StockCategory::create([
            'name' => 'Sealants',
            'is_active' => true,
        ]);

        $stockItem = StockItem::create([
            'name' => 'Silicone Sealant Clear 310ml',
            'category_id' => $category->id,
            'is_active' => true,
        ]);

        $variant = StockItemVariant::create([
            'stock_item_id' => $stockItem->id,
            'name' => 'Standard Clear',
            'sku' => 'SIL-CLR-310-STD',
            'warehouse_id' => $warehouse->id,
            'is_active' => true,
        ]);

        $po = $this->service->createPurchaseOrder([
            'business_id' => $this->business->id,
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $warehouse->id,
            'order_date' => now()->toDateString(),
        ], [
            [
                'stock_item_id' => $stockItem->id,
                'description' => 'Silicone Sealant Clear 310ml',
                'quantity' => 24,
                'unit_price' => 4.50,
            ]
        ], $this->admin);

        $item = $po->items->first();

        // Receive goods into warehouse
        $this->service->receiveGoods($po, [
            'received_date' => now()->toDateString(),
            'delivery_note_number' => 'DEL-DEPOT-1',
        ], [
            [
                'purchase_order_item_id' => $item->id,
                'quantity_received' => 24,
            ]
        ], null, $this->admin);

        // Verify stock movement was automatically recorded
        $movement = StockItemMovement::where('destination_warehouse_id', $warehouse->id)->latest('id')->first();
        $this->assertNotNull($movement);
        $this->assertEquals(24.00, (float)$movement->quantity);
        $this->assertEquals('in', $movement->movement_type);
        $this->assertEquals('completed', $movement->status);
    }
}
