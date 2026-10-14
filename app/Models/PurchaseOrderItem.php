<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'stock_item_id',
        'description',
        'quantity',
        'received_quantity',
        'unit_price',
        'tax_rate',
        'tax_amount',
        'total_price',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'received_quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function stockItem(): BelongsTo
    {
        return $this->belongsTo(StockItem::class);
    }

    public function grnItems(): HasMany
    {
        return $this->hasMany(GoodsReceivedNoteItem::class);
    }

    public function billItems(): HasMany
    {
        return $this->hasMany(SupplierBillItem::class);
    }

    public function getRemainingQuantityAttribute(): float
    {
        return max(0, (float)$this->quantity - (float)$this->received_quantity);
    }
}
