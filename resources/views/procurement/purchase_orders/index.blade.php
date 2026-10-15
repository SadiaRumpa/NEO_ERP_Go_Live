@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-1 mb-1"><span class="text-muted fw-light">Procurement /</span> Purchase Orders</h4>
            <p class="text-muted mb-0">Project material orders, warehouse stock requisitions, and committed cost tracking.</p>
        </div>
        <div>
            <a href="{{ route('procurement.purchase-orders.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Create Purchase Order
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card">
        <div class="card-header border-bottom">
            <form method="GET" action="{{ route('procurement.purchase-orders.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Supplier</label>
                    <select name="supplier_id" class="form-select">
                        <option value="">All Suppliers</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}" {{ request('supplier_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="partially_received" {{ request('status') == 'partially_received' ? 'selected' : '' }}>Partially Received</option>
                        <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>Fully Received</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-5 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bx bx-filter-alt me-1"></i> Filter</button>
                    <a href="{{ route('procurement.purchase-orders.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>PO Number</th>
                        <th>Supplier</th>
                        <th>Linked Job / Warehouse</th>
                        <th>Order Date</th>
                        <th>Total (£)</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($purchaseOrders as $po)
                    <tr>
                        <td>
                            <a href="{{ route('procurement.purchase-orders.show', $po) }}" class="fw-bold text-primary font-monospace">
                                {{ $po->po_number }}
                            </a>
                        </td>
                        <td><strong class="text-dark">{{ $po->supplier->name ?? '—' }}</strong></td>
                        <td>
                            @if($po->job)
                                <span class="badge bg-label-secondary"><i class="bx bx-briefcase me-1"></i> Job #{{ $po->job->id }} &bull; {{ Str::limit($po->job->job_title ?? $po->job->job_name, 22) }}</span>
                            @elseif($po->warehouse)
                                <span class="badge bg-label-info"><i class="bx bx-building me-1"></i> {{ $po->warehouse->warehouse_name ?? 'Warehouse' }}</span>
                            @else
                                <span class="text-muted small">Direct Expense</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-1">
                                <i class="bx bx-calendar text-muted"></i>
                                <span>{{ $po->order_date?->format('d M Y') }}</span>
                            </div>
                        </td>
                        <td class="fw-bold tabular-nums text-dark fs-6">£{{ number_format($po->total_amount, 2) }}</td>
                        <td>
                            @php
                                $badgeClass = match($po->status) {
                                    'draft' => 'bg-label-secondary',
                                    'sent' => 'bg-label-primary',
                                    'partially_received' => 'bg-label-warning',
                                    'received' => 'bg-label-success',
                                    'cancelled' => 'bg-label-danger',
                                    default => 'bg-label-secondary'
                                };
                                $statusDot = match($po->status) {
                                    'received' => 'dot-healthy',
                                    'partially_received', 'sent' => 'dot-warning',
                                    'cancelled' => 'dot-critical',
                                    default => ''
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">
                                @if($statusDot)<span class="status-indicator-dot {{ $statusDot }} me-1"></span>@endif
                                {{ ucfirst(str_replace('_', ' ', $po->status)) }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('procurement.purchase-orders.show', $po) }}" class="btn btn-sm btn-icon btn-outline-info" title="View Details">
                                    <i class="bx bx-show"></i>
                                </a>
                                @if(in_array($po->status, ['sent', 'partially_received']))
                                    <a href="{{ route('procurement.grns.create', ['purchase_order_id' => $po->id]) }}" class="btn btn-sm btn-outline-success" title="Receive Goods">
                                        <i class="bx bx-package me-1"></i> Receive
                                    </a>
                                    <a href="{{ route('procurement.supplier-bills.create', ['purchase_order_id' => $po->id]) }}" class="btn btn-sm btn-outline-primary" title="Enter Bill">
                                        <i class="bx bx-receipt me-1"></i> Bill
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="bx bx-cart fs-1 d-block mb-2"></i>
                            No Purchase Orders found. Click "Create Purchase Order" to raise one.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($purchaseOrders->hasPages())
        <div class="card-footer">
            {{ $purchaseOrders->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
