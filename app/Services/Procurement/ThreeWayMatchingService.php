<?php

namespace App\Services\Procurement;

use App\Models\GoodsReceivedNote;
use App\Models\GoodsReceivedNoteItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockItemMovement;
use App\Models\StockItemVariant;
use App\Models\SupplierBill;
use App\Models\SupplierBillItem;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ThreeWayMatchingService
{
    /**
     * Create a new Purchase Order with items and recalculate totals.
     */
    public function createPurchaseOrder(array $data, array $items, ?User $user = null): PurchaseOrder
    {
        return DB::transaction(function () use ($data, $items, $user) {
            $year = date('Y');
            $count = PurchaseOrder::whereYear('created_at', $year)->count() + 1;
            $poNumber = 'PO-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

            $po = PurchaseOrder::create([
                'business_id' => $data['business_id'] ?? null,
                'po_number' => $data['po_number'] ?? $poNumber,
                'supplier_id' => $data['supplier_id'],
                'job_id' => $data['job_id'] ?? null,
                'warehouse_id' => $data['warehouse_id'] ?? null,
                'order_date' => $data['order_date'] ?? now()->toDateString(),
                'expected_delivery_date' => $data['expected_delivery_date'] ?? null,
                'status' => 'draft',
                'tax_rate' => $data['tax_rate'] ?? 20.00,
                'notes' => $data['notes'] ?? null,
                'created_by' => $user?->id ?? auth()->id(),
            ]);

            $subtotal = 0;
            $taxTotal = 0;

            foreach ($items as $itemData) {
                $qty = (float)($itemData['quantity'] ?? 1);
                $unitPrice = (float)($itemData['unit_price'] ?? 0);
                $taxRate = (float)($itemData['tax_rate'] ?? $po->tax_rate ?? 20.00);

                $lineTotal = round($qty * $unitPrice, 2);
                $lineTax = round($lineTotal * ($taxRate / 100), 2);

                $po->items()->create([
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

            $po->update([
                'subtotal' => $subtotal,
                'tax_amount' => $taxTotal,
                'total_amount' => $subtotal + $taxTotal,
            ]);

            return $po->fresh(['items', 'supplier']);
        });
    }

    /**
     * Record receipt of goods against a Purchase Order.
     */
    public function receiveGoods(PurchaseOrder $po, array $data, array $itemsData, ?UploadedFile $photo = null, ?User $user = null): GoodsReceivedNote
    {
        return DB::transaction(function () use ($po, $data, $itemsData, $photo, $user) {
            $year = date('Y');
            $count = GoodsReceivedNote::whereYear('created_at', $year)->count() + 1;
            $grnNumber = 'GRN-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

            $photoPath = null;
            if ($photo) {
                $photoPath = $photo->store('grn_delivery_notes', 'public');
            }

            $grn = GoodsReceivedNote::create([
                'business_id' => $po->business_id,
                'grn_number' => $data['grn_number'] ?? $grnNumber,
                'purchase_order_id' => $po->id,
                'job_id' => $po->job_id,
                'received_date' => $data['received_date'] ?? now()->toDateString(),
                'delivery_note_number' => $data['delivery_note_number'] ?? null,
                'received_by' => $user?->id ?? auth()->id(),
                'delivery_photo_path' => $photoPath,
                'status' => $data['status'] ?? 'received',
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($itemsData as $itemInput) {
                $poiId = $itemInput['purchase_order_item_id'];
                $qtyReceived = (float)($itemInput['quantity_received'] ?? 0);

                if ($qtyReceived <= 0) {
                    continue;
                }

                $grn->items()->create([
                    'purchase_order_item_id' => $poiId,
                    'quantity_received' => $qtyReceived,
                    'condition' => $itemInput['condition'] ?? 'good',
                    'notes' => $itemInput['notes'] ?? null,
                ]);

                // Update PO Item cumulative received quantity
                $poItem = PurchaseOrderItem::find($poiId);
                if ($poItem) {
                    $poItem->increment('received_quantity', $qtyReceived);

                    // Integrate with Stock Movement if warehouse or stock item is configured
                    if ($po->warehouse_id && $poItem->stock_item_id) {
                        $variant = StockItemVariant::where('stock_item_id', $poItem->stock_item_id)->first();
                        StockItemMovement::create([
                            'item_variant_id' => $variant?->id ?? 1,
                            'quantity' => $qtyReceived,
                            'unit' => 'Pcs',
                            'cost' => $poItem->unit_price,
                            'movement_type' => 'in',
                            'movement_date' => $grn->received_date,
                            'user_id' => $grn->received_by,
                            'reference' => $grn->grn_number,
                            'destination_warehouse_id' => $po->warehouse_id,
                            'destination' => 'Warehouse Purchase Receipt',
                            'status' => 'completed',
                            'reason' => 'GRN Receipt: ' . $grn->grn_number . ' for PO: ' . $po->po_number,
                        ]);
                    }
                }
            }

            // Update PO status (partially_received vs received)
            $po->updateReceiptStatus();

            return $grn->fresh(['items', 'purchaseOrder']);
        });
    }

    /**
     * Create a Supplier Bill and automatically execute 3-Way Reconciliation.
     */
    public function createSupplierBill(PurchaseOrder $po, array $data, array $itemsData): SupplierBill
    {
        return DB::transaction(function () use ($po, $data, $itemsData) {
            $year = date('Y');
            $count = SupplierBill::whereYear('created_at', $year)->count() + 1;
            $internalRef = 'BILL-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

            $bill = SupplierBill::create([
                'business_id' => $po->business_id,
                'bill_number' => $data['bill_number'],
                'internal_reference' => $data['internal_reference'] ?? $internalRef,
                'purchase_order_id' => $po->id,
                'supplier_id' => $po->supplier_id,
                'job_id' => $po->job_id,
                'bill_date' => $data['bill_date'] ?? now()->toDateString(),
                'due_date' => $data['due_date'] ?? now()->addDays(30)->toDateString(),
                'subtotal' => 0.00,
                'tax_amount' => 0.00,
                'total_amount' => 0.00,
                'match_status' => 'pending',
                'approval_status' => 'pending',
            ]);

            $subtotal = 0;

            foreach ($itemsData as $itemInput) {
                $poItem = PurchaseOrderItem::findOrFail($itemInput['purchase_order_item_id']);
                $billedQty = (float)($itemInput['billed_quantity'] ?? $poItem->quantity);
                $billedPrice = (float)($itemInput['billed_unit_price'] ?? $poItem->unit_price);
                $billedTotal = round($billedQty * $billedPrice, 2);

                // Calculate cumulative GRN quantity for this specific PO item
                $grnQty = (float)GoodsReceivedNoteItem::where('purchase_order_item_id', $poItem->id)->sum('quantity_received');

                $qtyVariance = $billedQty - $grnQty;
                $priceVariance = $billedPrice - (float)$poItem->unit_price;
                $priceVariancePct = (float)$poItem->unit_price > 0 ? round(($priceVariance / (float)$poItem->unit_price) * 100, 2) : 0.00;

                $bill->items()->create([
                    'purchase_order_item_id' => $poItem->id,
                    'description' => $itemInput['description'] ?? $poItem->description,
                    'billed_quantity' => $billedQty,
                    'billed_unit_price' => $billedPrice,
                    'billed_total' => $billedTotal,
                    'po_quantity' => $poItem->quantity,
                    'po_unit_price' => $poItem->unit_price,
                    'grn_received_quantity' => $grnQty,
                    'quantity_variance' => $qtyVariance,
                    'price_variance_amount' => $priceVariance,
                    'price_variance_percentage' => $priceVariancePct,
                    'variance_type' => 'none',
                ]);

                $subtotal += $billedTotal;
            }

            $taxAmount = round($subtotal * 0.20, 2);
            $total = $subtotal + $taxAmount;

            $bill->update([
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $total,
            ]);

            // Execute 3-Way Match engine with standard 5% tolerance
            $this->runMatch($bill, 5.0);

            return $bill->fresh(['items', 'purchaseOrder', 'supplier']);
        });
    }

    /**
     * Execute 3-Way Match Algorithm comparing PO vs. GRN vs. Bill.
     */
    public function runMatch(SupplierBill $bill, float $tolerancePercent = 5.0): array
    {
        $hasVariance = false;
        $varianceMessages = [];

        foreach ($bill->items as $item) {
            $isQtyVariance = false;
            $isPriceVariance = false;

            // 1. Quantity Check: Billed Quantity cannot exceed actual Goods Received quantity
            if ($item->billed_quantity > $item->grn_received_quantity) {
                $isQtyVariance = true;
                $hasVariance = true;
                $diff = $item->billed_quantity - $item->grn_received_quantity;
                $varianceMessages[] = "Quantity Mismatch for '{$item->description}': Billed {$item->billed_quantity} exceeds received quantity of {$item->grn_received_quantity} (Difference: +{$diff}).";
            }

            // 2. Unit Price Check: Billed Unit Price cannot exceed PO price by more than tolerance %
            if ($item->price_variance_percentage > $tolerancePercent) {
                $isPriceVariance = true;
                $hasVariance = true;
                $varianceMessages[] = "Price Tolerance Exceeded for '{$item->description}': Billed at £{$item->billed_unit_price} vs agreed PO price £{$item->po_unit_price} (+{$item->price_variance_percentage}% increase exceeds {$tolerancePercent}% tolerance).";
            }

            // Determine line variance classification
            if ($isQtyVariance && $isPriceVariance) {
                $item->update(['variance_type' => 'both']);
            } elseif ($isQtyVariance) {
                $item->update(['variance_type' => 'qty_variance']);
            } elseif ($isPriceVariance) {
                $item->update(['variance_type' => 'price_variance']);
            } else {
                $item->update(['variance_type' => 'none']);
            }
        }

        if ($hasVariance) {
            $bill->update([
                'match_status' => 'variance_detected',
                'approval_status' => 'held',
                'variance_notes' => implode("\n", $varianceMessages),
            ]);
        } else {
            $bill->update([
                'match_status' => 'matched',
                'approval_status' => 'approved',
                'approved_at' => now(),
                'variance_notes' => "3-Way Match Verified: Billed quantities match verified GRN receipts, and unit prices comply with agreed PO rates (<= {$tolerancePercent}% tolerance).",
            ]);
        }

        return [
            'bill_id' => $bill->id,
            'match_status' => $bill->match_status,
            'approval_status' => $bill->approval_status,
            'has_variance' => $hasVariance,
            'messages' => $varianceMessages,
        ];
    }

    /**
     * Commercial Manager override approval for bills held due to variance.
     */
    public function overrideApproval(SupplierBill $bill, User $approver, string $justification): SupplierBill
    {
        $existingNotes = $bill->variance_notes ?? '';
        $overrideAudit = "\n[COMMERCIAL OVERRIDE - " . now()->toDateTimeString() . ' by ' . $approver->name . "]: " . $justification;

        $bill->update([
            'match_status' => 'approved_override',
            'approval_status' => 'approved',
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'variance_notes' => trim($existingNotes . $overrideAudit),
        ]);

        return $bill->fresh();
    }
}
