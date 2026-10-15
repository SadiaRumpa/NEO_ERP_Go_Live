<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subcontractor extends Model
{
    use HasFactory, BelongsToBusiness;

    protected $fillable = [
        'business_id',
        'company_name',
        'trading_name',
        'contact_name',
        'email',
        'phone',
        'address',
        'utr_number',
        'company_registration_number',
        'national_insurance_number',
        'cis_tax_status',
        'cis_verification_number',
        'verified_at',
        'trade_specialism',
        'status',
    ];

    protected $casts = [
        'verified_at' => 'date',
    ];

    public function paymentCertificates(): HasMany
    {
        return $this->hasMany(SubcontractorPaymentCertificate::class);
    }

    /**
     * Get the HMRC CIS deduction percentage for this subcontractor.
     * Gross: 0%, Standard: 20%, Higher: 30%
     */
    public function getCisTaxRateAttribute(): float
    {
        return match($this->cis_tax_status) {
            'gross' => 0.00,
            'higher' => 30.00,
            default => 20.00,
        };
    }
}
