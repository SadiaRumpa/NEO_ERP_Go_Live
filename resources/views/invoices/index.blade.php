@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-1 mb-1"><span class="text-muted fw-light">Finance /</span> Invoices</h4>
            <p class="text-muted mb-0">Sales Invoices, UK CIS reverse charge calculations, and accounting synchronization.</p>
        </div>
        <div>
            <a href="{{ route('invoices.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Create Invoice
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
            <form method="GET" action="{{ route('invoices.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Customer</label>
                    <select name="customer_id" class="form-select">
                        <option value="">All Customers</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>
                                {{ $c->company_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="bx bx-filter-alt me-1"></i> Filter</button>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                </div>
            </form>
        </div>
        <div class="px-4 py-3 d-flex justify-content-between align-items-center flex-wrap gap-2 border-bottom">
            @include('partials.per-page-selector', ['paginator' => $invoices])
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Invoice #</th>
                        <th>Customer</th>
                        <th>Job Ref</th>
                        <th>Date</th>
                        <th>Due Date</th>
                        <th class="text-end">Subtotal (£)</th>
                        <th class="text-end">Net Payable (£)</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($invoices as $inv)
                    <tr>
                        <td>
                            <a href="{{ route('invoices.show', $inv->id) }}" class="fw-bold text-primary">
                                {{ $inv->invoice_number }}
                            </a>
                        </td>
                        <td>{{ $inv->customer->company_name ?? 'N/A' }}</td>
                        <td>
                            @if($inv->job)
                                <a href="{{ route('jobs.show', $inv->job_id) }}" class="badge bg-label-info">
                                    {{ $inv->job->job_name ?: ('Job #' . $inv->job_id) }}
                                </a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>{{ $inv->invoice_date->format('d/m/Y') }}</td>
                        <td>{{ $inv->due_date->format('d/m/Y') }}</td>
                        <td class="text-end fw-semibold">£{{ number_format($inv->subtotal, 2) }}</td>
                        <td class="text-end fw-bold text-primary">£{{ number_format($inv->net_payable, 2) }}</td>
                        <td>
                            @if($inv->status === 'paid')
                                <span class="badge bg-success">PAID</span>
                            @elseif($inv->status === 'sent')
                                <span class="badge bg-info">SENT</span>
                            @elseif($inv->status === 'overdue')
                                <span class="badge bg-danger">OVERDUE</span>
                            @else
                                <span class="badge bg-secondary">DRAFT</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="{{ route('invoices.show', $inv->id) }}">
                                        <i class="bx bx-show me-1"></i> View Details
                                    </a>
                                    <a class="dropdown-item" href="{{ route('invoices.pdf', $inv->id) }}">
                                        <i class="bx bxs-file-pdf me-1 text-danger"></i> Download PDF
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">
                            <i class="bx bx-receipt fs-2 mb-2 d-block"></i>
                            No invoices found. Generate one from a completed Job or create manually.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="text-muted small">
                Showing {{ $invoices->firstItem() ?? 0 }} to {{ $invoices->lastItem() ?? 0 }} of {{ $invoices->total() }} results
            </div>
            <div>
                {{ $invoices->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
