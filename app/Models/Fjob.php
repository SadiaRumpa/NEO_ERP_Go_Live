<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Fjob extends Model
{
    use HasSlug;
    protected $fillable = [
        'enquiry_id',
        'lead_id',
        'job_name',
        'job_description',
        'customer_id',
        'job_type',
        'job_category',
        'ob_priority',
        'job_status',
        'job_closed_by',
        'job_closed_at',
        'job_close_reason'
    ];
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('fjob_name')
            ->saveSlugsTo('slug');
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
}
