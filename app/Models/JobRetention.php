<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobRetention extends Model
{
    use HasFactory, BelongsToBusiness;

    protected $fillable = [
        'business_id',
        'job_id',
        'valuation_id',
        'retention_reference',
        'total_retained_amount',
        'practical_completion_date',
        'pc_release_amount',
        'pc_released_at',
        'defects_expiry_date',
        'defects_release_amount',
        'defects_released_at',
        'status',
    ];

    protected $casts = [
        'practical_completion_date' => 'date',
        'defects_expiry_date' => 'date',
        'pc_released_at' => 'datetime',
        'defects_released_at' => 'datetime',
        'total_retained_amount' => 'decimal:2',
        'pc_release_amount' => 'decimal:2',
        'defects_release_amount' => 'decimal:2',
    ];

    public function job(): BelongsTo
    {
        return $this->belongsTo(Fjob::class, 'job_id');
    }

    public function valuation(): BelongsTo
    {
        return $this->belongsTo(JobValuation::class, 'valuation_id');
    }

    /**
     * Outstanding retained amount still held.
     */
    public function getBalanceHeldAttribute(): float
    {
        $released = 0.00;
        if ($this->pc_released_at) {
            $released += (float)$this->pc_release_amount;
        }
        if ($this->defects_released_at) {
            $released += (float)$this->defects_release_amount;
        }

        return max(0.00, round((float)$this->total_retained_amount - $released, 2));
    }
}
