<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FjobHsCheck extends Model
{
    protected $table = 'fjob_hs_check';
    protected $fillable = [
        'fjob_id',
        'job_title',
        'hs_check_title',
        'checked_by_user',
        'status',
    ];

    public function checklist()
    {
        return $this->belongsTo(HsChecklist::class, 'hs_checklist_id');
    }

    public function job()
    {
        return $this->belongsTo(Fjob::class, 'fjob_id');
    }
}
