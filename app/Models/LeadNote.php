<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadNote extends Model
{
    protected $fillable = [
        'lead_id',
        'note',
        'note_by',
        'note_type',
        'note_status',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }
}
