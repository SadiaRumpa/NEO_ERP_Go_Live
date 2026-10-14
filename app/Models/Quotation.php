<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    use BelongsToBusiness;

    protected $fillable = [
        'business_id',
        'lead_id',
        'customer_id',
        'job_type_id',
        'quotation_version',
        'cover_letter',
        'quotation_template_id',
        'quotation_date',
        'total_amount',
        'remarks',
        'valid_until',
        'status',
        'signed_at',
        'signer_name',
        'signer_signature',
        'signer_ip',
        'signer_user_agent',
        'rejection_reason',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function quotationTemplate()
    {
        return $this->belongsTo(QuotationTemplate::class);
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    public function jobType()
    {
        return $this->belongsTo(JobType::class);
    }

    public function sections()
    {
        return $this->hasMany(QuotationSection::class);
    }
}
