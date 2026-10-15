@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
        <div>
            <h4 class="fw-bold py-1 mb-1">
                <span class="text-muted fw-light">Subcontractors / Payment Certificates /</span> {{ $certificate->certificate_number }}
            </h4>
            <p class="text-muted mb-0">HMRC CIS Payment & Deduction Statement Voucher</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('subcontractors.certificates.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-left-arrow-alt me-1"></i> Back to Certificates
            </a>
            <button type="button" class="btn btn-outline-primary" onclick="window.print();">
                <i class="bx bx-printer me-1"></i> Print / Save PDF
            </button>
            @if($certificate->payment_status !== 'paid')
                <form method="POST" action="{{ route('subcontractors.certificates.mark-paid', $certificate) }}">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="bx bx-check me-1"></i> Mark as Paid
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible d-print-none" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Official CIS Voucher Card -->
    <div class="card p-4 border shadow-sm">
        <div class="d-flex justify-content-between border-bottom pb-4 mb-4">
            <div>
                <h4 class="fw-bold text-primary mb-1">CIS PAYMENT & DEDUCTION STATEMENT</h4>
                <p class="text-muted mb-0">Under Regulation 13 of the Income Tax (Construction Industry Scheme) Regulations 2005</p>
            </div>
            <div class="text-end">
                <h5 class="fw-bold mb-1">{{ $certificate->certificate_number }}</h5>
                <div><strong>Tax Month:</strong> {{ $certificate->tax_month }}</div>
                <div><strong>Issued Date:</strong> {{ $certificate->certified_at?->format('d M Y') ?? date('d M Y') }}</div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <h6 class="fw-bold text-muted mb-2">CONTRACTOR (PAYER)</h6>
                <div class="border p-3 rounded bg-light">
                    <h5 class="fw-bold mb-1">{{ config('app.name', 'NeoERP Construction Ltd') }}</h5>
                    <div class="small">
                        <div>Contractor Tax Office Ref: <strong>120/NE77491</strong></div>
                        <div>Certified by: {{ $certificate->certifier->name ?? 'Commercial Manager' }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <h6 class="fw-bold text-muted mb-2">SUBCONTRACTOR (PAYEE)</h6>
                <div class="border p-3 rounded bg-light">
                    <h5 class="fw-bold mb-1">{{ $certificate->subcontractor->company_name }}</h5>
                    <div class="small">
                        <div>Unique Taxpayer Reference (UTR): <strong>{{ $certificate->subcontractor->utr_number }}</strong></div>
                        <div>HMRC Verification Ref: <strong>{{ $certificate->subcontractor->cis_verification_number ?? 'VERIFIED' }}</strong></div>
                        @if($certificate->subcontractor->company_registration_number)
                            <div>Company Reg: {{ $certificate->subcontractor->company_registration_number }}</div>
                        @endif
                        @if($certificate->subcontractor->address)
                            <div class="text-muted">{{ $certificate->subcontractor->address }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="alert alert-secondary py-2 mb-4">
            <strong>Project Reference:</strong> Job #{{ $certificate->job->id }} — {{ $certificate->job->job_title ?? $certificate->job->job_name }}
            <span class="ms-3 text-muted">Valuation Period: {{ $certificate->period_start?->format('d M Y') }} to {{ $certificate->period_end?->format('d M Y') }}</span>
        </div>

        <!-- Financial Statement Breakdown -->
        <div class="table-responsive mb-4">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Statement Component</th>
                        <th>HMRC CIS Treatment</th>
                        <th class="text-end" style="width: 25%;">Amount (£)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong class="text-dark">Total Gross Amount Paid (excl. VAT)</strong></td>
                        <td class="text-muted small">Total claimed for labor, plant, materials</td>
                        <td class="text-end fw-bold tabular-nums fs-6">£{{ number_format($certificate->gross_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td><strong class="text-dark">Less: Cost of Materials</strong></td>
                        <td class="text-muted small">Statutory exemption from CIS tax deduction</td>
                        <td class="text-end text-muted tabular-nums fs-6">-£{{ number_format($certificate->materials_cost, 2) }}</td>
                    </tr>
                    <tr style="background-color: #f8fafc;">
                        <td><strong class="text-dark">Amount Liable to CIS Deduction (Labor Element)</strong></td>
                        <td class="text-muted small">Subject to statutory CIS withholding rate</td>
                        <td class="text-end fw-bold tabular-nums fs-6">£{{ number_format($certificate->labor_amount, 2) }}</td>
                    </tr>
                    <tr style="background-color: #fffbeb;">
                        <td>
                            <strong class="text-danger">CIS Tax Deducted at {{ number_format($certificate->cis_tax_rate, 0) }}%</strong>
                            <div class="small text-muted">Withheld and paid directly to HMRC under CIS scheme</div>
                        </td>
                        <td class="text-danger fw-bold">
                            <span class="badge bg-label-danger">{{ $certificate->subcontractor->cis_tax_status === 'gross' ? '0% Gross' : number_format($certificate->cis_tax_rate, 0) . '% Statutory' }}</span>
                        </td>
                        <td class="text-end fw-bold text-danger fs-6 tabular-nums">-£{{ number_format($certificate->cis_deduction_amount, 2) }}</td>
                    </tr>
                    <tr style="background-color: #ecfdf5; border: 2px solid #10b981;">
                        <td colspan="2" class="fs-5 fw-bold text-success py-3">
                            <i class="bx bx-check-circle me-1"></i> NET AMOUNT PAYABLE TO SUBCONTRACTOR
                        </td>
                        <td class="text-end fs-4 fw-bold text-success tabular-nums py-3">£{{ number_format($certificate->net_payable, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        @if($certificate->notes)
        <div class="mb-4">
            <h6 class="fw-bold small text-muted mb-1 text-uppercase">Certificate Notes & Work Description</h6>
            <div class="p-3 bg-light rounded text-dark small border">{{ $certificate->notes }}</div>
        </div>
        @endif

        <div class="row pt-4 border-top mt-auto text-muted small">
            <div class="col-6">
                <div>Payment Status: 
                    @if($certificate->payment_status === 'paid')
                        <span class="badge bg-success"><span class="status-indicator-dot dot-healthy me-1"></span> PAID</span>
                    @else
                        <span class="badge bg-warning"><span class="status-indicator-dot dot-warning me-1"></span> {{ strtoupper($certificate->payment_status) }}</span>
                    @endif
                </div>
                @if($certificate->paid_at)
                    <div class="mt-1">Settlement Date: <strong class="text-dark">{{ $certificate->paid_at?->format('d M Y') }}</strong></div>
                @endif
            </div>
            <div class="col-6 text-end">
                <div>Generated by <strong>NeoERP Construction Suite</strong></div>
                <div class="text-muted">Keep this official statement for HMRC end-of-year CIS accounting</div>
            </div>
        </div>
    </div>
</div>
@endsection
