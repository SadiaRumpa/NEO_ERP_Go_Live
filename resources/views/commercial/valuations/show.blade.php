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
            <div class="card mb-4 shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center border-bottom py-3">
                    <div>
                        <h5 class="card-title mb-0 d-flex align-items-center gap-2">
                            <i class="bx bx-spreadsheet text-primary"></i> Valuation Breakdown Schedule
                        </h5>
                        <small class="text-muted">Interim payment application compliant with JCT / NEC contractual terms</small>
                    </div>
                    @if($valuation->status === 'paid')
                        <span class="badge bg-success fs-6 px-3 py-2">
                            <i class="bx bx-check-double me-1"></i> PAID
                        </span>
                    @elseif($valuation->status === 'certified')
                        <span class="badge bg-label-info fs-6 px-3 py-2">
                            <i class="bx bx-check-shield me-1"></i> CERTIFIED
                        </span>
                    @else
                        <span class="badge bg-label-warning fs-6 px-3 py-2">
                            <span class="status-indicator-dot dot-warning me-1"></span> DRAFT APPLICATION
                        </span>
                    @endif
                </div>
                <div class="card-body pt-3">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <tbody>
                                <tr>
                                    <td class="fw-semibold py-3" style="width: 65%;">
                                        <span class="badge bg-label-secondary me-2">1</span> Cumulative Works Completed to Date
                                    </td>
                                    <td class="text-end fw-bold tabular-nums fs-6">£{{ number_format($valuation->cumulative_work_value, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold py-3">
                                        <span class="badge bg-label-secondary me-2">2</span> Materials Properly Stored on Site (MOS)
                                    </td>
                                    <td class="text-end fw-bold tabular-nums fs-6">£{{ number_format($valuation->materials_on_site_value, 2) }}</td>
                                </tr>
                                <tr style="background-color: #f0f7ff;">
                                    <td class="fw-bold py-3 text-primary">
                                        <span class="badge bg-primary me-2">3</span> Total Gross Valuation to Date (1 + 2)
                                    </td>
                                    <td class="text-end fw-bold fs-5 text-primary tabular-nums">£{{ number_format($valuation->gross_valuation, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold py-3 text-muted">
                                        <span class="badge bg-label-secondary me-2">4</span> Less: Previously Certified Gross Valuation
                                    </td>
                                    <td class="text-end text-muted tabular-nums fs-6">-£{{ number_format($valuation->previous_certified_amount, 2) }}</td>
                                </tr>
                                <tr style="background-color: #f8fafc;">
                                    <td class="fw-bold py-3">
                                        <span class="badge bg-label-dark me-2">5</span> Current Period Gross Claim (3 - 4)
                                    </td>
                                    <td class="text-end fw-bold fs-6 tabular-nums">£{{ number_format($valuation->current_gross_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold py-3 text-danger">
                                        <span class="badge bg-label-danger me-2">6</span> Less: Retention Withheld ({{ number_format($valuation->retention_rate, 1) }}%)
                                        @if($valuation->retention)
                                        <div class="small text-muted mt-1 ms-4 ps-1 font-monospace">Escrow Ledger Ref: {{ $valuation->retention->retention_reference }}</div>
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold text-danger tabular-nums fs-6">-£{{ number_format($valuation->retention_deduction, 2) }}</td>
                                </tr>
                                <tr style="background-color: #ecfdf5; border: 2px solid #10b981;">
                                    <td class="fw-bold py-3 fs-5 text-success">
                                        <span class="badge bg-success me-2">7</span> Net Amount Claimed This Application (5 - 6)
                                    </td>
                                    <td class="text-end fw-bold fs-4 text-success tabular-nums">£{{ number_format($valuation->net_claim_amount, 2) }}</td>
                                </tr>
                                @if($valuation->certified_amount !== null)
                                <tr style="background-color: #ecfeff; border: 2px solid #06b6d4;">
                                    <td class="fw-bold py-3 fs-5 text-info">
                                        <span class="badge bg-info me-2">8</span> Certified Payment Sum (Employer's Agent Certificate)
                                    </td>
                                    <td class="text-end fw-bold fs-4 text-info tabular-nums">£{{ number_format($valuation->certified_amount, 2) }}</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    @if($valuation->notes)
                    <div class="mt-4 border-top pt-3">
                        <h6 class="fw-bold mb-1 text-dark"><i class="bx bx-note me-1"></i> Commercial Notes & Contract Qualifications</h6>
                        <div class="p-3 border rounded bg-light text-secondary" style="white-space: pre-line;">{{ $valuation->notes }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar Actions & Certification -->
        <div class="col-lg-4">
            <!-- Certification Card -->
            @if($valuation->status === 'draft')
            <div class="card mb-4 border-info shadow-sm">
                <div class="card-header bg-label-info border-bottom py-3">
                    <h5 class="card-title text-info mb-0 d-flex align-items-center gap-2">
                        <i class="bx bx-check-shield"></i> Certify Payment
                    </h5>
                </div>
                <div class="card-body pt-3">
                    <p class="small text-muted mb-3">
                        Certify this application for payment. You can adjust the certified sum if deductions apply.
                    </p>
                    <form method="POST" action="{{ route('commercial.valuations.certify', $valuation) }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label required">Certified Sum (£)</label>
                            <input type="number" step="0.01" name="certified_amount" class="form-control form-control-lg fw-bold text-dark tabular-nums" value="{{ $valuation->net_claim_amount }}" required>
                        </div>
                        <button type="submit" class="btn btn-info w-100 shadow-sm">
                            <i class="bx bx-certification me-1"></i> Authorize Certificate
                        </button>
                    </form>
                </div>
            </div>
            @elseif($valuation->status === 'certified')
            <div class="card mb-4 border-success shadow-sm">
                <div class="card-header bg-label-success border-bottom py-3">
                    <h5 class="card-title text-success mb-0 d-flex align-items-center gap-2">
                        <i class="bx bx-money"></i> Payment Settlement
                    </h5>
                </div>
                <div class="card-body pt-3">
                    <div class="p-3 border rounded bg-light mb-3">
                        <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.7rem;">Certified Amount</small>
                        <span class="fs-3 fw-bold text-success tabular-nums">£{{ number_format($valuation->certified_amount, 2) }}</span>
                        <div class="small text-muted mt-1">
                            <i class="bx bx-time me-1"></i>Certified: {{ $valuation->certified_at ? $valuation->certified_at->format('d M Y H:i') : 'N/A' }}
                        </div>
                    </div>
                    <form method="POST" action="{{ route('commercial.valuations.mark-paid', $valuation) }}">
                        @csrf
                        <button type="submit" class="btn btn-success w-100 shadow-sm">
                            <i class="bx bx-check-double me-1"></i> Mark Client Payment Received
                        </button>
                    </form>
                </div>
            </div>
            @else
            <div class="card mb-4 border-success shadow-sm">
                <div class="card-body text-center py-4">
                    <i class="bx bx-check-circle text-success fs-1"></i>
                    <h5 class="fw-bold mt-2 mb-1">Claim Paid & Reconciled</h5>
                    <p class="text-muted small mb-0">Funds received on {{ $valuation->paid_at ? $valuation->paid_at->format('d M Y') : 'N/A' }}.</p>
                </div>
            </div>
            @endif

            <!-- Retention Link Card -->
            @if($valuation->retention)
            <div class="neo-kpi-card kpi-warning shadow-sm">
                <div class="kpi-subtitle">Retention Withholding In Escrow</div>
                <div class="kpi-value text-warning tabular-nums">£{{ number_format($valuation->retention->total_retained_amount, 2) }}</div>
                <div class="kpi-footnote">
                    <div class="font-monospace">Ref: {{ $valuation->retention->retention_reference }}</div>
                    <div class="mt-1"><i class="bx bx-check me-1"></i>50% on Practical Completion</div>
                    <div><i class="bx bx-check me-1"></i>50% on Defects Expiry (12m)</div>
                </div>
                <a href="{{ route('commercial.retentions.index') }}" class="btn btn-sm btn-outline-warning mt-3 w-100">
                    <i class="bx bx-book-open me-1"></i> View Retention Ledger
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
