<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\SupplierBill;
use App\Services\Procurement\ThreeWayMatchingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierBillController extends Controller
{
    public function __construct(
        protected ThreeWayMatchingService $matchingService
    ) {}

    public function index(Request $request): View
    {
        $query = SupplierBill::with(['supplier', 'purchaseOrder', 'job', 'approver']);

        if ($request->filled('match_status')) {
            $query->where('match_status', $request->input('match_status'));
        }

        if ($request->filled('approval_status')) {
            $query->where('approval_status', $request->input('approval_status'));
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->input('supplier_id'));
        }

        $bills = $query->latest('id')->paginate(15);

        return view('procurement.supplier_bills.index', compact('bills'));
    }

    public function create(Request $request): View
    {
        $poId = $request->input('purchase_order_id');
        $purchaseOrder = PurchaseOrder::with(['supplier', 'items.stockItem'])->findOrFail($poId);

        return view('procurement.supplier_bills.create', compact('purchaseOrder'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'bill_number' => 'required|string|max:100',
            'bill_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:bill_date',
            'items' => 'required|array|min:1',
            'items.*.purchase_order_item_id' => 'required|exists:purchase_order_items,id',
            'items.*.billed_quantity' => 'required|numeric|min:0.01',
            'items.*.billed_unit_price' => 'required|numeric|min:0',
            'items.*.description' => 'nullable|string|max:255',
        ]);

        $po = PurchaseOrder::findOrFail($validated['purchase_order_id']);

        $bill = $this->matchingService->createSupplierBill(
            $po,
            $validated,
            $request->input('items', [])
        );

        $flashType = $bill->match_status === 'matched' ? 'success' : 'warning';
        $message = $bill->match_status === 'matched'
            ? 'Supplier Bill registered and 3-Way Match verified cleanly!'
            : 'Supplier Bill registered. Commercial variances were detected and this bill has been placed on HOLD.';

        return redirect()->route('procurement.supplier-bills.show', $bill)
            ->with($flashType, $message);
    }

    public function show(SupplierBill $supplierBill): View
    {
        $supplierBill->load(['supplier', 'purchaseOrder.grns', 'job', 'approver', 'items.purchaseOrderItem']);

        return view('procurement.supplier_bills.show', compact('supplierBill'));
    }

    public function override(Request $request, SupplierBill $supplierBill): RedirectResponse
    {
        $validated = $request->validate([
            'justification' => 'required|string|min:5|max:1000',
        ]);

        $this->matchingService->overrideApproval(
            $supplierBill,
            $request->user(),
            $validated['justification']
        );

        return redirect()->route('procurement.supplier-bills.show', $supplierBill)
            ->with('success', 'Commercial Manager override applied. Bill approved for payment.');
    }
}
