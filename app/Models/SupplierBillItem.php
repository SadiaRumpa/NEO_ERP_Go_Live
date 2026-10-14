<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierBillItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_bill_id',
        'purchase_order_item_id',
        'description',
        'billed_quantity',
        'billed_unit_price',
        'billed_total',
        'po_quantity',
        'po_unit_price',
        'grn_received_quantity',
        'quantity_variance',
        'price_variance_amount',
        'price_variance_percentage',
        'variance_type',
    ];

    protected $casts = [
        'billed_quantity' => 'decimal:2',
        'billed_unit_price' => 'decimal:2',
        'billed_total' => 'decimal:2',
        'po_quantity' => 'decimal:2',
        'po_unit_price' => 'decimal:2',
        'grn_received_quantity' => 'decimal:2',
        'quantity_variance' => 'decimal:2',
        'price_variance_amount' => 'decimal:2',
        'price_variance_percentage' => 'decimal:2',
    ];

    public function supplierBill(): BelongsTo
    {
        return $this->belongsTo(SupplierBill::class);
    }

    public function purchaseOrderItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }
}
