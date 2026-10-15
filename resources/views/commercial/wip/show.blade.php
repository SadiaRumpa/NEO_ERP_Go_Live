@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-1 mb-1">
                <span class="text-muted fw-light">Commercial WIP Engine /</span> Job #{{ $wip['job_id'] }} - {{ $wip['job_name'] }}
            </h4>
            <p class="text-muted mb-0">Client: <strong>{{ $wip['customer_name'] }}</strong> &bull; Live Work-In-Progress Job Costing Ledger</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('commercial.wip.portfolio') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Portfolio
            </a>
            <button type="button" class="btn btn-outline-primary" onclick="window.print();">
                <i class="bx bx-printer me-1"></i> Print Job Pack
            </button>
        </div>
    </div>

    <!-- Executive KPI Bar -->
    <div class="row mb-4">
        <div class="col-md-3 mb-2">
            <div class="card bg-label-primary">
                <div class="card-body">
                    <h6 class="card-subtitle text-primary mb-1 fw-bold">Revised Contract Sum</h6>
                    <h3 class="card-title fw-bold mb-0 text-primary">£{{ number_format($wip['revised_contract_sum'], 2) }}</h3>
                    <small class="text-muted">
                        Base: £{{ number_format($wip['original_contract_value'], 2) }} + Vars: £{{ number_format($wip['approved_variations'], 2) }}
                    </small>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-2">
            <div class="card bg-label-danger">
                <div class="card-body">
                    <h6 class="card-subtitle text-danger mb-1 fw-bold">Actual Incurred Cost</h6>
                    <h3 class="card-title fw-bold mb-0 text-danger">£{{ number_format($wip['total_actual_cost'], 2) }}</h3>
                    <small class="text-muted">Committed POs: £{{ number_format($wip['total_committed_cost'], 2) }}</small>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-2">
            <div class="card {{ $wip['commercial_status'] === 'healthy' ? 'bg-label-success' : ($wip['commercial_status'] === 'warning' ? 'bg-label-warning' : 'bg-label-danger') }}">
                <div class="card-body">
                    <h6 class="card-subtitle mb-1 fw-bold">Live Gross Margin</h6>
                    <h3 class="card-title fw-bold mb-0">
                        {{ number_format($wip['live_gross_margin_percent'], 1) }}%
                    </h3>
                    <small class="text-muted">Live GP: £{{ number_format($wip['live_gross_profit'], 2) }}</small>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-2">
            <div class="card bg-label-info">
                <div class="card-body">
                    <h6 class="card-subtitle text-info mb-1 fw-bold">WIP Valuation Position</h6>
                    <h3 class="card-title fw-bold mb-0 text-info">£{{ number_format(abs($wip['wip_balance']), 2) }}</h3>
                    <small class="text-muted">{{ $wip['wip_type'] }}</small>
                </div>
            </div>
        </div>
    </div>

    <!-- 4 Cost Pillars Breakdown Cards -->
    <h5 class="fw-bold mb-3"><i class="bx bx-layer me-1 text-primary"></i> The 4 Cost Ingestion Pillars</h5>
    <div class="row mb-4">
        <!-- Pillar 1: Materials -->
        <div class="col-md-3 mb-2">
            <div class="card h-100 border-top border-primary border-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold text-primary mb-0"><i class="bx bx-package me-1"></i> 1. Materials</h6>
                        <span class="badge bg-label-primary">{{ $wip['pillars']['materials']['count_pos'] }} POs</span>
                    </div>
                    <h4 class="fw-bold mb-1">£{{ number_format($wip['pillars']['materials']['actual_cost'], 2) }}</h4>
                    <div class="small text-muted">
                        <div>Committed POs: £{{ number_format($wip['pillars']['materials']['po_committed'], 2) }}</div>
                        <div>Bills Incurred: £{{ number_format($wip['pillars']['materials']['bills_incurred'], 2) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pillar 2: Labor -->
        <div class="col-md-3 mb-2">
            <div class="card h-100 border-top border-info border-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold text-info mb-0"><i class="bx bx-time me-1"></i> 2. Labor</h6>
                        <span class="badge bg-label-info">{{ $wip['pillars']['labor']['count_timesheets'] }} Timesheets</span>
                    </div>
                    <h4 class="fw-bold mb-1">£{{ number_format($wip['pillars']['labor']['burdened_cost'], 2) }}</h4>
                    <div class="small text-muted">
                        <div>Burdened (NI/Pens/OH)</div>
                        <div>Direct Pay: £{{ number_format($wip['pillars']['labor']['direct_pay'], 2) }} ({{ $wip['pillars']['labor']['total_hours'] }}h)</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pillar 3: Subcontractors -->
        <div class="col-md-3 mb-2">
            <div class="card h-100 border-top border-warning border-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold text-warning mb-0"><i class="bx bx-hard-hat me-1"></i> 3. Subcontractors</h6>
                        <span class="badge bg-label-warning">{{ $wip['pillars']['subcontractors']['count_certificates'] }} Certs</span>
                    </div>
                    <h4 class="fw-bold mb-1">£{{ number_format($wip['pillars']['subcontractors']['gross_cost'], 2) }}</h4>
                    <div class="small text-muted">
                        <div>HMRC CIS: -£{{ number_format($wip['pillars']['subcontractors']['cis_tax_withheld'], 2) }}</div>
                        <div>Net Paid: £{{ number_format($wip['pillars']['subcontractors']['net_payable'], 2) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pillar 4: Plant & Equipment -->
        <div class="col-md-3 mb-2">
            <div class="card h-100 border-top border-success border-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold text-success mb-0"><i class="bx bx-crane me-1"></i> 4. Plant Hire</h6>
                        <span class="badge bg-label-success">{{ $wip['pillars']['plant']['active_count'] }} Active</span>
                    </div>
                    <h4 class="fw-bold mb-1">£{{ number_format($wip['pillars']['plant']['accrued_cost'], 2) }}</h4>
                    <div class="small text-muted">
                        <div>Live daily/weekly accruals</div>
                        <div>Total contracts: {{ $wip['pillars']['plant']['count_hires'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Itemized Detailed Cost Tables -->
    <div class="card mb-4">
        <div class="card-header border-bottom">
            <ul class="nav nav-pills card-header-pills" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-materials" role="tab">
                        <i class="bx bx-package me-1"></i> Materials POs & Bills
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-labor" role="tab">
                        <i class="bx bx-time me-1"></i> Burdened Timesheets
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-subs" role="tab">
                        <i class="bx bx-hard-hat me-1"></i> Subcontractor CIS
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-plant" role="tab">
                        <i class="bx bx-crane me-1"></i> Plant Equipment Hire
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-commercial" role="tab">
                        <i class="bx bx-file me-1"></i> Variations & Valuations
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content p-0">
                <!-- Tab Materials -->
                <div class="tab-pane fade show active" id="tab-materials" role="tabpanel">
                    <div class="table-responsive text-nowrap">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>PO Ref</th>
                                    <th>Supplier</th>
                                    <th>Issue Date</th>
                                    <th class="text-end">Committed Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($job->purchaseOrders as $po)
                                <tr>
                                    <td><span class="fw-bold">{{ $po->po_number }}</span></td>
                                    <td>{{ $po->supplier->name ?? 'N/A' }}</td>
                                    <td>{{ $po->order_date ? $po->order_date->format('d M Y') : 'N/A' }}</td>
                                    <td class="text-end fw-bold">£{{ number_format($po->total_amount, 2) }}</td>
                                    <td><span class="badge bg-label-info text-capitalize">{{ str_replace('_', ' ', $po->status) }}</span></td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center py-3 text-muted">No purchase orders logged on this job.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab Labor -->
                <div class="tab-pane fade" id="tab-labor" role="tabpanel">
                    <div class="table-responsive text-nowrap">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Timesheet #</th>
                                    <th>Operative</th>
                                    <th>Work Date</th>
                                    <th class="text-end">Hours</th>
                                    <th class="text-end">Direct Pay</th>
                                    <th class="text-end fw-bold text-primary">Burdened WIP Cost</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($job->laborTimesheets as $ts)
                                <tr>
                                    <td><span class="fw-bold">{{ $ts->timesheet_number }}</span></td>
                                    <td>{{ $ts->worker->name ?? 'Operative' }}</td>
                                    <td>{{ $ts->work_date->format('d M Y') }}</td>
                                    <td class="text-end">{{ $ts->total_hours }}h</td>
                                    <td class="text-end">£{{ number_format($ts->direct_pay_amount, 2) }}</td>
                                    <td class="text-end fw-bold text-primary">£{{ number_format($ts->fully_burdened_cost, 2) }}</td>
                                    <td><span class="badge bg-label-success">{{ ucfirst($ts->status) }}</span></td>
                                </tr>
                                @empty
                                <tr><td colspan="7" class="text-center py-3 text-muted">No labor timesheets logged on this job.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab Subcontractors -->
                <div class="tab-pane fade" id="tab-subs" role="tabpanel">
                    <div class="table-responsive text-nowrap">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Certificate #</th>
                                    <th>Subcontractor</th>
                                    <th>HMRC UTR</th>
                                    <th class="text-end">Gross (£)</th>
                                    <th class="text-end">CIS Deduction</th>
                                    <th class="text-end fw-bold text-success">Net Payable</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($job->subcontractorCertificates as $cert)
                                <tr>
                                    <td><span class="fw-bold">{{ $cert->certificate_number }}</span></td>
                                    <td>{{ $cert->subcontractor->company_name ?? 'N/A' }}</td>
                                    <td><code>{{ $cert->subcontractor->utr_number ?? 'N/A' }}</code></td>
                                    <td class="text-end">£{{ number_format($cert->gross_amount, 2) }}</td>
                                    <td class="text-end text-danger">-£{{ number_format($cert->cis_deduction_amount, 2) }} ({{ $cert->cis_tax_rate }}%)</td>
                                    <td class="text-end fw-bold text-success">£{{ number_format($cert->net_payable, 2) }}</td>
                                    <td><span class="badge bg-label-info">{{ ucfirst($cert->payment_status) }}</span></td>
                                </tr>
                                @empty
                                <tr><td colspan="7" class="text-center py-3 text-muted">No subcontractor certificates issued on this job.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab Plant -->
                <div class="tab-pane fade" id="tab-plant" role="tabpanel">
                    <div class="table-responsive text-nowrap">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Hire Ref</th>
                                    <th>Equipment</th>
                                    <th>Supplier</th>
                                    <th>Hire Period</th>
                                    <th class="text-end fw-bold">Accrued Cost (£)</th>
                                    <th>Status</th>
                                    <th>Off-Hire Ref</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($job->plantHires as $ph)
                                <tr>
                                    <td><span class="fw-bold">{{ $ph->hire_reference }}</span></td>
                                    <td>{{ $ph->item_name }}</td>
                                    <td>{{ $ph->supplier->name ?? 'N/A' }}</td>
                                    <td>{{ $ph->start_date->format('d/m/y') }} &rarr; {{ $ph->expected_end_date->format('d/m/y') }}</td>
                                    <td class="text-end fw-bold">£{{ number_format($ph->accrued_hire_cost, 2) }}</td>
                                    <td><span class="badge bg-label-primary">{{ ucfirst($ph->status) }}</span></td>
                                    <td><code>{{ $ph->off_hire_reference ?? 'Pending Return' }}</code></td>
                                </tr>
                                @empty
                                <tr><td colspan="7" class="text-center py-3 text-muted">No plant or equipment hires allocated to this job.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab Commercial -->
                <div class="tab-pane fade" id="tab-commercial" role="tabpanel">
                    <h6 class="fw-bold mb-2">Approved Variations</h6>
                    <div class="table-responsive text-nowrap mb-4">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>Variation #</th>
                                    <th>Title</th>
                                    <th class="text-end">Cost Impact</th>
                                    <th class="text-end">Quoted Value</th>
                                    <th>Status</th>
                                    <th>Signed By</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($job->variations as $v)
                                <tr>
                                    <td><span class="fw-bold">{{ $v->variation_number }}</span></td>
                                    <td>{{ $v->title }}</td>
                                    <td class="text-end">£{{ number_format($v->cost_amount, 2) }}</td>
                                    <td class="text-end fw-bold text-primary">£{{ number_format($v->quoted_amount, 2) }}</td>
                                    <td><span class="badge bg-label-success">{{ ucfirst($v->status) }}</span></td>
                                    <td>{{ $v->client_signer_name ?? 'N/A' }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="text-center py-2 text-muted">No variations logged.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <h6 class="fw-bold mb-2">Applications for Payment (Valuations)</h6>
                    <div class="table-responsive text-nowrap">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>Application #</th>
                                    <th>Valuation Date</th>
                                    <th class="text-end">Gross Valuation</th>
                                    <th class="text-end">Retention Deducted</th>
                                    <th class="text-end">Net Claim</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($job->valuations as $val)
                                <tr>
                                    <td><span class="fw-bold">{{ $val->application_number }}</span></td>
                                    <td>{{ $val->valuation_date->format('d M Y') }}</td>
                                    <td class="text-end">£{{ number_format($val->gross_valuation, 2) }}</td>
                                    <td class="text-end text-danger">-£{{ number_format($val->retention_deduction, 2) }}</td>
                                    <td class="text-end fw-bold text-success">£{{ number_format($val->certified_amount ?? $val->net_claim_amount, 2) }}</td>
                                    <td><span class="badge bg-label-info">{{ ucfirst($val->status) }}</span></td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="text-center py-2 text-muted">No payment applications issued.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
