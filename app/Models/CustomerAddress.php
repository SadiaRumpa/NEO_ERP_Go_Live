<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerAddress extends Model
{
    protected $fillable = [
        'customer_id',
        'address',
        'city',
        'state',
        'country',
        'zip_code',
        'location',
        'type'
    ];
}
