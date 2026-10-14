<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoodsReceivedNoteItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'goods_received_note_id',
        'purchase_order_item_id',
        'quantity_received',
        'condition',
        'notes',
    ];

    protected $casts = [
        'quantity_received' => 'decimal:2',
    ];

    public function goodsReceivedNote(): BelongsTo
    {
        return $this->belongsTo(GoodsReceivedNote::class);
    }

    public function purchaseOrderItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }
}
