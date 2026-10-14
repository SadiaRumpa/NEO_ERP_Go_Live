<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class BusinessScope implements Scope
{
    /**
     * All models should be scoped to the active tenant/business unless bypassed.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $businessId = session('current_business_id');

        if (!$businessId && Auth::check()) {
            $user = Auth::user();
            // Super admins can view all unless filtering by a specific business
            if ($user->hasRole('super_admin') && !session()->has('current_business_id')) {
                return;
            }
            $businessId = $user->business_id;
        }

        // If no business context is set (e.g. unauthenticated background console/seeder), do not filter
        if (!$businessId) {
            return;
        }

        $builder->where($model->qualifyColumn('business_id'), $businessId);
    }
}
