@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-1 mb-1">
                <span class="text-muted fw-light">Invoices /</span> {{ $invoice->invoice_number }}
            </h4>
            <span class="text-muted">Issue Date: {{ $invoice->invoice_date->format('d M Y') }} &bull; Due: {{ $invoice->due_date->format('d M Y') }}</span>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('invoices.pdf', $invoice->id) }}" class="btn btn-outline-danger">
                <i class="bx bxs-file-pdf me-1"></i> Download PDF
            </a>
            @if($invoice->status !== 'paid')
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#recordPaymentModal">
                <i class="bx bx-check-circle me-1"></i> Record Payment
            </button>
            @endif
            <div class="dropdown">
                <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bx bx-sync me-1"></i> Accounting Sync
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <form method="POST" action="{{ route('invoices.sync', $invoice->id) }}">
                            @csrf
                            <input type="hidden" name="provider" value="xero">
                            <button type="submit" class="dropdown-item">
                                <i class="bx bx-cloud-upload me-1 text-primary"></i> Sync to Xero
                            </button>
                        </form>
                    </li>
                    <li>
                        <form method="POST" action="{{ route('invoices.sync', $invoice->id) }}">
                            @csrf
                            <input type="hidden" name="provider" value="quickbooks">
                            <button type="submit" class="dropdown-item">
                                <i class="bx bx-cloud-upload me-1 text-success"></i> Sync to QuickBooks
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="row">
        <!-- Invoice Details & Items -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-sm-6">
                            <h6 class="text-muted text-uppercase mb-2">Billed To:</h6>
                            <h5 class="fw-bold mb-1">{{ $invoice->customer->company_name ?? 'Client' }}</h5>
                            <p class="text-muted mb-0">
                                Attn: {{ $invoice->customer->contact_name ?? 'Accounts' }}<br>
                                Email: {{ $invoice->customer->contact_email }}<br>
                                Phone: {{ $invoice->customer->contact_phone }}
                            </p>
                        </div>
                        <div class="col-sm-6 text-sm-end">
                            <h6 class="text-muted text-uppercase mb-2">Invoice Info:</h6>
                            <p class="mb-1"><strong>Invoice Number:</strong> {{ $invoice->invoice_number }}</p>
                            <p class="mb-1"><strong>Status:</strong>
                                @if($invoice->status === 'paid')
                                    <span class="badge bg-success">PAID</span>
                                @else
                                    <span class="badge bg-secondary">{{ strtoupper($invoice->status) }}</span>
                                @endif
                            </p>
                            <p class="mb-1"><strong>Payment Terms:</strong> {{ $invoice->payment_terms }}</p>
                            @if($invoice->job)
                            <p class="mb-0"><strong>Job:</strong> <a href="{{ route('jobs.show', $invoice->job_id) }}">{{ $invoice->job->job_name ?: ('Job #' . $invoice->job_id) }}</a></p>
                            @endif
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Description / Line Item</th>
                                    <th class="text-center" style="width: 10%;">Qty</th>
                                    <th class="text-end" style="width: 18%;">Unit Price</th>
                                    <th class="text-end" style="width: 20%;">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoice->items as $item)
                                <tr>
                                    <td>
                                        <strong>{{ $item->description }}</strong>
                                        @if($item->is_labor)
                                            <span class="badge bg-label-primary ms-1">CIS Labor</span>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ number_format($item->quantity, 2) }}</td>
                                    <td class="text-end">£{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="text-end fw-semibold">£{{ number_format($item->total_price, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="row justify-content-end mt-4">
                        <div class="col-md-6">
                            <ul class="list-group list-group-flush">
                                <li class="d-flex justify-content-between py-2 border-bottom">
                                    <span class="text-muted">Subtotal (Net):</span>
                                    <span class="fw-semibold">£{{ number_format($invoice->subtotal, 2) }}</span>
                                </li>
                                <li class="d-flex justify-content-between py-2 border-bottom">
                                    <span class="text-muted">UK VAT ({{ number_format($invoice->tax_rate, 0) }}%):</span>
                                    <span class="fw-semibold">£{{ number_format($invoice->tax_amount, 2) }}</span>
                                </li>
                                <li class="d-flex justify-content-between py-2 border-bottom">
                                    <span class="text-muted">Gross Total:</span>
                                    <span class="fw-semibold">£{{ number_format($invoice->total_amount, 2) }}</span>
                                </li>
                                @if($invoice->cis_applicable && $invoice->cis_deduction_amount > 0)
                                <li class="d-flex justify-content-between py-2 border-bottom text-danger">
                                    <span>Less CIS Deduction ({{ number_format($invoice->cis_deduction_rate, 0) }}%):</span>
                                    <span class="fw-bold">-£{{ number_format($invoice->cis_deduction_amount, 2) }}</span>
                                </li>
                                @endif
                                <li class="d-flex justify-content-between py-3 fs-5 fw-bold text-primary">
                                    <span>Net Amount Payable:</span>
                                    <span>£{{ number_format($invoice->net_payable, 2) }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar / Sync Logs -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header border-bottom">
                    <h6 class="mb-0 fw-bold"><i class="bx bx-check-shield me-1"></i> Payment Status</h6>
                </div>
                <div class="card-body pt-3">
                    @if($invoice->status === 'paid')
                        <div class="alert alert-success mb-2">
                            <h6 class="alert-heading fw-bold mb-1"><i class="bx bx-check-double"></i> Fully Paid</h6>
                            <p class="mb-0 small">Paid on: {{ $invoice->paid_at?->format('d/m/Y H:i') }}<br>Reference: {{ $invoice->payment_reference }}</p>
                        </div>
                    @else
                        <p class="text-muted small">This invoice is currently awaiting payment. Due date is <strong>{{ $invoice->due_date->format('d/m/Y') }}</strong>.</p>
                        <button type="button" class="btn btn-outline-success w-100" data-bs-toggle="modal" data-bs-target="#recordPaymentModal">
                            <i class="bx bx-check me-1"></i> Mark as Paid
                        </button>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header border-bottom">
                    <h6 class="mb-0 fw-bold"><i class="bx bx-history me-1"></i> Accounting Sync History</h6>
                </div>
                <div class="card-body pt-3">
                    @forelse($invoice->syncLogs as $log)
                    <div class="d-flex align-items-start mb-3 pb-2 border-bottom">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded bg-label-{{ $log->status === 'synced' ? 'success' : 'danger' }}">
                                <i class="bx {{ $log->provider === 'xero' ? 'bx-cube' : 'bx-briefcase' }}"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0 fw-semibold">{{ strtoupper($log->provider) }} Sync</h6>
                            <small class="text-muted">{{ $log->synced_at?->diffForHumans() ?? 'Just now' }}</small>
                            <p class="small text-truncate mb-0 mt-1">Ref: <code>{{ $log->external_id }}</code></p>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted small text-center my-3">No accounting sync events recorded yet. Use the "Accounting Sync" button to publish to Xero or QuickBooks.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Record Payment -->
<div class="modal fade" id="recordPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('invoices.record_payment', $invoice->id) }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Record Payment for {{ $invoice->invoice_number }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Payment Reference (e.g. BACS-84920)</label>
                    <input type="text" name="payment_reference" class="form-control" required placeholder="BACS / Faster Payment Ref">
                </div>
                <div class="mb-3">
                    <label class="form-label">Payment Date</label>
                    <input type="datetime-local" name="paid_at" class="form-control" value="{{ date('Y-m-d\TH:i') }}">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success">Confirm Payment</button>
            </div>
        </form>
    </div>
</div>
@endsection
