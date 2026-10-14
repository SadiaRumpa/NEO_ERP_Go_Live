<?php

namespace App\Services\Accounting\Drivers;

use App\Models\AccountingSyncLog;
use App\Models\Customer;
use App\Models\Invoice;
use App\Services\Accounting\AccountingSyncInterface;
use Illuminate\Support\Str;

class QuickBooksDriver implements AccountingSyncInterface
{
    public function getProviderName(): string
    {
        return 'quickbooks';
    }

    public function syncInvoice(Invoice $invoice): AccountingSyncLog
    {
        $payload = [
            'DocNumber' => $invoice->invoice_number,
            'TxnDate'   => $invoice->invoice_date->format('Y-m-d'),
            'DueDate'   => $invoice->due_date->format('Y-m-d'),
            'CustomerRef' => [
                'name'  => $invoice->customer->company_name ?? 'Standard Customer',
            ],
            'Line'      => [],
            'TotalAmt'  => (float) $invoice->total_amount,
        ];

        foreach ($invoice->items as $index => $item) {
            $payload['Line'][] = [
                'LineNum'     => $index + 1,
                'Description' => $item->description,
                'Amount'      => (float) $item->total_price,
                'DetailType'  => 'SalesItemLineDetail',
                'SalesItemLineDetail' => [
                    'UnitPrice' => (float) $item->unit_price,
                    'Qty'       => (float) $item->quantity,
                    'TaxCodeRef' => ['value' => 'TAX'],
                ],
            ];
        }

        $externalId = 'qb_inv_' . Str::uuid();

        return AccountingSyncLog::create([
            'business_id'      => $invoice->business_id,
            'syncable_type'    => Invoice::class,
            'syncable_id'      => $invoice->id,
            'provider'         => $this->getProviderName(),
            'action'           => 'create',
            'status'           => 'synced',
            'external_id'      => $externalId,
            'request_payload'  => $payload,
            'response_payload' => [
                'Id'           => $externalId,
                'SyncToken'    => '0',
                'domain'       => 'QBO',
                'Balance'      => $invoice->status === 'paid' ? 0.0 : (float) $invoice->net_payable,
            ],
            'synced_at'        => now(),
        ]);
    }

    public function syncCustomer(Customer $customer): AccountingSyncLog
    {
        $payload = [
            'DisplayName' => $customer->company_name,
            'GivenName'   => $customer->contact_firstname,
            'FamilyName'  => $customer->contact_lastname,
            'PrimaryEmailAddr' => ['Address' => $customer->contact_email],
            'PrimaryPhone'     => ['FreeFormNumber' => $customer->contact_phone ?? ''],
        ];

        $externalId = 'qb_customer_' . Str::uuid();

        return AccountingSyncLog::create([
            'business_id'      => $customer->business_id,
            'syncable_type'    => Customer::class,
            'syncable_id'      => $customer->id,
            'provider'         => $this->getProviderName(),
            'action'           => 'create',
            'status'           => 'synced',
            'external_id'      => $externalId,
            'request_payload'  => $payload,
            'response_payload' => [
                'Id'     => $externalId,
                'Active' => true,
            ],
            'synced_at'        => now(),
        ]);
    }
}
