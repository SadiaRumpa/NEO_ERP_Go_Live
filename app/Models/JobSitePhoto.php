<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobSitePhoto extends Model
{
    protected $fillable = [
        'job_site_visit_id',
        'job_id',
        'user_id',
        'photo_path',
        'photo_name',
        'caption',
        'stage',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'latitude'  => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function siteVisit(): BelongsTo
    {
        return $this->belongsTo(JobSiteVisit::class, 'job_site_visit_id');
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(Fjob::class, 'job_id');
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
