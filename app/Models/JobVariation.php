<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobVariation extends Model
{
    use HasFactory, BelongsToBusiness;

    protected $fillable = [
        'business_id',
        'variation_number',
        'job_id',
        'title',
        'description',
        'cost_amount',
        'markup_percentage',
        'quoted_amount',
        'status',
        'client_signer_name',
        'client_signer_email',
        'signed_at',
        'signature_data',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'cost_amount' => 'decimal:2',
        'markup_percentage' => 'decimal:2',
        'quoted_amount' => 'decimal:2',
        'signed_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function job(): BelongsTo
    {
        return $this->belongsTo(Fjob::class, 'job_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Profit margin on this variation (£).
     */
    public function getMarginAmountAttribute(): float
    {
        return round((float)$this->quoted_amount - (float)$this->cost_amount, 2);
    }

    /**
     * Profit margin percentage on quoted value.
     */
    public function getMarginPercentAttribute(): float
    {
        if ((float)$this->quoted_amount <= 0) {
            return 0.00;
        }

        return round(($this->margin_amount / (float)$this->quoted_amount) * 100, 2);
    }
}
