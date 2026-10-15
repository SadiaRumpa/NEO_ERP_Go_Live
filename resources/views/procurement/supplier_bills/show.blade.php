@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-1 mb-1">
                <span class="text-muted fw-light">Procurement / Supplier Bills /</span> {{ $supplierBill->internal_reference }}
            </h4>
            <p class="text-muted mb-0">Supplier Invoice #<strong>{{ $supplierBill->bill_number }}</strong> from <strong>{{ $supplierBill->supplier->name }}</strong></p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('procurement.purchase-orders.show', $supplierBill->purchaseOrder) }}" class="btn btn-outline-secondary">
                <i class="bx bx-left-arrow-alt me-1"></i> View PO {{ $supplierBill->purchaseOrder->po_number }}
            </a>
            @if($supplierBill->approval_status === 'held')
                <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#overrideModal">
                    <i class="bx bx-shield-quarter me-1"></i> Commercial Manager Override
                </button>
            @endif
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('warning'))
    <div class="alert alert-warning alert-dismissible" role="alert">
        <i class="bx bx-error me-1"></i> {{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- 3-Way Match Result Banner -->
    @if($supplierBill->match_status === 'matched')
        <div class="alert alert-success d-flex align-items-center mb-4" role="alert">
            <i class="bx bx-check-circle fs-3 me-3"></i>
            <div>
                <h6 class="alert-heading mb-1 fw-bold">3-Way Match Verified & Approved</h6>
                <span>All line items accurately match agreed Purchase Order rates and physically verified Goods Received Notes. Approved for accounts payable.</span>
            </div>
        </div>
    @elseif($supplierBill->match_status === 'approved_override')
        <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
            <i class="bx bx-shield-quarter fs-3 me-3"></i>
            <div>
                <h6 class="alert-heading mb-1 fw-bold">Commercial Override Applied</h6>
                <span>This bill had commercial variances but was explicitly approved by <strong>{{ $supplierBill->approver->name ?? 'Commercial Manager' }}</strong> on {{ $supplierBill->approved_at?->format('d M Y H:i') }}.</span>
            </div>
        </div>
    @else
        <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
            <i class="bx bx-error-circle fs-3 me-3"></i>
            <div>
                <h6 class="alert-heading mb-1 fw-bold">Commercial Variance Detected — PAYMENT HELD</h6>
                <span>Discrepancies identified between PO rates, GRN physical deliveries, and billed invoice amounts. Payment is held pending commercial review.</span>
            </div>
        </div>
    @endif

    <!-- Audit Details Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.05em;">Invoice Details</small>
                        <span class="badge bg-label-info">{{ $supplierBill->supplier->payment_terms }}</span>
                    </div>
                    <h5 class="card-title fw-bold text-dark mb-1">{{ $supplierBill->supplier->name }}</h5>
                    <div class="small mt-2">
                        <div class="mb-1"><strong class="text-secondary">Invoice Number:</strong> <span class="font-monospace fw-bold text-dark">{{ $supplierBill->bill_number }}</span></div>
                        <div class="mb-1"><strong class="text-secondary">Invoice Date:</strong> {{ $supplierBill->bill_date?->format('d M Y') }}</div>
                        <div><strong class="text-secondary">Payment Due:</strong> {{ $supplierBill->due_date?->format('d M Y') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <small class="text-muted d-block text-uppercase fw-semibold mb-2" style="font-size: 0.72rem; letter-spacing: 0.05em;">Commercial Status & Authorization</small>
                    <div class="mb-3">
                        @if($supplierBill->approval_status === 'approved')
                            <span class="badge bg-success fs-6 px-3 py-2">
                                <span class="status-indicator-dot dot-healthy me-1"></span> Approved for Payment
                            </span>
                        @elseif($supplierBill->approval_status === 'held')
                            <span class="badge bg-danger fs-6 px-3 py-2">
                                <span class="status-indicator-dot dot-critical me-1"></span> HELD ON VARIANCE
                            </span>
                        @else
                            <span class="badge bg-secondary fs-6 px-3 py-2">Pending Review</span>
                        @endif
                    </div>
                    <div class="small border-top pt-2">
                        @if($supplierBill->approved_by)
                            <div><strong class="text-secondary">Authorized By:</strong> {{ $supplierBill->approver->name }}</div>
                            <div class="text-muted"><i class="bx bx-time me-1"></i>{{ $supplierBill->approved_at?->format('d M Y H:i') }}</div>
                        @else
                            <div class="text-muted"><i class="bx bx-shield-quarter me-1"></i>Awaiting commercial sign-off</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="neo-kpi-card kpi-primary h-100 shadow-sm">
                <div class="kpi-subtitle">Total Invoiced Amount</div>
                <div class="kpi-value text-primary tabular-nums mb-3">
                    £{{ number_format($supplierBill->total_amount, 2) }}
                </div>
                <div class="border-top pt-2 small">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Net Subtotal:</span>
                        <span class="fw-semibold tabular-nums">£{{ number_format($supplierBill->subtotal, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">VAT Amount:</span>
                        <span class="fw-semibold tabular-nums">£{{ number_format($supplierBill->tax_amount, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3-Way Match Matrix Table -->
    <div class="card mb-4">
        <div class="card-header border-bottom">
            <h5 class="card-title mb-0">3-Way Match Reconciliation Matrix (PO vs. GRN vs. Bill)</h5>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead class="table-light">
                    <tr>
                        <th rowspan="2" class="align-middle" style="width: 25%;">Item Description</th>
                        <th colspan="2" class="text-center border-start border-end table-primary">1. PO (Agreed)</th>
                        <th class="text-center border-end table-success">2. GRN (Delivered)</th>
                        <th colspan="2" class="text-center border-end table-info">3. Bill (Invoiced)</th>
                        <th colspan="2" class="text-center table-warning">Variances</th>
                        <th rowspan="2" class="text-center align-middle">Status</th>
                    </tr>
                    <tr>
                        <th class="text-center border-start small">Ordered Qty</th>
                        <th class="text-end small">Agreed Unit £</th>
                        <th class="text-center border-end small">Received Qty</th>
                        <th class="text-center small">Billed Qty</th>
                        <th class="text-end border-end small">Billed Unit £</th>
                        <th class="text-center small">Qty Diff</th>
                        <th class="text-center small">Price Diff %</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($supplierBill->items as $item)
                    @php
                        $hasQtyDiff = $item->billed_quantity > $item->grn_received_quantity;
                        $hasPriceDiff = $item->price_variance_percentage > 5.0;
                    @endphp
                    <tr>
                        <td><strong class="text-dark">{{ $item->description }}</strong></td>
                        
                        <!-- PO values -->
                        <td class="text-center border-start tabular-nums">{{ number_format($item->po_quantity, 2) }}</td>
                        <td class="text-end border-end tabular-nums">£{{ number_format($item->po_unit_price, 2) }}</td>
                        
                        <!-- GRN values -->
                        <td class="text-center border-end fw-bold text-success tabular-nums">{{ number_format($item->grn_received_quantity, 2) }}</td>
                        
                        <!-- Bill values -->
                        <td class="text-center fw-bold tabular-nums {{ $hasQtyDiff ? 'text-danger bg-label-danger' : '' }}">{{ number_format($item->billed_quantity, 2) }}</td>
                        <td class="text-end border-end fw-bold tabular-nums {{ $hasPriceDiff ? 'text-danger bg-label-danger' : '' }}">£{{ number_format($item->billed_unit_price, 2) }}</td>
                        
                        <!-- Variances -->
                        <td class="text-center tabular-nums">
                            @if($item->quantity_variance > 0)
                                <span class="badge bg-label-danger">+{{ number_format($item->quantity_variance, 2) }} Unreceived</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center tabular-nums">
                            @if($item->price_variance_percentage > 0)
                                <span class="badge {{ $hasPriceDiff ? 'bg-label-danger' : 'bg-label-warning' }}">
                                    +{{ number_format($item->price_variance_percentage, 1) }}%
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>

                        <!-- Line Status -->
                        <td class="text-center">
                            @if($item->variance_type === 'none')
                                <span class="badge bg-success">
                                    <span class="status-indicator-dot dot-healthy me-1"></span> Matched
                                </span>
                            @else
                                <span class="badge bg-danger">
                                    <span class="status-indicator-dot dot-critical me-1"></span> Variance
                                </span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if($supplierBill->variance_notes)
    <div class="card mb-4">
        <div class="card-header border-bottom">
            <h6 class="card-title mb-0">Reconciliation & Audit Trail Log</h6>
        </div>
        <div class="card-body pt-3">
            <pre class="mb-0 bg-light p-3 rounded text-dark" style="white-space: pre-wrap; font-family: inherit;">{{ $supplierBill->variance_notes }}</pre>
        </div>
    </div>
    @endif
</div>

<!-- Commercial Manager Override Modal -->
<div class="modal fade" id="overrideModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('procurement.supplier-bills.override', $supplierBill) }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Commercial Manager Variance Override</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">
                    Applying an override will clear the commercial hold, approve this bill for accounting payment, and record your authorization into the immutable audit trail.
                </p>
                <div class="mb-3">
                    <label class="form-label required">Commercial Justification / Resolution Reason</label>
                    <textarea name="justification" class="form-control" rows="3" placeholder="e.g. Price increase was agreed via variation #4 due to urgent weekend delivery" required minlength="5"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-warning"><i class="bx bx-check-shield me-1"></i> Authorize & Release Payment</button>
            </div>
        </form>
    </div>
</div>
@endsection
