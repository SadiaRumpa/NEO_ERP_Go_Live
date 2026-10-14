@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-1 mb-1">
                <span class="text-muted fw-light">Procurement / Purchase Orders /</span> {{ $purchaseOrder->po_number }}
            </h4>
            <p class="text-muted mb-0">Raised on {{ $purchaseOrder->order_date?->format('d M Y') }} by {{ $purchaseOrder->creator->name ?? 'System' }}</p>
        </div>
        <div class="d-flex gap-2">
            @if($purchaseOrder->status === 'draft')
                <form method="POST" action="{{ route('procurement.purchase-orders.send', $purchaseOrder) }}">
                    @csrf
                    <button type="submit" class="btn btn-primary"><i class="bx bx-paper-plane me-1"></i> Send to Supplier</button>
                </form>
            @endif

            @if(in_array($purchaseOrder->status, ['sent', 'partially_received']))
                <a href="{{ route('procurement.grns.create', ['purchase_order_id' => $purchaseOrder->id]) }}" class="btn btn-success">
                    <i class="bx bx-package me-1"></i> Log Goods Receipt (GRN)
                </a>
                <a href="{{ route('procurement.supplier-bills.create', ['purchase_order_id' => $purchaseOrder->id]) }}" class="btn btn-outline-primary">
                    <i class="bx bx-receipt me-1"></i> Enter Supplier Bill
                </a>
            @endif

            @if(in_array($purchaseOrder->status, ['draft', 'sent']))
                <form method="POST" action="{{ route('procurement.purchase-orders.cancel', $purchaseOrder) }}" onsubmit="return confirm('Cancel this purchase order?');">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger"><i class="bx bx-x me-1"></i> Cancel Order</button>
                </form>
            @endif
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="row">
        <!-- Overview Cards -->
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="card-subtitle text-muted mb-2">Supplier & Account</h6>
                    <h5 class="card-title fw-bold text-primary mb-1">{{ $purchaseOrder->supplier->name }}</h5>
                    <p class="mb-1 text-muted">{{ $purchaseOrder->supplier->address ?? 'No address registered' }}</p>
                    <div class="small mt-2">
                        <div><strong>Contact:</strong> {{ $purchaseOrder->supplier->contact_name ?? '—' }}</div>
                        <div><strong>Email:</strong> {{ $purchaseOrder->supplier->email ?? '—' }}</div>
                        <div><strong>Phone:</strong> {{ $purchaseOrder->supplier->phone ?? '—' }}</div>
                        <div><strong>Terms:</strong> <span class="badge bg-label-info">{{ $purchaseOrder->supplier->payment_terms }}</span></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="card-subtitle text-muted mb-2">Cost Allocation & Destination</h6>
                    @if($purchaseOrder->job)
                        <div class="alert alert-primary mb-2 py-2">
                            <i class="bx bx-briefcase me-1"></i>
                            <strong>Allocated to Job #{{ $purchaseOrder->job->id }}</strong>
                            <div class="small">{{ $purchaseOrder->job->job_title ?? $purchaseOrder->job->job_name }}</div>
                        </div>
                    @elseif($purchaseOrder->warehouse)
                        <div class="alert alert-info mb-2 py-2">
                            <i class="bx bx-building me-1"></i>
                            <strong>Central Inventory: {{ $purchaseOrder->warehouse->warehouse_name }}</strong>
                        </div>
                    @else
                        <span class="text-muted">Direct Expense / Unallocated</span>
                    @endif
                    <div class="small mt-2">
                        <div><strong>Expected Delivery:</strong> {{ $purchaseOrder->expected_delivery_date?->format('d M Y') ?? 'Not specified' }}</div>
                        <div><strong>Special Notes:</strong> {{ $purchaseOrder->notes ?? 'None' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="card-subtitle text-muted mb-2">Financial Commitment & Status</h6>
                    @php
                        $badgeClass = match($purchaseOrder->status) {
                            'draft' => 'bg-label-secondary',
                            'sent' => 'bg-label-primary',
                            'partially_received' => 'bg-label-warning',
                            'received' => 'bg-label-success',
                            'cancelled' => 'bg-label-danger',
                            default => 'bg-label-secondary'
                        };
                    @endphp
                    <div class="mb-3">
                        <span class="badge {{ $badgeClass }} fs-6">{{ ucfirst(str_replace('_', ' ', $purchaseOrder->status)) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Subtotal:</span>
                        <span class="fw-bold">£{{ number_format($purchaseOrder->subtotal, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">VAT ({{ number_format($purchaseOrder->tax_rate, 0) }}%):</span>
                        <span class="fw-bold">£{{ number_format($purchaseOrder->tax_amount, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between border-top pt-2">
                        <span class="fw-bold fs-5">Total Committed:</span>
                        <span class="fw-bold fs-5 text-primary">£{{ number_format($purchaseOrder->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Line Items Table -->
    <div class="card mb-4">
        <div class="card-header border-bottom">
            <h5 class="card-title mb-0">Ordered Items & Receipt Progress</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Item Description</th>
                        <th class="text-center">Ordered Qty</th>
                        <th class="text-center">Received Qty</th>
                        <th style="width: 25%;">Delivery Progress</th>
                        <th class="text-end">Agreed Price</th>
                        <th class="text-end">Total (£)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchaseOrder->items as $item)
                    @php
                        $pct = $item->quantity > 0 ? min(100, round(($item->received_quantity / $item->quantity) * 100)) : 0;
                        $barClass = $pct >= 100 ? 'bg-success' : ($pct > 0 ? 'bg-warning' : 'bg-secondary');
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $item->description }}</strong>
                            @if($item->stockItem)
                                <div><small class="text-muted">SKU: {{ $item->stockItem->item_name }}</small></div>
                            @endif
                        </td>
                        <td class="text-center fw-bold">{{ number_format($item->quantity, 2) }}</td>
                        <td class="text-center fw-bold">{{ number_format($item->received_quantity, 2) }}</td>
                        <td class="align-middle">
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height: 8px;">
                                    <div class="progress-bar {{ $barClass }}" role="progressbar" style="width: {{ $pct }}%;" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <small class="fw-bold">{{ $pct }}%</small>
                            </div>
                        </td>
                        <td class="text-end">£{{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-end fw-bold">£{{ number_format($item->total_price, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Associated Goods Received Notes (GRNs) & Supplier Bills -->
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="bx bx-package me-1 text-success"></i> Goods Received Notes (GRNs)</h5>
                    @if(in_array($purchaseOrder->status, ['sent', 'partially_received']))
                        <a href="{{ route('procurement.grns.create', ['purchase_order_id' => $purchaseOrder->id]) }}" class="btn btn-xs btn-outline-success">Log GRN</a>
                    @endif
                </div>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>GRN Number</th>
                                <th>Date</th>
                                <th>Delivery Note Ref</th>
                                <th>Receiver</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($purchaseOrder->grns as $grn)
                            <tr>
                                <td>
                                    <a href="{{ route('procurement.grns.show', $grn) }}" class="fw-bold text-success">
                                        {{ $grn->grn_number }}
                                    </a>
                                </td>
                                <td>{{ $grn->received_date?->format('d M Y') }}</td>
                                <td>{{ $grn->delivery_note_number ?? '—' }}</td>
                                <td>{{ $grn->receiver->name ?? '—' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-3 text-muted">No goods receipts logged yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="bx bx-receipt me-1 text-primary"></i> 3-Way Match Supplier Bills</h5>
                    @if(in_array($purchaseOrder->status, ['sent', 'partially_received', 'received']))
                        <a href="{{ route('procurement.supplier-bills.create', ['purchase_order_id' => $purchaseOrder->id]) }}" class="btn btn-xs btn-outline-primary">Enter Bill</a>
                    @endif
                </div>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Bill Ref</th>
                                <th>Supplier Inv #</th>
                                <th>Total</th>
                                <th>Match Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($purchaseOrder->bills as $bill)
                            <tr>
                                <td>
                                    <a href="{{ route('procurement.supplier-bills.show', $bill) }}" class="fw-bold text-primary">
                                        {{ $bill->internal_reference }}
                                    </a>
                                </td>
                                <td>{{ $bill->bill_number }}</td>
                                <td class="fw-bold">£{{ number_format($bill->total_amount, 2) }}</td>
                                <td>
                                    @php
                                        $matchBadge = match($bill->match_status) {
                                            'matched' => 'bg-label-success',
                                            'variance_detected' => 'bg-label-danger',
                                            'approved_override' => 'bg-label-warning',
                                            default => 'bg-label-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $matchBadge }}">{{ ucfirst(str_replace('_', ' ', $bill->match_status)) }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-3 text-muted">No supplier bills matched yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
