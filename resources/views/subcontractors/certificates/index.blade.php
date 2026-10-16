@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-1 mb-1"><span class="text-muted fw-light">Subcontractors /</span> Payment Certificates</h4>
            <p class="text-muted mb-0">Certified labor payments, materials exemptions, and statutory CIS withholding statements.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('subcontractors.cis-monthly-return') }}" class="btn btn-outline-primary">
                <i class="bx bx-file me-1"></i> HMRC CIS300 Return
            </a>
            <a href="{{ route('subcontractors.certificates.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> New Payment Certificate
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
            <form method="GET" action="{{ route('subcontractors.certificates.index') }}" class="row g-3">
                <div class="col-md-4">
                    <select name="subcontractor_id" class="form-select">
                        <option value="">All Subcontractors</option>
                        @foreach($subcontractors as $sub)
                            <option value="{{ $sub->id }}" {{ request('subcontractor_id') == $sub->id ? 'selected' : '' }}>
                                {{ $sub->company_name }} ({{ $sub->cis_tax_status }} - {{ $sub->cis_tax_rate }}%)
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="payment_status" class="form-select">
                        <option value="">All Payment Statuses</option>
                        <option value="certified" {{ request('payment_status') == 'certified' ? 'selected' : '' }}>Certified (Payable)</option>
                        <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="draft" {{ request('payment_status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    </select>
                </div>
                <div class="col-md-5 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bx bx-filter-alt me-1"></i> Filter</button>
                    <a href="{{ route('subcontractors.certificates.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
        <div class="px-4 py-3 d-flex justify-content-between align-items-center flex-wrap gap-2 border-bottom">
            @include('partials.per-page-selector', ['paginator' => $certificates])
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Cert Number</th>
                        <th>Subcontractor</th>
                        <th>Job</th>
                        <th>Period</th>
                        <th>Gross Claim</th>
                        <th>Materials</th>
                        <th>Labor</th>
                        <th>CIS Deducted</th>
                        <th>Net Payable</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($certificates as $cert)
                    <tr>
                        <td>
                            <a href="{{ route('subcontractors.certificates.show', $cert) }}" class="fw-bold text-primary">
                                {{ $cert->certificate_number }}
                            </a>
                        </td>
                        <td>
                            <strong>{{ $cert->subcontractor->company_name }}</strong>
                            <div class="small text-muted">UTR: {{ $cert->subcontractor->utr_number }}</div>
                        </td>
                        <td>
                            <span class="badge bg-label-info">Job #{{ $cert->job->id }}</span>
                        </td>
                        <td>{{ $cert->period_start?->format('d M') }} – {{ $cert->period_end?->format('d M Y') }}</td>
                        <td class="fw-bold">£{{ number_format($cert->gross_amount, 2) }}</td>
                        <td>£{{ number_format($cert->materials_cost, 2) }}</td>
                        <td>£{{ number_format($cert->labor_amount, 2) }}</td>
                        <td class="fw-bold text-danger">
                            -£{{ number_format($cert->cis_deduction_amount, 2) }}
                            <small class="text-muted">({{ number_format($cert->cis_tax_rate, 0) }}%)</small>
                        </td>
                        <td class="fw-bold text-success fs-6">£{{ number_format($cert->net_payable, 2) }}</td>
                        <td>
                            @if($cert->payment_status === 'paid')
                                <span class="badge bg-success">PAID</span>
                            @else
                                <span class="badge bg-primary">CERTIFIED</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('subcontractors.certificates.show', $cert) }}" class="btn btn-sm btn-icon btn-outline-info" title="View Certificate Voucher">
                                <i class="bx bx-show"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center py-4 text-muted">
                            <i class="bx bx-receipt fs-1 d-block mb-2"></i>
                            No Subcontractor Payment Certificates found. Click "New Payment Certificate" to generate one.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="text-muted small">
                Showing {{ $certificates->firstItem() ?? 0 }} to {{ $certificates->lastItem() ?? 0 }} of {{ $certificates->total() }} results
            </div>
            <div>
                {{ $certificates->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
