<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Lead extends Model
{
    use HasSlug, BelongsToBusiness;

    protected $fillable = [
        'business_id',
        'enquiry_id',
        'enquiry',
        'lead_name',
        'lead_title',
        'lead_description',
        'customer_id',
        'lead_type',
        'lead_category',
        'lead_priority',
        'lead_status',
        'lead_close_reason',
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

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('lead_name')
            ->saveSlugsTo('slug');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function leadaddress()
    {
        return $this->hasOne(LeadAddress::class);
    }

    public function leadimages()
    {
        return $this->hasMany(LeadImage::class);
    }

    public function leadnotes()
    {
        return $this->hasMany(LeadNote::class);
    }

    public function leadattachments()
    {
        return $this->hasMany(LeadAttachment::class);
    }

    public function completedquotation()
    {
        return $this->hasOne(Quotation::class)->where('status', 'accepted');
    }
}
