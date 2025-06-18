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
        'fjob_category',
        'job_priority',
        'fjob_status',
        'fjob_closed_by',
        'fjob_closed_at',
        'fjob_close_reason'
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
    public function fjobaddress()
    {
        return $this->hasOne(FjobAddress::class);
    }
    public function fjobimages()
    {
        return $this->hasMany(FjobImage::class);
    }
    public function fjobnotes()
    {
        return $this->hasMany(FjobNote::class);
    }
    public function fjobquotations()
    {
        return $this->hasMany(FjobQuotation::class);
    }
    public function fjobattachments()
    {
        return $this->hasMany(FjobAttachment::class);
    }
}
