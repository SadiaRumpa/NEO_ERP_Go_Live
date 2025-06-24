<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobSchedule extends Model
{
    protected $fillable = [
        'job_id',
        'job_name',
        'start_date',
        'end_date',
        'instructions',
        'status',
    ];

    public function workers()
    {
        return $this->belongsToMany(User::class, 'job_schedule_workers')
            ->withPivot('assigned_date', 'completion_date', 'status', 'notes')
            ->withTimestamps();
    }

    public function attachments()
    {
        return $this->hasMany(JobScheduleAttachment::class);
    }
}
