<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use BelongsToBusiness;

    protected $fillable = [
        'business_id',
        'customer_id',
        'job_id',
        'quotation_id',
        'invoice_number',
        'invoice_date',
        'due_date',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'total_amount',
        'cis_applicable',
        'cis_deduction_rate',
        'cis_deduction_amount',
        'net_payable',
        'status',
        'payment_terms',
        'notes',
        'paid_at',
        'payment_reference',
    ];

    protected $casts = [
        'invoice_date'         => 'date',
        'due_date'             => 'date',
        'subtotal'             => 'decimal:2',
        'tax_rate'             => 'decimal:2',
        'tax_amount'           => 'decimal:2',
        'total_amount'         => 'decimal:2',
        'cis_applicable'       => 'boolean',
        'cis_deduction_rate'   => 'decimal:2',
        'cis_deduction_amount' => 'decimal:2',
        'net_payable'          => 'decimal:2',
        'paid_at'              => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(Fjob::class, 'job_id');
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function syncLogs(): HasMany
    {
        return $this->hasMany(AccountingSyncLog::class, 'syncable_id')
            ->where('syncable_type', self::class);
    }

    /**
     * Recalculate totals, UK VAT, and CIS deduction.
     */
    public function recalculateTotals(): self
    {
        $subtotal = 0;
        $laborTotal = 0;

        foreach ($this->items as $item) {
            $lineTotal = round($item->quantity * $item->unit_price, 2);
            $subtotal += $lineTotal;
            if ($item->is_labor) {
                $laborTotal += $lineTotal;
            }
        }

        $taxAmount = round($subtotal * ($this->tax_rate / 100), 2);
        $totalAmount = $subtotal + $taxAmount;

        // CIS Deduction applies to labor portion in the UK construction industry
        $cisDeduction = 0;
        if ($this->cis_applicable && $this->cis_deduction_rate > 0) {
            $cisDeduction = round($laborTotal * ($this->cis_deduction_rate / 100), 2);
        }

        $netPayable = max(0, $totalAmount - $cisDeduction);

        $this->update([
            'subtotal'             => $subtotal,
            'tax_amount'           => $taxAmount,
            'total_amount'         => $totalAmount,
            'cis_deduction_amount' => $cisDeduction,
            'net_payable'          => $netPayable,
        ]);

        return $this;
    }
}
