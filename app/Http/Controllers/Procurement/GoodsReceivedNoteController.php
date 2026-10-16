<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\GoodsReceivedNote;
use App\Models\PurchaseOrder;
use App\Services\Procurement\ThreeWayMatchingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GoodsReceivedNoteController extends Controller
{
    public function __construct(
        protected ThreeWayMatchingService $matchingService
    ) {}

    public function index(Request $request): View
    {
        $query = GoodsReceivedNote::with(['purchaseOrder.supplier', 'job', 'receiver']);

        if ($request->filled('purchase_order_id')) {
            $query->where('purchase_order_id', $request->input('purchase_order_id'));
        }

        $grns = $query->latest('id')->paginate($this->getPerPage(15))->withQueryString();

        return view('procurement.grns.index', compact('grns'));
    }

    public function create(Request $request): View
    {
        $poId = $request->input('purchase_order_id');
        $purchaseOrder = PurchaseOrder::with(['supplier', 'items.stockItem'])->findOrFail($poId);

        return view('procurement.grns.create', compact('purchaseOrder'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'received_date' => 'required|date',
            'delivery_note_number' => 'nullable|string|max:100',
            'delivery_photo' => 'nullable|image|max:10240', // up to 10MB photo of delivery ticket
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.purchase_order_item_id' => 'required|exists:purchase_order_items,id',
            'items.*.quantity_received' => 'required|numeric|min:0',
            'items.*.condition' => 'required|in:good,damaged,shortage',
            'items.*.notes' => 'nullable|string',
        ]);

        $po = PurchaseOrder::findOrFail($validated['purchase_order_id']);
        $photo = $request->file('delivery_photo');

        $grn = $this->matchingService->receiveGoods(
            $po,
            $validated,
            $request->input('items', []),
            $photo,
            $request->user()
        );

        return redirect()->route('procurement.grns.show', $grn)
            ->with('success', 'Goods Received Note ' . $grn->grn_number . ' logged successfully.');
    }

    public function show(GoodsReceivedNote $grn): View
    {
        $grn->load(['purchaseOrder.supplier', 'job', 'receiver', 'items.purchaseOrderItem']);

        return view('procurement.grns.show', compact('grn'));
    }
}
