<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class JobSiteVisit extends Model
{
    use BelongsToBusiness;

    protected $fillable = [
        'business_id',
        'job_id',
        'user_id',
        'arrived_at',
        'departed_at',
        'latitude',
        'longitude',
        'status',
        'notes',
        'customer_signature',
        'customer_signer_name',
        'signed_at',
    ];

    protected $casts = [
        'arrived_at'  => 'datetime',
        'departed_at' => 'datetime',
        'signed_at'   => 'datetime',
        'latitude'    => 'decimal:7',
        'longitude'   => 'decimal:7',
    ];

    public function job(): BelongsTo
    {
        return $this->belongsTo(Fjob::class, 'job_id');
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function safetyChecklist(): HasOne
    {
        return $this->hasOne(JobSafetyChecklist::class, 'job_site_visit_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(JobSitePhoto::class, 'job_site_visit_id');
    }
}
