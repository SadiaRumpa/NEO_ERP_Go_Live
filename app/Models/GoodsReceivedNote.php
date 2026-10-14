<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GoodsReceivedNote extends Model
{
    use HasFactory, BelongsToBusiness;

    protected $fillable = [
        'business_id',
        'grn_number',
        'purchase_order_id',
        'job_id',
        'received_date',
        'delivery_note_number',
        'received_by',
        'delivery_photo_path',
        'status',
        'notes',
    ];

    protected $casts = [
        'received_date' => 'date',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(Fjob::class, 'job_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(GoodsReceivedNoteItem::class);
    }
}
