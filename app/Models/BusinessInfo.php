<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class BusinessInfo extends Model
{
    use HasSlug;

    protected $table = 'business_info';
    protected $fillable = [
        'business_name',
        'business_short_name',
        'business_domain',
        'business_allowed_file_types',
        'business_logo',
        'business_logo_dark',
        'business_favicon',
        'business_address',
        'business_address2',
        'business_city',
        'business_state',
        'business_zip',
        'business_phone',
        'business_email',
        'business_website',
        'business_registration_number',
        'business_registration_number2',
        'business_status',
        'business_start_date',
        'business_brand_app_name',
        'business_brand_logo_on_top_left',
        'business_brand_credit_on_footer_enable',
        'business_brand_credit_on_footer',
    ];

    public function getSlugOptions() : SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('business_name')
            ->saveSlugsTo('business_slug');
    }
}
