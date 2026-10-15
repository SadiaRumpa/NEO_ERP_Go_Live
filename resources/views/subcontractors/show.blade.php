@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-1 mb-1">
                <span class="text-muted fw-light">Subcontractors /</span> {{ $subcontractor->company_name }}
            </h4>
            <p class="text-muted mb-0">HMRC UTR: <strong>{{ $subcontractor->utr_number }}</strong> • Specialism: <strong>{{ $subcontractor->trade_specialism ?? 'Contractor' }}</strong></p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('subcontractors.edit', $subcontractor) }}" class="btn btn-outline-secondary">
                <i class="bx bx-edit-alt me-1"></i> Edit Profile
            </a>
            <a href="{{ route('subcontractors.certificates.create', ['subcontractor_id' => $subcontractor->id]) }}" class="btn btn-success">
                <i class="bx bx-receipt me-1"></i> Raise Payment Certificate
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="row">
        <!-- Subcontractor Profile -->
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="card-subtitle text-muted mb-2">Company & Contact</h6>
                    <h5 class="card-title fw-bold text-primary mb-1">{{ $subcontractor->company_name }}</h5>
                    @if($subcontractor->trading_name)
                        <div class="text-muted small mb-2">t/a {{ $subcontractor->trading_name }}</div>
                    @endif
                    <div class="small mt-3">
                        <div><strong>Contact:</strong> {{ $subcontractor->contact_name ?? '—' }}</div>
                        <div><strong>Email:</strong> {{ $subcontractor->email ?? '—' }}</div>
                        <div><strong>Phone:</strong> {{ $subcontractor->phone ?? '—' }}</div>
                        <div><strong>Address:</strong> {{ $subcontractor->address ?? '—' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CIS Status Card -->
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="card-subtitle text-muted mb-2">HMRC CIS Tax Compliance</h6>
                    <div class="mb-3">
                        @if($subcontractor->cis_tax_status === 'gross')
                            <span class="badge bg-success fs-6"><i class="bx bx-check-shield me-1"></i> Gross Payment (0% Deduction)</span>
                        @elseif($subcontractor->cis_tax_status === 'standard')
                            <span class="badge bg-primary fs-6"><i class="bx bx-shield me-1"></i> Standard CIS (20% Deduction)</span>
                        @else
                            <span class="badge bg-danger fs-6"><i class="bx bx-error-shield me-1"></i> Higher Rate (30% Deduction)</span>
                        @endif
                    </div>
                    <div class="small">
                        <div><strong>UTR Number:</strong> <code>{{ $subcontractor->utr_number }}</code></div>
                        <div><strong>Verification Ref:</strong> {{ $subcontractor->cis_verification_number ?? 'Pending' }}</div>
                        <div><strong>Verified Date:</strong> {{ $subcontractor->verified_at?->format('d M Y') ?? 'Unrecorded' }}</div>
                        <div><strong>Company Reg:</strong> {{ $subcontractor->company_registration_number ?? '—' }}</div>
                        <div><strong>NI Number:</strong> {{ $subcontractor->national_insurance_number ?? '—' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cumulative Summary -->
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="card-subtitle text-muted mb-2">Historical Spend & Withholding</h6>
                    @php
                        $totGross = $subcontractor->paymentCertificates->sum('gross_amount');
                        $totCis = $subcontractor->paymentCertificates->sum('cis_deduction_amount');
                        $totNet = $subcontractor->paymentCertificates->sum('net_payable');
                    @endphp
                    <div class="d-flex justify-content-between mb-1 small">
                        <span>Total Certified Gross:</span>
                        <span class="fw-bold">£{{ number_format($totGross, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1 small">
                        <span>Total CIS Tax Deducted:</span>
                        <span class="fw-bold text-danger">£{{ number_format($totCis, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between border-top pt-2">
                        <span class="fw-bold fs-6">Net Paid / Payable:</span>
                        <span class="fw-bold fs-5 text-success">£{{ number_format($totNet, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Certificates Table -->
    <div class="card">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Subcontractor Payment Certificates</h5>
            <a href="{{ route('subcontractors.certificates.create', ['subcontractor_id' => $subcontractor->id]) }}" class="btn btn-sm btn-outline-success">
                <i class="bx bx-plus me-1"></i> New Certificate
            </a>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Cert Number</th>
                        <th>Job</th>
                        <th>Period</th>
                        <th>Gross Claim</th>
                        <th>Materials (Exempt)</th>
                        <th>Labor (Subject to CIS)</th>
                        <th>CIS Tax Deducted</th>
                        <th>Net Payable</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subcontractor->paymentCertificates as $cert)
                    <tr>
                        <td>
                            <a href="{{ route('subcontractors.certificates.show', $cert) }}" class="fw-bold text-primary">
                                {{ $cert->certificate_number }}
                            </a>
                        </td>
                        <td>
                            <span class="badge bg-label-info">Job #{{ $cert->job->id }}</span>
                        </td>
                        <td>{{ $cert->period_start?->format('d/m/y') }} – {{ $cert->period_end?->format('d/m/y') }}</td>
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
                            <a href="{{ route('subcontractors.certificates.show', $cert) }}" class="btn btn-sm btn-icon btn-outline-info" title="View Certificate Statement">
                                <i class="bx bx-show"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-4 text-muted">
                            <i class="bx bx-receipt fs-1 d-block mb-2"></i>
                            No payment certificates issued for this subcontractor yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
