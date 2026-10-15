<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;

class Fjob extends Model
{
    use BelongsToBusiness;

    protected $fillable = [
        'business_id',
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

    protected $casts = [
        'assigned_to' => 'array',
        'annual_maintenance' => 'boolean',
        'installations' => 'boolean',
        'repairs' => 'boolean',
        'testing' => 'boolean',
        'job_closed_at' => 'datetime',
    ];

    public function setAssignedToAttribute($value): void
    {
        if (is_null($value) || $value === '') {
            $this->attributes['assigned_to'] = null;
        } elseif (is_array($value)) {
            $this->attributes['assigned_to'] = json_encode(array_values($value));
        } elseif (is_numeric($value)) {
            $this->attributes['assigned_to'] = json_encode([(int) $value]);
        } elseif (is_string($value)) {
            json_decode($value);
            if (json_last_error() === JSON_ERROR_NONE) {
                $this->attributes['assigned_to'] = $value;
            } else {
                $this->attributes['assigned_to'] = json_encode([$value]);
            }
        } else {
            $this->attributes['assigned_to'] = json_encode($value);
        }
    }

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

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'job_id');
    }

    public function siteVisits()
    {
        return $this->hasMany(JobSiteVisit::class, 'job_id');
    }

    public function sitePhotos()
    {
        return $this->hasMany(JobSitePhoto::class, 'job_id');
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class, 'job_id');
    }

    public function laborTimesheets()
    {
        return $this->hasMany(LaborTimesheet::class, 'job_id');
    }

    public function subcontractorCertificates()
    {
        return $this->hasMany(SubcontractorPaymentCertificate::class, 'job_id');
    }

    public function plantHires()
    {
        return $this->hasMany(PlantHire::class, 'job_id');
    }

    public function variations()
    {
        return $this->hasMany(JobVariation::class, 'job_id');
    }

    public function valuations()
    {
        return $this->hasMany(JobValuation::class, 'job_id');
    }

    public function retentions()
    {
        return $this->hasMany(JobRetention::class, 'job_id');
    }
}
