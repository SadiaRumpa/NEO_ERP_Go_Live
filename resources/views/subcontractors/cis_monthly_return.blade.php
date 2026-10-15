@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-1 mb-1"><span class="text-muted fw-light">HMRC Compliance /</span> CIS300 Monthly Return</h4>
            <p class="text-muted mb-0">Construction Industry Scheme monthly return schedule, statutory subcontractor deductions, and HMRC submission ledger.</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-primary" onclick="window.print();">
                <i class="bx bx-printer me-1"></i> Print Return
            </button>
            <a href="{{ route('subcontractors.certificates.index') }}" class="btn btn-secondary">
                <i class="bx bx-receipt me-1"></i> Certificates Ledger
            </a>
        </div>
    </div>

    <!-- Month Filter & HMRC Deadline Banner -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-5">
                    <form method="GET" action="{{ route('subcontractors.cis-monthly-return') }}" class="d-flex gap-2">
                        <label class="form-label col-form-label fw-bold">Tax Month:</label>
                        <input type="month" name="tax_month" class="form-control" value="{{ $taxMonth }}">
                        <button type="submit" class="btn btn-primary"><i class="bx bx-refresh me-1"></i> Load</button>
                    </form>
                </div>
                <div class="col-md-7 text-md-end mt-2 mt-md-0">
                    <div class="alert alert-warning py-2 mb-0 d-inline-block">
                        <i class="bx bx-calendar-exclamation me-1"></i>
                        HMRC CIS Filing Deadline for <strong>{{ $taxMonth }}</strong> is <strong>19th {{ date('F Y', strtotime($taxMonth . '-01 +1 month')) }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Metrics Cards -->
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-subtitle text-muted mb-1">Total Gross Payments</h6>
                    <h4 class="card-title fw-bold mb-0">£{{ number_format($return['total_gross'], 2) }}</h4>
                    <small class="text-muted">{{ $return['certificates_count'] }} payment certificates</small>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-subtitle text-muted mb-1">Materials Exempt</h6>
                    <h4 class="card-title fw-bold mb-0">£{{ number_format($return['total_materials'], 2) }}</h4>
                    <small class="text-muted">Direct costs of materials</small>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-subtitle text-muted mb-1">Labor Liable to CIS</h6>
                    <h4 class="card-title fw-bold mb-0 text-primary">£{{ number_format($return['total_labor'], 2) }}</h4>
                    <small class="text-muted">Taxable labor element</small>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card bg-label-danger">
                <div class="card-body">
                    <h6 class="card-subtitle text-danger mb-1 fw-bold">CIS Deductions to Pay HMRC</h6>
                    <h4 class="card-title fw-bold mb-0 text-danger">£{{ number_format($return['total_deductions'], 2) }}</h4>
                    <small class="text-muted">Payable to Collector of Taxes</small>
                </div>
            </div>
        </div>
    </div>

    <!-- HMRC CIS300 Schedule Table -->
    <div class="card">
        <div class="card-header border-bottom">
            <h5 class="card-title mb-0">Subcontractor Payment & Deduction Schedule (HMRC CIS300)</h5>
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
                    @forelse($return['schedule'] as $row)
                    <tr>
                        <td>
                            <a href="{{ route('subcontractors.show', $row['subcontractor_id']) }}" class="fw-bold text-primary">
                                {{ $row['name'] }}
                            </a>
                        </td>
                        <td><code>{{ $row['utr_number'] }}</code></td>
                        <td><span class="badge bg-label-info">{{ $row['verification_number'] }}</span></td>
                        <td>
                            <span class="badge {{ $row['tax_rate'] == 0 ? 'bg-label-success' : ($row['tax_rate'] == 20 ? 'bg-label-primary' : 'bg-label-danger') }}">
                                {{ number_format($row['tax_rate'], 0) }}% ({{ ucfirst($row['tax_status']) }})
                            </span>
                        </td>
                        <td class="text-end fw-bold">£{{ number_format($row['total_gross'], 2) }}</td>
                        <td class="text-end">£{{ number_format($row['total_materials'], 2) }}</td>
                        <td class="text-end">£{{ number_format($row['total_labor'], 2) }}</td>
                        <td class="text-end fw-bold text-danger">-£{{ number_format($row['total_deductions'], 2) }}</td>
                        <td class="text-end fw-bold text-success">£{{ number_format($row['total_net_paid'], 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">
                            <i class="bx bx-receipt fs-1 d-block mb-2"></i>
                            No certified payments recorded for tax month {{ $taxMonth }}.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if(count($return['schedule']) > 0)
                <tfoot class="table-light">
                    <tr>
                        <th colspan="4" class="text-end fw-bold">HMRC CIS300 Return Totals:</th>
                        <th class="text-end fw-bold">£{{ number_format($return['total_gross'], 2) }}</th>
                        <th class="text-end fw-bold">£{{ number_format($return['total_materials'], 2) }}</th>
                        <th class="text-end fw-bold">£{{ number_format($return['total_labor'], 2) }}</th>
                        <th class="text-end fw-bold text-danger">-£{{ number_format($return['total_deductions'], 2) }}</th>
                        <th class="text-end fw-bold text-success fs-6">£{{ number_format($return['total_net_paid'], 2) }}</th>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection
