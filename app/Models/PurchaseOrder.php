<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    use HasFactory, BelongsToBusiness;

    protected $fillable = [
        'business_id',
        'po_number',
        'supplier_id',
        'job_id',
        'warehouse_id',
        'order_date',
        'expected_delivery_date',
        'status',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'total_amount',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(Fjob::class, 'job_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(StockWarehouse::class, 'warehouse_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function grns(): HasMany
    {
        return $this->hasMany(GoodsReceivedNote::class);
    }

    public function bills(): HasMany
    {
        return $this->hasMany(SupplierBill::class);
    }

    /**
     * Recalculate totals from items.
     */
    public function recalculateTotals(): void
    {
        $subtotal = $this->items()->sum('total_price');
        $taxAmount = $this->items()->sum('tax_amount');
        $total = $subtotal + $taxAmount;

        $this->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $total,
        ]);
    }

    /**
     * Check if all ordered items have been fully received.
     */
    public function updateReceiptStatus(): void
    {
        $items = $this->items()->get();
        if ($items->isEmpty()) {
            return;
        }

        $allReceived = true;
        $anyReceived = false;

        foreach ($items as $item) {
            if ($item->received_quantity >= $item->quantity) {
                $anyReceived = true;
            } elseif ($item->received_quantity > 0) {
                $anyReceived = true;
                $allReceived = false;
            } else {
                $allReceived = false;
            }
        }

        if ($allReceived) {
            $this->update(['status' => 'received']);
        } elseif ($anyReceived) {
            $this->update(['status' => 'partially_received']);
        }
    }
}
