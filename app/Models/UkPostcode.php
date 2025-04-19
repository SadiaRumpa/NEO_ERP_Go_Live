<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UkPostcode extends Model
{
    protected $table = 'uk_postcodes';
    public $incrementing = false;
    public $timestamps = false; // or true if you use timestamps

    protected $primaryKey = null;
    public $keyType = 'string';

    public function getKeyName()
    {
        return null;
    }
}
