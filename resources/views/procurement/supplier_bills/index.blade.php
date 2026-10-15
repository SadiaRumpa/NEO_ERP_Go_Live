@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-1 mb-1"><span class="text-muted fw-light">Procurement /</span> Supplier Bills & 3-Way Matching</h4>
            <p class="text-muted mb-0">Automated 3-way reconciliation (PO $\leftrightarrow$ GRN $\leftrightarrow$ Bill), price variance tolerance, and payment approvals.</p>
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

    <div class="card">
        <div class="card-header border-bottom">
            <form method="GET" action="{{ route('procurement.supplier-bills.index') }}" class="row g-3">
                <div class="col-md-3">
                    <select name="match_status" class="form-select">
                        <option value="">All Match Statuses</option>
                        <option value="matched" {{ request('match_status') == 'matched' ? 'selected' : '' }}>Matched (Green)</option>
                        <option value="variance_detected" {{ request('match_status') == 'variance_detected' ? 'selected' : '' }}>Variance Detected (Red)</option>
                        <option value="approved_override" {{ request('match_status') == 'approved_override' ? 'selected' : '' }}>Commercial Override</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="approval_status" class="form-select">
                        <option value="">All Approval Statuses</option>
                        <option value="approved" {{ request('approval_status') == 'approved' ? 'selected' : '' }}>Approved for Payment</option>
                        <option value="held" {{ request('approval_status') == 'held' ? 'selected' : '' }}>On Commercial Hold</option>
                        <option value="pending" {{ request('approval_status') == 'pending' ? 'selected' : '' }}>Pending Audit</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bx bx-filter-alt me-1"></i> Filter</button>
                    <a href="{{ route('procurement.supplier-bills.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Internal Ref</th>
                        <th>Supplier Invoice #</th>
                        <th>Supplier</th>
                        <th>Purchase Order</th>
                        <th>Due Date</th>
                        <th>Total Amount</th>
                        <th>Match Status</th>
                        <th>Payment Approval</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($bills as $bill)
                    <tr>
                        <td>
                            <a href="{{ route('procurement.supplier-bills.show', $bill) }}" class="fw-bold text-primary font-monospace">
                                {{ $bill->internal_reference }}
                            </a>
                        </td>
                        <td><strong class="text-dark font-monospace">{{ $bill->bill_number }}</strong></td>
                        <td><span class="fw-semibold text-dark">{{ $bill->supplier->name ?? '—' }}</span></td>
                        <td>
                            <a href="{{ route('procurement.purchase-orders.show', $bill->purchaseOrder) }}" class="text-primary font-monospace">
                                {{ $bill->purchaseOrder->po_number }}
                            </a>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-1">
                                <i class="bx bx-calendar text-muted"></i>
                                <span>{{ $bill->due_date?->format('d M Y') }}</span>
                            </div>
                        </td>
                        <td class="fw-bold tabular-nums text-dark fs-6">£{{ number_format($bill->total_amount, 2) }}</td>
                        <td>
                            @php
                                $mClass = match($bill->match_status) {
                                    'matched' => 'bg-label-success',
                                    'variance_detected' => 'bg-label-danger',
                                    'approved_override' => 'bg-label-warning',
                                    default => 'bg-label-secondary'
                                };
                                $mDot = match($bill->match_status) {
                                    'matched' => 'dot-healthy',
                                    'variance_detected' => 'dot-critical',
                                    'approved_override' => 'dot-warning',
                                    default => ''
                                };
                            @endphp
                            <span class="badge {{ $mClass }}">
                                @if($mDot)<span class="status-indicator-dot {{ $mDot }} me-1"></span>@endif
                                {{ ucfirst(str_replace('_', ' ', $bill->match_status)) }}
                            </span>
                        </td>
                        <td>
                            @if($bill->approval_status === 'approved')
                                <span class="badge bg-success"><span class="status-indicator-dot dot-healthy me-1"></span> Approved</span>
                            @elseif($bill->approval_status === 'held')
                                <span class="badge bg-danger"><span class="status-indicator-dot dot-critical me-1"></span> ON HOLD</span>
                            @else
                                <span class="badge bg-secondary">Pending</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('procurement.supplier-bills.show', $bill) }}" class="btn btn-sm btn-icon btn-outline-info" title="3-Way Match Audit">
                                <i class="bx bx-check-shield"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">
                            <i class="bx bx-receipt fs-1 d-block mb-2"></i>
                            No Supplier Bills entered yet. Open a Purchase Order to enter a supplier bill for 3-way matching.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($bills->hasPages())
        <div class="card-footer">
            {{ $bills->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
