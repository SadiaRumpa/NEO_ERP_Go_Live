<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailTemplate extends Model
{
    protected $table = 'mail_templates';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'subject',
        'body',
        'attachments',
        'type',
        'status',
    ];   
}
