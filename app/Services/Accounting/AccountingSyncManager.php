<?php

namespace App\Services\Accounting;

use App\Models\AccountingSyncLog;
use App\Models\Customer;
use App\Models\Invoice;
use App\Services\Accounting\Drivers\QuickBooksDriver;
use App\Services\Accounting\Drivers\XeroDriver;
use InvalidArgumentException;

class AccountingSyncManager
{
    /**
     * @var array<string, AccountingSyncInterface>
     */
    protected array $drivers = [];

    public function __construct()
    {
        $this->registerDriver('xero', new XeroDriver());
        $this->registerDriver('quickbooks', new QuickBooksDriver());
    }

    public function registerDriver(string $name, AccountingSyncInterface $driver): void
    {
        $this->drivers[strtolower($name)] = $driver;
    }

    public function driver(?string $name = null): AccountingSyncInterface
    {
        $name = strtolower($name ?: config('services.accounting.default', 'xero'));

        if (!isset($this->drivers[$name])) {
            throw new InvalidArgumentException("Accounting driver [{$name}] is not supported.");
        }

        return $this->drivers[$name];
    }

    public function syncInvoice(Invoice $invoice, ?string $provider = null): AccountingSyncLog
    {
        return $this->driver($provider)->syncInvoice($invoice);
    }

    public function syncCustomer(Customer $customer, ?string $provider = null): AccountingSyncLog
    {
        return $this->driver($provider)->syncCustomer($customer);
    }
}
