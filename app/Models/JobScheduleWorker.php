<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobScheduleWorker extends Model
{
    protected $fillable = [
        'job_schedule_id',
        'worker_id',
        'assigned_date',
        'completion_date',
        'status',
        'notes',
    ];

    public function jobSchedule()
    {
        return $this->belongsTo(JobSchedule::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
