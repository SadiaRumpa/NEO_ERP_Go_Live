<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AccountingSyncLog extends Model
{
    use BelongsToBusiness;

    protected $fillable = [
        'business_id',
        'syncable_type',
        'syncable_id',
        'provider',
        'action',
        'status',
        'external_id',
        'request_payload',
        'response_payload',
        'error_message',
        'synced_at',
    ];

    protected $casts = [
        'request_payload'  => 'array',
        'response_payload' => 'array',
        'synced_at'        => 'datetime',
    ];

    public function syncable(): MorphTo
    {
        return $this->morphTo();
    }
}
