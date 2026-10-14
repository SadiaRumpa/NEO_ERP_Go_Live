<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplierBill extends Model
{
    use HasFactory, BelongsToBusiness;

    protected $fillable = [
        'business_id',
        'bill_number',
        'internal_reference',
        'purchase_order_id',
        'supplier_id',
        'job_id',
        'bill_date',
        'due_date',
        'subtotal',
        'tax_amount',
        'total_amount',
        'match_status',
        'approval_status',
        'approved_by',
        'approved_at',
        'variance_notes',
    ];

    protected $casts = [
        'bill_date' => 'date',
        'due_date' => 'date',
        'approved_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(Fjob::class, 'job_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SupplierBillItem::class);
    }
}
