@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-label-primary px-2 py-1"><i class="ti tabler-building-bank me-1"></i> HMRC Statutory Compliance</span>
                <span class="text-muted fs-7">&bull; UK CIS Regulation</span>
            </div>
            <h4 class="fw-bold mb-1 text-dark"><span class="text-muted fw-light">HMRC Compliance /</span> CIS300 Monthly Return</h4>
            <p class="text-muted mb-0 small">Construction Industry Scheme monthly return schedule, statutory subcontractor deductions, and HMRC submission ledger.</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary" onclick="window.print();">
                <i class="ti tabler-printer me-1"></i> Print HMRC CIS300
            </button>
            <a href="{{ route('subcontractors.certificates.index') }}" class="btn btn-primary">
                <i class="ti tabler-receipt me-1"></i> Certificates Ledger
            </a>
        </div>
    </div>

    <!-- Month Filter & HMRC Deadline Banner -->
    <div class="card mb-4 border-primary">
        <div class="card-body py-3">
            <div class="row align-items-center g-3">
                <div class="col-md-5">
                    <form method="GET" action="{{ route('subcontractors.cis-monthly-return') }}" class="d-flex gap-2 align-items-center">
                        <label class="form-label col-form-label fw-bold mb-0 text-nowrap">Tax Month:</label>
                        <input type="month" name="tax_month" class="form-control" value="{{ $taxMonth }}">
                        <button type="submit" class="btn btn-primary"><i class="ti tabler-refresh me-1"></i> Load</button>
                    </form>
                </div>
                <div class="col-md-7 text-md-end">
                    <div class="badge bg-label-warning py-2 px-3 fs-7 border">
                        <i class="ti tabler-calendar-time me-1"></i>
                        HMRC CIS Filing Deadline for <strong>{{ $taxMonth }}</strong> is <strong>19th {{ date('F Y', strtotime($taxMonth . '-01 +1 month')) }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Metrics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="neo-kpi-card kpi-primary h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-subtitle">Total Gross Payments</div>
                        <div class="kpi-value text-dark tabular-nums">£{{ number_format($return['total_gross'], 2) }}</div>
                    </div>
                    <span class="badge bg-label-primary p-2 rounded-3"><i class="ti tabler-cash fs-4"></i></span>
                </div>
                <div class="kpi-footnote">{{ $return['certificates_count'] }} payment certificates processed</div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="neo-kpi-card kpi-info h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-subtitle">Materials Exempt from CIS</div>
                        <div class="kpi-value text-info tabular-nums">£{{ number_format($return['total_materials'], 2) }}</div>
                    </div>
                    <span class="badge bg-label-info p-2 rounded-3"><i class="ti tabler-shield-check fs-4"></i></span>
                </div>
                <div class="kpi-footnote">Direct cost of goods/materials</div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="neo-kpi-card kpi-warning h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-subtitle">Labor Element (Taxable)</div>
                        <div class="kpi-value text-primary tabular-nums">£{{ number_format($return['total_labor'], 2) }}</div>
                    </div>
                    <span class="badge bg-label-warning p-2 rounded-3"><i class="ti tabler-hammer fs-4"></i></span>
                </div>
                <div class="kpi-footnote">Subject to CIS deduction rates</div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="neo-kpi-card kpi-danger h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-subtitle">CIS Tax Payable to HMRC</div>
                        <div class="kpi-value text-danger tabular-nums">£{{ number_format($return['total_deductions'], 2) }}</div>
                    </div>
                    <span class="badge bg-label-danger p-2 rounded-3"><i class="ti tabler-receipt-tax fs-4"></i></span>
                </div>
                <div class="kpi-footnote">Payable to Collector of Taxes</div>
            </div>
        </div>
    </div>

    <!-- HMRC CIS300 Schedule Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center border-bottom">
            <div>
                <h5 class="card-title mb-0">Subcontractor Payment & Deduction Schedule (HMRC CIS300)</h5>
                <small class="text-muted">Statutory monthly return breakdown by subcontractor UTR</small>
            </div>
            <span class="badge bg-label-secondary font-monospace">{{ count($return['schedule']) }} Subcontractors</span>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Subcontractor</th>
                        <th>HMRC UTR</th>
                        <th>Verification Ref</th>
                        <th>CIS Tax Rate</th>
                        <th class="text-end">Total Gross (£)</th>
                        <th class="text-end">Materials (£)</th>
                        <th class="text-end">Labor Element (£)</th>
                        <th class="text-end text-danger">CIS Tax Deducted (£)</th>
                        <th class="text-end text-success">Net Paid (£)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($return['schedule'] as $sub)
                    <tr>
                        <td>
                            <a href="{{ route('subcontractors.show', $sub['subcontractor_id']) }}" class="fw-bold text-primary">
                                {{ $sub['name'] }}
                            </a>
                        </td>
                        <td><code>{{ $sub['utr_number'] }}</code></td>
                        <td><span class="badge bg-label-info">{{ $sub['verification_number'] }}</span></td>
                        <td>
                            @if($sub['tax_rate'] == 0)
                                <span class="badge bg-label-success">0% (Gross)</span>
                            @elseif($sub['tax_rate'] == 20)
                                <span class="badge bg-label-primary">20% (Standard)</span>
                            @else
                                <span class="badge bg-label-danger">30% (Higher)</span>
                            @endif
                        </td>
                        <td class="text-end fw-bold tabular-nums">£{{ number_format($sub['total_gross'], 2) }}</td>
                        <td class="text-end tabular-nums">£{{ number_format($sub['total_materials'], 2) }}</td>
                        <td class="text-end tabular-nums">£{{ number_format($sub['total_labor'], 2) }}</td>
                        <td class="text-end fw-bold text-danger tabular-nums">-£{{ number_format($sub['total_deductions'], 2) }}</td>
                        <td class="text-end fw-bold text-success tabular-nums">£{{ number_format($sub['total_net_paid'], 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="ti tabler-file-off fs-1 d-block mb-2 text-secondary"></i>
                            No subcontractor payments certified or paid in tax month {{ $taxMonth }}.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if(count($return['schedule']) > 0)
                <tfoot class="table-light">
                    <tr>
                        <th colspan="4" class="text-end fw-bold">HMRC CIS300 Return Totals:</th>
                        <th class="text-end fw-bold tabular-nums">£{{ number_format($return['total_gross'], 2) }}</th>
                        <th class="text-end fw-bold tabular-nums">£{{ number_format($return['total_materials'], 2) }}</th>
                        <th class="text-end fw-bold tabular-nums">£{{ number_format($return['total_labor'], 2) }}</th>
                        <th class="text-end fw-bold text-danger tabular-nums">-£{{ number_format($return['total_deductions'], 2) }}</th>
                        <th class="text-end fw-bold text-success fs-6 tabular-nums">£{{ number_format($return['total_net_paid'], 2) }}</th>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection
