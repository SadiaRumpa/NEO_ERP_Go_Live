<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fjob extends Model
{
    protected $fillable = [
        'enquiry_id',
        'enquiry',
        'lead_id',
        'job_name',
        'slug',
        'job_title',
        'job_description',
        'customer_id',
        'job_type',
        'job_category',
        'job_priority',
        'job_status',
        'job_closed_by',
        'job_closed_at',
        'job_close_reason',
        'annual_maintenance',
        'installations',
        'repairs',
        'testing',
        'assigned_to'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    public function jobaddress()
    {
        return $this->hasOne(FjobAddress::class);
    }
    public function jobimages()
    {
        return $this->hasMany(FjobImage::class);
    }
    public function jobnotes()
    {
        return $this->hasMany(FjobNote::class);
    }
    public function jobquotations()
    {
        return $this->hasOne(FjobQuotation::class);
    }
    public function  jobattachments()
    {
        return $this->hasMany(FjobAttachment::class);
    }
    public function jobattributes()
    {
        return $this->hasMany(FjobAttribute::class);
    }
    public function jobhschecks()
    {
        return $this->hasMany(FjobHsCheck::class);
    }
    public function jobtype()
    {
        return $this->hasOne(JobType::class, 'slug', 'job_type');
    }

    public function addresses()
    {
        return $this->hasMany(FjobAddress::class, 'fjob_id');
    }

    public function schedules()
    {
        return $this->hasMany(JobSchedule::class, 'job_id');
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    public function notes()
    {
        return $this->hasMany(FjobNote::class, 'fjob_id');
    }

    public function images()
    {
        return $this->hasMany(FjobImage::class, 'fjob_id');
    }

    public function quotation()
    {
        return $this->hasOne(FjobQuotation::class, 'fjob_id');
    }
}
