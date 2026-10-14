<?php

namespace App\Services\Accounting;

use App\Models\AccountingSyncLog;
use App\Models\Customer;
use App\Models\Invoice;

interface AccountingSyncInterface
{
    /**
     * Sync a sales invoice to the remote accounting provider.
     */
    public function syncInvoice(Invoice $invoice): AccountingSyncLog;

    /**
     * Sync customer contacts to the remote accounting provider.
     */
    public function syncCustomer(Customer $customer): AccountingSyncLog;

    /**
     * Get provider name ('xero' or 'quickbooks').
     */
    public function getProviderName(): string;
}
