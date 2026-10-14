<?php

namespace App\Models\Concerns;

use App\Models\BusinessInfo;
use App\Models\Scopes\BusinessScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait BelongsToBusiness
{
    /**
     * Boot the BelongsToBusiness trait for a model.
     */
    public static function bootBelongsToBusiness(): void
    {
        static::addGlobalScope(new BusinessScope());

        static::creating(function ($model) {
            if (empty($model->business_id)) {
                $businessId = session('current_business_id');
                if (!$businessId && Auth::check()) {
                    $businessId = Auth::user()->business_id;
                }
                if ($businessId) {
                    $model->business_id = $businessId;
                }
            }
        });
    }

    /**
     * Get the business that owns this entity.
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(BusinessInfo::class, 'business_id');
    }

    /**
     * Scope a query to only include entities for a specific business.
     */
    public function scopeForBusiness($query, int $businessId)
    {
        return $query->withoutGlobalScope(BusinessScope::class)->where('business_id', $businessId);
    }
}
