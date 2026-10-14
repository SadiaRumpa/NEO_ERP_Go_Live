<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobSafetyChecklist extends Model
{
    protected $fillable = [
        'job_site_visit_id',
        'job_id',
        'user_id',
        'working_at_height_passed',
        'ppe_compliant',
        'edge_protection_verified',
        'weather_acceptable',
        'risk_assessment_notes',
        'signed_by_worker',
        'completed_at',
    ];

    protected $casts = [
        'working_at_height_passed' => 'boolean',
        'ppe_compliant'            => 'boolean',
        'edge_protection_verified' => 'boolean',
        'weather_acceptable'       => 'boolean',
        'completed_at'             => 'datetime',
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
