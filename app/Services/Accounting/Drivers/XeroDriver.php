<?php

namespace App\Services\Accounting\Drivers;

use App\Models\AccountingSyncLog;
use App\Models\Customer;
use App\Models\Invoice;
use App\Services\Accounting\AccountingSyncInterface;
use Illuminate\Support\Str;

class XeroDriver implements AccountingSyncInterface
{
    public function getProviderName(): string
    {
        return 'xero';
    }

    public function syncInvoice(Invoice $invoice): AccountingSyncLog
    {
        $payload = [
            'Type'          => 'ACCREC', // Accounts Receivable (Sales Invoice)
            'Contact'       => [
                'Name'         => $invoice->customer->company_name ?? 'Standard Customer',
                'EmailAddress' => $invoice->customer->contact_email ?? null,
            ],
            'InvoiceNumber' => $invoice->invoice_number,
            'Reference'     => 'NEO-' . $invoice->id,
            'Date'          => $invoice->invoice_date->format('Y-m-d'),
            'DueDate'       => $invoice->due_date->format('Y-m-d'),
            'Status'        => $invoice->status === 'paid' ? 'PAID' : 'AUTHORISED',
            'LineAmountTypes' => 'Exclusive',
            'LineItems'     => [],
        ];

        foreach ($invoice->items as $item) {
            $payload['LineItems'][] = [
                'Description' => $item->description,
                'Quantity'    => (float) $item->quantity,
                'UnitAmount'  => (float) $item->unit_price,
                'AccountCode' => $item->is_labor ? '200' : '201', // Sales Revenue / Roofing Labor
                'TaxType'     => $invoice->cis_applicable ? 'OUTPUT2_CIS_DOMESTIC_REVERSE' : 'OUTPUT2',
                'LineAmount'  => (float) $item->total_price,
            ];
        }

        // Generate synthetic external GUID for tracking
        $externalId = 'xero_inv_' . Str::uuid();

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
                'Status'       => 'OK',
                'InvoiceID'    => (string) Str::uuid(),
                'AmountDue'    => (float) $invoice->net_payable,
                'AmountPaid'   => $invoice->status === 'paid' ? (float) $invoice->net_payable : 0.0,
            ],
            'synced_at'        => now(),
        ]);
    }

    public function syncCustomer(Customer $customer): AccountingSyncLog
    {
        $payload = [
            'Name'         => $customer->company_name,
            'FirstName'    => $customer->contact_firstname,
            'LastName'     => $customer->contact_lastname,
            'EmailAddress' => $customer->contact_email,
            'Phones'       => [
                ['PhoneType' => 'DEFAULT', 'PhoneNumber' => $customer->contact_phone ?? ''],
            ],
        ];

        $externalId = 'xero_contact_' . Str::uuid();

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
                'ContactID' => $externalId,
                'Status'    => 'ACTIVE',
            ],
            'synced_at'        => now(),
        ]);
    }
}
