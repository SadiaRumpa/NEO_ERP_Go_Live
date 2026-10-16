@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-1 mb-1"><span class="text-muted fw-light">Procurement /</span> Suppliers</h4>
            <p class="text-muted mb-0">Approved vendors, contact profiles, trade accounts, and payment terms.</p>
        </div>
        <div>
            <a href="{{ route('procurement.suppliers.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Add Supplier
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
            <form method="GET" action="{{ route('procurement.suppliers.index') }}" class="row g-3">
                <div class="col-md-5">
                    <input type="text" name="search" class="form-control" placeholder="Search by name, code, contact, email..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bx bx-filter-alt me-1"></i> Filter</button>
                    <a href="{{ route('procurement.suppliers.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
        <div class="px-4 py-3 d-flex justify-content-between align-items-center flex-wrap gap-2 border-bottom">
            @include('partials.per-page-selector', ['paginator' => $suppliers])
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Supplier</th>
                        <th>Code</th>
                        <th>Contact</th>
                        <th>Payment Terms</th>
                        <th>VAT / Tax ID</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($suppliers as $supplier)
                    <tr>
                        <td>
                            <strong>{{ $supplier->name }}</strong>
                            @if($supplier->address)
                                <div class="text-muted small text-truncate" style="max-width: 200px;">{{ $supplier->address }}</div>
                            @endif
                        </td>
                        <td><code>{{ $supplier->code ?? 'N/A' }}</code></td>
                        <td>
                            <div>{{ $supplier->contact_name ?? '—' }}</div>
                            <small class="text-muted">{{ $supplier->email }} {{ $supplier->phone ? '• ' . $supplier->phone : '' }}</small>
                        </td>
                        <td><span class="badge bg-label-info">{{ $supplier->payment_terms }}</span></td>
                        <td>{{ $supplier->tax_number ?? '—' }}</td>
                        <td>
                            @if($supplier->status === 'active')
                                <span class="badge bg-label-success">Active</span>
                            @else
                                <span class="badge bg-label-secondary">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('procurement.suppliers.edit', $supplier) }}" class="btn btn-sm btn-icon btn-outline-primary" title="Edit Supplier">
                                <i class="bx bx-edit-alt"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="bx bx-store-alt fs-1 d-block mb-2"></i>
                            No suppliers found. Click "Add Supplier" to register one.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="text-muted small">
                Showing {{ $suppliers->firstItem() ?? 0 }} to {{ $suppliers->lastItem() ?? 0 }} of {{ $suppliers->total() }} results
            </div>
            <div>
                {{ $suppliers->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
