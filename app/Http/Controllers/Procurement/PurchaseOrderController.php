<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Fjob;
use App\Models\PurchaseOrder;
use App\Models\StockItem;
use App\Models\StockWarehouse;
use App\Models\Supplier;
use App\Services\Procurement\ThreeWayMatchingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    public function __construct(
        protected ThreeWayMatchingService $matchingService
    ) {}

    public function index(Request $request): View
    {
        $query = PurchaseOrder::with(['supplier', 'job', 'warehouse']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->input('supplier_id'));
        }

        if ($request->filled('job_id')) {
            $query->where('job_id', $request->input('job_id'));
        }

        $purchaseOrders = $query->latest('id')->paginate($this->getPerPage(15))->withQueryString();
        $suppliers = Supplier::where('status', 'active')->orderBy('name')->get();

        return view('procurement.purchase_orders.index', compact('purchaseOrders', 'suppliers'));
    }

    public function create(): View
    {
        $suppliers = Supplier::where('status', 'active')->orderBy('name')->get();
        $jobs = Fjob::latest('id')->limit(50)->get();
        $warehouses = StockWarehouse::where('status', 1)->get();
        $stockItems = StockItem::all();

        return view('procurement.purchase_orders.create', compact('suppliers', 'jobs', 'warehouses', 'stockItems'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'job_id' => 'nullable|exists:fjobs,id',
            'warehouse_id' => 'nullable|exists:stock_warehouses,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after_or_equal:order_date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.stock_item_id' => 'nullable|exists:stock_items,id',
        ]);

        $po = $this->matchingService->createPurchaseOrder($validated, $request->input('items', []), $request->user());

        return redirect()->route('procurement.purchase-orders.show', $po)
            ->with('success', 'Purchase Order ' . $po->po_number . ' created successfully.');
    }

    public function show(PurchaseOrder $purchaseOrder): View
    {
        $purchaseOrder->load(['supplier', 'job', 'warehouse', 'creator', 'items.stockItem', 'grns.receiver', 'bills.supplier']);

        return view('procurement.purchase_orders.show', compact('purchaseOrder'));
    }

    public function edit(PurchaseOrder $purchaseOrder): View
    {
        if ($purchaseOrder->status !== 'draft') {
            abort(403, 'Only draft purchase orders can be edited.');
        }

        $suppliers = Supplier::where('status', 'active')->orderBy('name')->get();
        $jobs = Fjob::latest('id')->limit(50)->get();
        $warehouses = StockWarehouse::where('is_active', true)->get();
        $stockItems = StockItem::all();

        return view('procurement.purchase_orders.create', [
            'purchaseOrder' => $purchaseOrder,
            'suppliers' => $suppliers,
            'jobs' => $jobs,
            'warehouses' => $warehouses,
            'stockItems' => $stockItems,
            'isEdit' => true,
        ]);
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        if ($purchaseOrder->status !== 'draft') {
            return redirect()->route('procurement.purchase-orders.show', $purchaseOrder)
                ->with('error', 'Only draft purchase orders can be edited.');
        }

        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'job_id' => 'nullable|exists:fjobs,id',
            'warehouse_id' => 'nullable|exists:stock_warehouses,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after_or_equal:order_date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.stock_item_id' => 'nullable|exists:stock_items,id',
        ]);

        $purchaseOrder->update([
            'supplier_id' => $validated['supplier_id'],
            'job_id' => $validated['job_id'] ?? null,
            'warehouse_id' => $validated['warehouse_id'] ?? null,
            'order_date' => $validated['order_date'],
            'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        $purchaseOrder->items()->delete();
        $subtotal = 0;
        $taxTotal = 0;

        foreach ($request->input('items', []) as $itemData) {
            $qty = (float)($itemData['quantity'] ?? 1);
            $unitPrice = (float)($itemData['unit_price'] ?? 0);
            $taxRate = (float)($purchaseOrder->tax_rate ?? 20.00);

            $lineTotal = round($qty * $unitPrice, 2);
            $lineTax = round($lineTotal * ($taxRate / 100), 2);

            $purchaseOrder->items()->create([
                'stock_item_id' => $itemData['stock_item_id'] ?? null,
                'description' => $itemData['description'] ?? 'Procurement Item',
                'quantity' => $qty,
                'received_quantity' => 0.00,
                'unit_price' => $unitPrice,
                'tax_rate' => $taxRate,
                'tax_amount' => $lineTax,
                'total_price' => $lineTotal,
            ]);

            $subtotal += $lineTotal;
            $taxTotal += $lineTax;
        }

        $purchaseOrder->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxTotal,
            'total_amount' => $subtotal + $taxTotal,
        ]);

        return redirect()->route('procurement.purchase-orders.show', $purchaseOrder)
            ->with('success', 'Purchase Order updated successfully.');
    }

    public function send(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        if ($purchaseOrder->status === 'draft') {
            $purchaseOrder->update(['status' => 'sent']);
        }

        return redirect()->route('procurement.purchase-orders.show', $purchaseOrder)
            ->with('success', 'Purchase Order ' . $purchaseOrder->po_number . ' marked as Sent to supplier.');
    }

    public function cancel(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        if (in_array($purchaseOrder->status, ['draft', 'sent'])) {
            $purchaseOrder->update(['status' => 'cancelled']);
        }

        return redirect()->route('procurement.purchase-orders.show', $purchaseOrder)
            ->with('success', 'Purchase Order ' . $purchaseOrder->po_number . ' has been cancelled.');
    }
}
