<?php

namespace App\Http\Controllers;

use App\Models\BusinessInfo;
use App\Models\Customer;
use App\Models\Fjob;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Quotation;
use App\Services\Accounting\AccountingSyncManager;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    /**
     * Display a listing of invoices.
     */
    public function index(Request $request): View
    {
        $query = Invoice::with(['customer', 'job']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->input('customer_id'));
        }

        $invoices = $query->latest('id')->paginate($this->getPerPage(15))->withQueryString();
        $customers = Customer::orderBy('company_name')->get();

        return view('invoices.index', compact('invoices', 'customers'));
    }

    /**
     * Show the form for creating a new invoice.
     */
    public function create(): View
    {
        $customers = Customer::orderBy('company_name')->get();
        $jobs = Fjob::latest('id')->limit(50)->get();

        return view('invoices.create', compact('customers', 'jobs'));
    }

    /**
     * Store a newly created invoice.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id'          => 'required|exists:customers,id',
            'job_id'               => 'nullable|exists:fjobs,id',
            'quotation_id'         => 'nullable|exists:quotations,id',
            'invoice_date'         => 'required|date',
            'due_date'             => 'required|date|after_or_equal:invoice_date',
            'payment_terms'        => 'nullable|string',
            'notes'                => 'nullable|string',
            'cis_applicable'       => 'nullable|boolean',
            'cis_deduction_rate'   => 'nullable|numeric|min:0|max:100',
            'items'                => 'required|array|min:1',
            'items.*.description'  => 'required|string',
            'items.*.quantity'     => 'required|numeric|min:0.01',
            'items.*.unit_price'   => 'required|numeric|min:0',
            'items.*.is_labor'     => 'nullable|boolean',
        ]);

        $invoice = DB::transaction(function () use ($validated) {
            $invoiceNumber = 'INV-' . date('Y') . '-' . str_pad(Invoice::count() + 1, 4, '0', STR_PAD_LEFT);

            $invoice = Invoice::create([
                'customer_id'        => $validated['customer_id'],
                'job_id'             => $validated['job_id'] ?? null,
                'quotation_id'       => $validated['quotation_id'] ?? null,
                'invoice_number'     => $invoiceNumber,
                'invoice_date'       => $validated['invoice_date'],
                'due_date'           => $validated['due_date'],
                'tax_rate'           => 20.00,
                'payment_terms'      => $validated['payment_terms'] ?? '30 Days Net',
                'notes'              => $validated['notes'] ?? null,
                'cis_applicable'     => !empty($validated['cis_applicable']),
                'cis_deduction_rate' => $validated['cis_deduction_rate'] ?? 0.00,
                'status'             => 'draft',
            ]);

            foreach ($validated['items'] as $itemData) {
                $qty = (float) $itemData['quantity'];
                $price = (float) $itemData['unit_price'];

                InvoiceItem::create([
                    'invoice_id'  => $invoice->id,
                    'description' => $itemData['description'],
                    'quantity'    => $qty,
                    'unit_price'  => $price,
                    'total_price' => round($qty * $price, 2),
                    'is_labor'    => !empty($itemData['is_labor']),
                ]);
            }

            $invoice->recalculateTotals();

            return $invoice;
        });

        return redirect()->route('invoices.show', $invoice->id)->with('success', 'Invoice created successfully.');
    }

    /**
     * Generate an invoice automatically from a completed Job.
     */
    public function generateFromJob(int $jobId): RedirectResponse
    {
        $job = Fjob::with(['customer', 'quotation', 'lead'])->findOrFail($jobId);

        $invoice = DB::transaction(function () use ($job) {
            $invoiceNumber = 'INV-' . date('Y') . '-' . str_pad(Invoice::count() + 1, 4, '0', STR_PAD_LEFT);

            $invoice = Invoice::create([
                'customer_id'        => $job->customer_id,
                'job_id'             => $job->id,
                'invoice_number'     => $invoiceNumber,
                'invoice_date'       => Carbon::today(),
                'due_date'           => Carbon::today()->addDays(30),
                'tax_rate'           => 20.00,
                'payment_terms'      => '30 Days Net',
                'status'             => 'draft',
                'notes'              => 'Generated from Job: ' . ($job->job_name ?: ('Job #' . $job->id)),
                'cis_applicable'     => true, // Default true for construction works
                'cis_deduction_rate' => 20.00,
            ]);

            // Add standard line items based on job scope
            InvoiceItem::create([
                'invoice_id'  => $invoice->id,
                'description' => 'Roofing & Cladding Installation Works - ' . ($job->job_name ?: 'Contract Site Works'),
                'quantity'    => 1.00,
                'unit_price'  => 3500.00,
                'total_price' => 3500.00,
                'is_labor'    => true,
            ]);

            InvoiceItem::create([
                'invoice_id'  => $invoice->id,
                'description' => 'Commercial Roofing Materials & Membrane System',
                'quantity'    => 1.00,
                'unit_price'  => 1850.00,
                'total_price' => 1850.00,
                'is_labor'    => false,
            ]);

            $invoice->recalculateTotals();

            return $invoice;
        });

        return redirect()->route('invoices.show', $invoice->id)->with('success', "Invoice {$invoice->invoice_number} generated from Job.");
    }

    /**
     * Display the specified invoice.
     */
    public function show(int $id): View
    {
        $invoice = Invoice::with(['customer', 'job', 'items', 'syncLogs'])->findOrFail($id);
        $business = BusinessInfo::first();

        return view('invoices.show', compact('invoice', 'business'));
    }

    /**
     * Generate / Stream PDF for invoice.
     */
    public function pdf(int $id): Response
    {
        $invoice = Invoice::with(['customer', 'job', 'items'])->findOrFail($id);
        $business = BusinessInfo::first();

        $pdf = Pdf::loadView('invoices.pdf.invoice', compact('invoice', 'business'))
            ->setPaper('a4', 'portrait')
            ->setOption([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => false,
            ]);

        return $pdf->download("{$invoice->invoice_number}.pdf");
    }

    /**
     * Record payment for an invoice.
     */
    public function recordPayment(Request $request, int $id): RedirectResponse
    {
        $invoice = Invoice::findOrFail($id);

        $validated = $request->validate([
            'payment_reference' => 'required|string|max:100',
            'paid_at'           => 'nullable|date',
        ]);

        $invoice->update([
            'status'            => 'paid',
            'payment_reference' => $validated['payment_reference'],
            'paid_at'           => $validated['paid_at'] ?? now(),
        ]);

        return back()->with('success', "Payment recorded for invoice {$invoice->invoice_number}.");
    }

    /**
     * Sync invoice to remote accounting software (Xero or QuickBooks).
     */
    public function syncAccounting(Request $request, int $id, AccountingSyncManager $syncManager): RedirectResponse
    {
        $invoice = Invoice::with(['customer', 'items'])->findOrFail($id);
        $provider = $request->input('provider', 'xero');

        try {
            $log = $syncManager->syncInvoice($invoice, $provider);
            return back()->with('success', "Invoice successfully synced to " . ucfirst($provider) . " (Ref: {$log->external_id}).");
        } catch (\Throwable $e) {
            return back()->with('error', "Accounting sync failed: " . $e->getMessage());
        }
    }
}
