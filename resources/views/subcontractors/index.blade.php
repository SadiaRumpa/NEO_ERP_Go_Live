@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-1 mb-1"><span class="text-muted fw-light">Commercial & Labor /</span> Subcontractors</h4>
            <p class="text-muted mb-0">HMRC Construction Industry Scheme (CIS) register, UTR verification, and tax deduction tracking.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('subcontractors.cis-monthly-return') }}" class="btn btn-outline-primary">
                <i class="bx bx-file me-1"></i> HMRC CIS300 Return
            </a>
            <a href="{{ route('subcontractors.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Add Subcontractor
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
            <form method="GET" action="{{ route('subcontractors.index') }}" class="row g-3">
                <div class="col-md-5">
                    <input type="text" name="search" class="form-control" placeholder="Search by name, UTR number, contact..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="cis_tax_status" class="form-select">
                        <option value="">All CIS Tax Statuses</option>
                        <option value="gross" {{ request('cis_tax_status') == 'gross' ? 'selected' : '' }}>Gross (0% Deduction)</option>
                        <option value="standard" {{ request('cis_tax_status') == 'standard' ? 'selected' : '' }}>Standard (20% Deduction)</option>
                        <option value="higher" {{ request('cis_tax_status') == 'higher' ? 'selected' : '' }}>Higher (30% Deduction)</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bx bx-filter-alt me-1"></i> Filter</button>
                    <a href="{{ route('subcontractors.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Company Name</th>
                        <th>HMRC UTR Number</th>
                        <th>Specialism</th>
                        <th>CIS Tax Status</th>
                        <th>HMRC Verification Ref</th>
                        <th>Certificates</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($subcontractors as $sub)
                    <tr>
                        <td>
                            <a href="{{ route('subcontractors.show', $sub) }}" class="fw-bold text-primary">
                                {{ $sub->company_name }}
                            </a>
                            @if($sub->contact_name)
                                <div class="small text-muted">{{ $sub->contact_name }} • {{ $sub->phone }}</div>
                            @endif
                        </td>
                        <td><code>{{ $sub->utr_number }}</code></td>
                        <td>{{ $sub->trade_specialism ?? 'General Trades' }}</td>
                        <td>
                            @if($sub->cis_tax_status === 'gross')
                                <span class="badge bg-label-success">Gross (0%)</span>
                            @elseif($sub->cis_tax_status === 'standard')
                                <span class="badge bg-label-primary">Standard (20%)</span>
                            @else
                                <span class="badge bg-label-danger">Higher (30%)</span>
                            @endif
                        </td>
                        <td>
                            @if($sub->cis_verification_number)
                                <span class="badge bg-label-info">{{ $sub->cis_verification_number }}</span>
                            @else
                                <span class="text-muted small">Pending Verification</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-label-secondary">{{ $sub->payment_certificates_count }} certs</span>
                        </td>
                        <td>
                            @if($sub->status === 'active')
                                <span class="badge bg-label-success">Active</span>
                            @else
                                <span class="badge bg-label-secondary">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('subcontractors.show', $sub) }}" class="btn btn-sm btn-icon btn-outline-info" title="View Profile">
                                    <i class="bx bx-show"></i>
                                </a>
                                <a href="{{ route('subcontractors.certificates.create', ['subcontractor_id' => $sub->id]) }}" class="btn btn-sm btn-outline-success" title="Raise Payment Cert">
                                    <i class="bx bx-receipt me-1"></i> Cert
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            <i class="bx bx-hard-hat fs-1 d-block mb-2"></i>
                            No subcontractors registered yet. Click "Add Subcontractor" to register one with HMRC CIS tax status.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($subcontractors->hasPages())
        <div class="card-footer">
            {{ $subcontractors->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
