<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory, BelongsToBusiness;

    protected $fillable = [
        'business_id',
        'name',
        'code',
        'contact_name',
        'email',
        'phone',
        'address',
        'tax_number',
        'payment_terms',
        'status',
    ];

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function supplierBills(): HasMany
    {
        return $this->hasMany(SupplierBill::class);
    }
}
