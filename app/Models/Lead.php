<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Lead extends Model
{
    use HasSlug;
    protected $fillable = [
        'enquiry_id',
        'lead_name',
        'lead_description',
        'customer_id',
        'lead_type',
        'lead_category',
        'lead_priority',
        'lead_status',
    ];

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

    public function images()
    {
        return $this->hasMany(LeadImage::class);
    }
}
