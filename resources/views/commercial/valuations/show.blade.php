@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-1 mb-1">
                <span class="text-muted fw-light">Commercial /</span> Application for Payment {{ $valuation->application_number }}
            </h4>
            <p class="text-muted mb-0">Job #{{ $valuation->job_id }} &bull; Period Ending {{ $valuation->period_end->format('d M Y') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('commercial.valuations.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Register
            </a>
            <button type="button" class="btn btn-outline-primary" onclick="window.print();">
                <i class="bx bx-printer me-1"></i> Print Application
            </button>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="row">
        <!-- Main Valuation Schedule -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center border-bottom">
                    <h5 class="card-title mb-0">Valuation Breakdown Schedule</h5>
                    @if($valuation->status === 'paid')
                        <span class="badge bg-success fs-6">PAID</span>
                    @elseif($valuation->status === 'certified')
                        <span class="badge bg-info fs-6">CERTIFIED</span>
                    @else
                        <span class="badge bg-secondary fs-6">DRAFT APPLICATION</span>
                    @endif
                </div>
                <div class="card-body pt-3">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <td class="fw-semibold" style="width: 65%;">1. Cumulative Works Completed to Date</td>
                                    <td class="text-end fw-bold">£{{ number_format($valuation->cumulative_work_value, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">2. Materials Properly Stored on Site (MOS)</td>
                                    <td class="text-end fw-bold">£{{ number_format($valuation->materials_on_site_value, 2) }}</td>
                                </tr>
                                <tr class="table-primary">
                                    <td class="fw-bold">3. Total Gross Valuation to Date (1 + 2)</td>
                                    <td class="text-end fw-bold fs-6 text-primary">£{{ number_format($valuation->gross_valuation, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">4. Less: Previously Certified Gross Valuation</td>
                                    <td class="text-end text-muted">-£{{ number_format($valuation->previous_certified_amount, 2) }}</td>
                                </tr>
                                <tr class="table-light">
                                    <td class="fw-bold">5. Current Period Gross Claim (3 - 4)</td>
                                    <td class="text-end fw-bold">£{{ number_format($valuation->current_gross_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-danger">
                                        6. Less: Retention Withheld ({{ number_format($valuation->retention_rate, 1) }}%)
                                        @if($valuation->retention)
                                        <div class="small text-muted">Ledger Ref: <code>{{ $valuation->retention->retention_reference }}</code></div>
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold text-danger">-£{{ number_format($valuation->retention_deduction, 2) }}</td>
                                </tr>
                                <tr class="table-success">
                                    <td class="fw-bold fs-5 text-success">7. Net Amount Claimed This Application (5 - 6)</td>
                                    <td class="text-end fw-bold fs-5 text-success">£{{ number_format($valuation->net_claim_amount, 2) }}</td>
                                </tr>
                                @if($valuation->certified_amount !== null)
                                <tr class="table-info">
                                    <td class="fw-bold fs-5 text-info">8. Certified Payment Sum (Employer's Agent Certificate)</td>
                                    <td class="text-end fw-bold fs-5 text-info">£{{ number_format($valuation->certified_amount, 2) }}</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    @if($valuation->notes)
                    <div class="mt-4 border-top pt-3">
                        <h6 class="fw-bold mb-1">Commercial Notes</h6>
                        <p class="text-secondary mb-0" style="white-space: pre-line;">{{ $valuation->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar Actions & Certification -->
        <div class="col-lg-4">
            <!-- Certification Card -->
            @if($valuation->status === 'draft')
            <div class="card mb-4 border-info">
                <div class="card-header bg-label-info border-bottom">
                    <h5 class="card-title text-info mb-0"><i class="bx bx-check-shield me-1"></i> Certify Payment</h5>
                </div>
                <div class="card-body pt-3">
                    <p class="small text-muted mb-3">
                        Certify this application for payment. You can adjust the certified sum if deductions apply.
                    </p>
                    <form method="POST" action="{{ route('commercial.valuations.certify', $valuation) }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label required">Certified Sum (£)</label>
                            <input type="number" step="0.01" name="certified_amount" class="form-control" value="{{ $valuation->net_claim_amount }}" required>
                        </div>
                        <button type="submit" class="btn btn-info w-100">
                            <i class="bx bx-certification me-1"></i> Authorize Certificate
                        </button>
                    </form>
                </div>
            </div>
            @elseif($valuation->status === 'certified')
            <div class="card mb-4 border-success">
                <div class="card-header bg-label-success border-bottom">
                    <h5 class="card-title text-success mb-0"><i class="bx bx-money me-1"></i> Payment Settlement</h5>
                </div>
                <div class="card-body pt-3">
                    <div class="mb-2">
                        <small class="text-muted d-block">Certified Amount</small>
                        <span class="fs-4 fw-bold text-success">£{{ number_format($valuation->certified_amount, 2) }}</span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Certified At</small>
                        <span>{{ $valuation->certified_at ? $valuation->certified_at->format('d M Y H:i') : 'N/A' }}</span>
                    </div>
                    <form method="POST" action="{{ route('commercial.valuations.mark-paid', $valuation) }}">
                        @csrf
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bx bx-check-double me-1"></i> Mark Client Payment Received
                        </button>
                    </form>
                </div>
            </div>
            @else
            <div class="card mb-4 border-success">
                <div class="card-body text-center">
                    <i class="bx bx-check-circle text-success fs-1"></i>
                    <h5 class="fw-bold mt-2 mb-1">Claim Paid & Reconciled</h5>
                    <p class="text-muted small mb-0">Funds received on {{ $valuation->paid_at ? $valuation->paid_at->format('d M Y') : 'N/A' }}.</p>
                </div>
            </div>
            @endif

            <!-- Retention Link Card -->
            @if($valuation->retention)
            <div class="card bg-label-warning border-warning">
                <div class="card-body">
                    <h6 class="card-subtitle text-warning mb-1 fw-bold">Retention Withholding</h6>
                    <h3 class="card-title fw-bold mb-0 text-warning">£{{ number_format($valuation->retention->total_retained_amount, 2) }}</h3>
                    <div class="small text-muted mt-2">
                        Ref: <code>{{ $valuation->retention->retention_reference }}</code>
                        <div class="mt-1">50% release on Practical Completion</div>
                        <div>50% release on Defects Expiry (12m)</div>
                    </div>
                    <a href="{{ route('commercial.retentions.index') }}" class="btn btn-sm btn-outline-warning mt-3 w-100">
                        View Retention Ledger
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
