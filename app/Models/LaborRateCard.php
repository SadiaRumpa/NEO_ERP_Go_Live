<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaborRateCard extends Model
{
    use HasFactory, BelongsToBusiness;

    protected $fillable = [
        'business_id',
        'user_id',
        'base_hourly_rate',
        'overtime_multiplier_1_5',
        'overtime_multiplier_2_0',
        'ni_burden_percent',
        'pension_burden_percent',
        'overhead_burden_percent',
        'effective_from',
        'is_active',
    ];

    protected $casts = [
        'base_hourly_rate' => 'decimal:2',
        'overtime_multiplier_1_5' => 'decimal:2',
        'overtime_multiplier_2_0' => 'decimal:2',
        'ni_burden_percent' => 'decimal:2',
        'pension_burden_percent' => 'decimal:2',
        'overhead_burden_percent' => 'decimal:2',
        'effective_from' => 'date',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calculate total burden percentage (e.g. 13.8% NI + 3% Pension + 10% Overhead = 26.8%).
     */
    public function getTotalBurdenPercentAttribute(): float
    {
        return (float)$this->ni_burden_percent + (float)$this->pension_burden_percent + (float)$this->overhead_burden_percent;
    }

    /**
     * Get the burden multiplier (e.g. 1.2680).
     */
    public function getBurdenMultiplierAttribute(): float
    {
        return round(1 + ($this->total_burden_percent / 100), 4);
    }

    /**
     * Calculate burdened rate per standard hour.
     */
    public function getBurdenedHourlyRateAttribute(): float
    {
        return round((float)$this->base_hourly_rate * $this->burden_multiplier, 2);
    }
}
