<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerDetails extends Model
{
    protected $fillable = [
        'customer_id',
        'assets',
        'net_assets',
        'liabilities',
        'cash_in_bank',
    ];
}
