@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Breadcrumb & Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('commercial.wip.portfolio') }}" class="text-muted text-decoration-none small">
                    <i class="ti tabler-arrow-left me-1"></i> Commercial Portfolio
                </a>
                <span class="text-muted fs-7">&bull;</span>
                <span class="badge bg-label-primary font-monospace">JOB-{{ str_pad($wip['job_id'], 4, '0', STR_PAD_LEFT) }}</span>
            </div>
            <h4 class="fw-bold mb-1 text-dark">
                {{ $wip['job_name'] }}
            </h4>
            <div class="text-muted small">
                Client: <strong class="text-dark">{{ $wip['customer_name'] }}</strong>
                &bull; Live Work-In-Progress Job Costing Ledger
            </div>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary" onclick="window.print();">
                <i class="ti tabler-printer me-1"></i> Print Job Financial Pack
            </button>
            <a href="{{ route('commercial.valuations.create', ['job_id' => $job->id]) }}" class="btn btn-primary">
                <i class="ti tabler-plus me-1"></i> New Application
            </a>
        </div>
    </div>

    <!-- Executive KPI Metric Widgets -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="neo-kpi-card kpi-primary h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-subtitle">Revised Contract Sum</div>
                        <div class="kpi-value text-primary">£{{ number_format($wip['revised_contract_sum'], 2) }}</div>
                    </div>
                    <span class="badge bg-label-primary p-2 rounded-3"><i class="ti tabler-file-certificate fs-4"></i></span>
                </div>
                <div class="kpi-footnote">
                    Base: £{{ number_format($wip['original_contract_value'], 2) }}
                    @if($wip['approved_variations'] > 0)
                        &bull; <span class="text-success">+£{{ number_format($wip['approved_variations'], 2) }} var</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="neo-kpi-card kpi-danger h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-subtitle">Actual Incurred Cost</div>
                        <div class="kpi-value text-danger">£{{ number_format($wip['total_actual_cost'], 2) }}</div>
                    </div>
                    <span class="badge bg-label-danger p-2 rounded-3"><i class="ti tabler-coins fs-4"></i></span>
                </div>
                <div class="kpi-footnote">Committed POs: £{{ number_format($wip['total_committed_cost'], 2) }}</div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="neo-kpi-card {{ $wip['commercial_status'] === 'healthy' ? 'kpi-success' : ($wip['commercial_status'] === 'warning' ? 'kpi-warning' : 'kpi-danger') }} h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-subtitle">Live Gross Margin</div>
                        <div class="kpi-value {{ $wip['commercial_status'] === 'healthy' ? 'text-success' : ($wip['commercial_status'] === 'warning' ? 'text-warning' : 'text-danger') }}">
                            {{ number_format($wip['live_gross_margin_percent'], 1) }}%
                        </div>
                    </div>
                    <span class="badge {{ $wip['commercial_status'] === 'healthy' ? 'bg-label-success' : ($wip['commercial_status'] === 'warning' ? 'bg-label-warning' : 'bg-label-danger') }} p-2 rounded-3">
                        <i class="ti tabler-trending-up fs-4"></i>
                    </span>
                </div>
                <div class="kpi-footnote">Gross Profit: £{{ number_format($wip['live_gross_profit'], 2) }}</div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="neo-kpi-card kpi-info h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-subtitle">WIP Valuation Position</div>
                        <div class="kpi-value text-info">£{{ number_format(abs($wip['wip_balance']), 2) }}</div>
                    </div>
                    <span class="badge bg-label-info p-2 rounded-3"><i class="ti tabler-scale fs-4"></i></span>
                </div>
                <div class="kpi-footnote">{{ $wip['wip_type'] }}</div>
            </div>
        </div>
    </div>

    <!-- 4 Cost Pillars Breakdown Cards with Visual Progress Bar -->
    @php
        $totMat = $wip['pillars']['materials']['actual_cost'];
        $totLab = $wip['pillars']['labor']['burdened_cost'];
        $totSub = $wip['pillars']['subcontractors']['gross_cost'];
        $totPlt = $wip['pillars']['plant']['accrued_cost'];
        $grandCost = max(0.01, $totMat + $totLab + $totSub + $totPlt);
        $pMat = round(($totMat / $grandCost) * 100, 1);
        $pLab = round(($totLab / $grandCost) * 100, 1);
        $pSub = round(($totSub / $grandCost) * 100, 1);
        $pPlt = round(($totPlt / $grandCost) * 100, 1);
    @endphp

    <div class="card mb-4">
        <div class="card-header border-bottom">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h5 class="card-title mb-0"><i class="ti tabler-stack-2 me-1 text-primary"></i> The 4 Cost Ingestion Pillars</h5>
                    <small class="text-muted">Total direct project expenditure absorbed into live Job WIP</small>
                </div>
                <span class="badge bg-label-dark fs-7 tabular-nums">Total: £{{ number_format($wip['total_actual_cost'], 2) }}</span>
            </div>
            <div class="neo-pillar-progress-track mt-3">
                <div class="neo-pillar-segment segment-materials" style="width: {{ $pMat }}%;" title="Materials: {{ $pMat }}%"></div>
                <div class="neo-pillar-segment segment-labor" style="width: {{ $pLab }}%;" title="Labor: {{ $pLab }}%"></div>
                <div class="neo-pillar-segment segment-subs" style="width: {{ $pSub }}%;" title="Subcontractors: {{ $pSub }}%"></div>
                <div class="neo-pillar-segment segment-plant" style="width: {{ $pPlt }}%;" title="Plant Hire: {{ $pPlt }}%"></div>
            </div>
            <div class="neo-pillar-legend">
                <div class="neo-pillar-legend-item">
                    <span class="neo-legend-swatch segment-materials"></span>
                    <span>1. Materials {{ $pMat }}%</span>
                </div>
                <div class="neo-pillar-legend-item">
                    <span class="neo-legend-swatch segment-labor"></span>
                    <span>2. Labor {{ $pLab }}%</span>
                </div>
                <div class="neo-pillar-legend-item">
                    <span class="neo-legend-swatch segment-subs"></span>
                    <span>3. Subcontractors {{ $pSub }}%</span>
                </div>
                <div class="neo-pillar-legend-item">
                    <span class="neo-legend-swatch segment-plant"></span>
                    <span>4. Plant Hire {{ $pPlt }}%</span>
                </div>
            </div>
        </div>

        <div class="card-body pt-3">
            <div class="row g-3">
                <!-- Pillar 1: Materials -->
                <div class="col-md-3">
                    <div class="p-3 border rounded-3 bg-light h-100">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold text-primary small text-uppercase">1. Materials</span>
                            <span class="badge bg-label-primary">{{ $wip['pillars']['materials']['count_pos'] }} POs</span>
                        </div>
                        <h4 class="fw-bold mb-1 tabular-nums">£{{ number_format($wip['pillars']['materials']['actual_cost'], 2) }}</h4>
                        <div class="small text-muted">
                            <div>Committed POs: £{{ number_format($wip['pillars']['materials']['po_committed'], 2) }}</div>
                            <div>Matched Bills: £{{ number_format($wip['pillars']['materials']['bills_incurred'], 2) }}</div>
                        </div>
                    </div>
                </div>

                <!-- Pillar 2: Labor -->
                <div class="col-md-3">
                    <div class="p-3 border rounded-3 bg-light h-100">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold text-info small text-uppercase">2. Labor</span>
                            <span class="badge bg-label-info">{{ $wip['pillars']['labor']['count_timesheets'] }} Timesheets</span>
                        </div>
                        <h4 class="fw-bold mb-1 tabular-nums">£{{ number_format($wip['pillars']['labor']['burdened_cost'], 2) }}</h4>
                        <div class="small text-muted">
                            <div>Burdened (NI, Pens, OH)</div>
                            <div>Direct Pay: £{{ number_format($wip['pillars']['labor']['direct_pay'], 2) }} ({{ $wip['pillars']['labor']['total_hours'] }}h)</div>
                        </div>
                    </div>
                </div>

                <!-- Pillar 3: Subcontractors -->
                <div class="col-md-3">
                    <div class="p-3 border rounded-3 bg-light h-100">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold text-warning small text-uppercase">3. Subcontractors</span>
                            <span class="badge bg-label-warning">{{ $wip['pillars']['subcontractors']['count_certificates'] }} Certs</span>
                        </div>
                        <h4 class="fw-bold mb-1 tabular-nums">£{{ number_format($wip['pillars']['subcontractors']['gross_cost'], 2) }}</h4>
                        <div class="small text-muted">
                            <div>HMRC CIS: -£{{ number_format($wip['pillars']['subcontractors']['cis_tax_withheld'], 2) }}</div>
                            <div>Net Paid: £{{ number_format($wip['pillars']['subcontractors']['net_payable'], 2) }}</div>
                        </div>
                    </div>
                </div>

                <!-- Pillar 4: Plant & Equipment -->
                <div class="col-md-3">
                    <div class="p-3 border rounded-3 bg-light h-100">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold text-success small text-uppercase">4. Plant Hire</span>
                            <span class="badge bg-label-success">{{ $wip['pillars']['plant']['active_count'] }} Active</span>
                        </div>
                        <h4 class="fw-bold mb-1 tabular-nums">£{{ number_format($wip['pillars']['plant']['accrued_cost'], 2) }}</h4>
                        <div class="small text-muted">
                            <div>Live daily/weekly accruals</div>
                            <div>Contracts logged: {{ $wip['pillars']['plant']['count_hires'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed 4-Pillars & Commercial Tabs -->
    <div class="card">
        <div class="card-header border-bottom">
            <ul class="nav nav-pills card-header-pills" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-materials" role="tab">
                        <i class="ti tabler-package me-1"></i> Materials ({{ $job->purchaseOrders->count() }})
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-labor" role="tab">
                        <i class="ti tabler-clock me-1"></i> Labor Timesheets ({{ $job->laborTimesheets->count() }})
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-subs" role="tab">
                        <i class="ti tabler-hard-hat me-1"></i> Subcontractor CIS ({{ $job->subcontractorCertificates->count() }})
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-plant" role="tab">
                        <i class="ti tabler-crane me-1"></i> Plant Equipment ({{ $job->plantHires->count() }})
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-commercial" role="tab">
                        <i class="ti tabler-file-certificate me-1"></i> Commercial Contracting
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
                                    <th>PO Reference</th>
                                    <th>Supplier</th>
                                    <th>Order Date</th>
                                    <th class="text-end">Committed Total (£)</th>
                                    <th>3-Way Match Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($job->purchaseOrders as $po)
                                <tr>
                                    <td><span class="fw-bold font-monospace text-primary">{{ $po->po_number }}</span></td>
                                    <td>{{ $po->supplier->name ?? 'N/A' }}</td>
                                    <td>{{ $po->order_date ? $po->order_date->format('d M Y') : 'N/A' }}</td>
                                    <td class="text-end fw-bold text-dark tabular-nums">£{{ number_format($po->total_amount, 2) }}</td>
                                    <td>
                                        <span class="badge bg-label-info text-capitalize">
                                            {{ str_replace('_', ' ', $po->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center py-4 text-muted">No purchase orders logged on this job.</td></tr>
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
                                    <th class="text-end">Total Hours</th>
                                    <th class="text-end">Direct Pay (£)</th>
                                    <th class="text-end fw-bold text-primary">Burdened WIP Cost (£)</th>
                                    <th>Approval Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($job->laborTimesheets as $ts)
                                <tr>
                                    <td><span class="fw-bold font-monospace text-primary">{{ $ts->timesheet_number }}</span></td>
                                    <td><strong>{{ $ts->worker->name ?? 'Operative' }}</strong></td>
                                    <td>{{ $ts->work_date->format('d M Y') }}</td>
                                    <td class="text-end tabular-nums">{{ $ts->total_hours }}h</td>
                                    <td class="text-end tabular-nums">£{{ number_format($ts->direct_pay_amount, 2) }}</td>
                                    <td class="text-end fw-bold text-primary tabular-nums">£{{ number_format($ts->fully_burdened_cost, 2) }}</td>
                                    <td>
                                        @if($ts->status === 'approved')
                                            <span class="badge bg-label-success"><i class="ti tabler-check me-1"></i>Approved</span>
                                        @elseif($ts->status === 'rejected')
                                            <span class="badge bg-label-danger"><i class="ti tabler-x me-1"></i>Rejected</span>
                                        @else
                                            <span class="badge bg-label-warning">Submitted</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="7" class="text-center py-4 text-muted">No labor timesheets logged on this job.</td></tr>
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
                                    <th class="text-end">Gross Amount (£)</th>
                                    <th class="text-end">CIS Deduction</th>
                                    <th class="text-end fw-bold text-success">Net Payable (£)</th>
                                    <th>Payment Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($job->subcontractorCertificates as $cert)
                                <tr>
                                    <td><span class="fw-bold font-monospace text-primary">{{ $cert->certificate_number }}</span></td>
                                    <td><strong>{{ $cert->subcontractor->company_name ?? 'N/A' }}</strong></td>
                                    <td><code>{{ $cert->subcontractor->utr_number ?? 'N/A' }}</code></td>
                                    <td class="text-end tabular-nums">£{{ number_format($cert->gross_amount, 2) }}</td>
                                    <td class="text-end text-danger tabular-nums">
                                        -£{{ number_format($cert->cis_deduction_amount, 2) }}
                                        <small class="text-muted d-block">({{ $cert->cis_tax_rate }}%)</small>
                                    </td>
                                    <td class="text-end fw-bold text-success tabular-nums">£{{ number_format($cert->net_payable, 2) }}</td>
                                    <td>
                                        <span class="badge {{ $cert->payment_status === 'paid' ? 'bg-label-success' : 'bg-label-info' }}">
                                            {{ ucfirst($cert->payment_status) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="7" class="text-center py-4 text-muted">No subcontractor payment certificates issued on this job.</td></tr>
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
                                    <th>Hire Reference</th>
                                    <th>Equipment Item</th>
                                    <th>Supplier</th>
                                    <th>Hire Period</th>
                                    <th class="text-end fw-bold">Accrued Cost (£)</th>
                                    <th>Status</th>
                                    <th>Off-Hire Reference</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($job->plantHires as $ph)
                                <tr>
                                    <td>
                                        <a href="{{ route('plant-hires.show', $ph) }}" class="fw-bold font-monospace text-primary">
                                            {{ $ph->hire_reference }}
                                        </a>
                                    </td>
                                    <td>
                                        <strong>{{ $ph->item_name }}</strong>
                                        <div class="small text-muted text-capitalize">{{ str_replace('_', ' ', $ph->category) }}</div>
                                    </td>
                                    <td>{{ $ph->supplier->name ?? 'N/A' }}</td>
                                    <td>{{ $ph->start_date->format('d/m/y') }} &rarr; {{ $ph->expected_end_date->format('d/m/y') }}</td>
                                    <td class="text-end fw-bold text-dark tabular-nums">£{{ number_format($ph->accrued_hire_cost, 2) }}</td>
                                    <td>
                                        <span class="badge {{ $ph->status === 'active' ? 'bg-label-success' : 'bg-label-secondary' }}">
                                            {{ ucfirst($ph->status) }}
                                        </span>
                                    </td>
                                    <td><code>{{ $ph->off_hire_reference ?? 'Active on site' }}</code></td>
                                </tr>
                                @empty
                                <tr><td colspan="7" class="text-center py-4 text-muted">No plant or equipment hires allocated to this job.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab Commercial -->
                <div class="tab-pane fade" id="tab-commercial" role="tabpanel">
                    <h6 class="fw-bold mb-2">Approved Variations & Scope Changes</h6>
                    <div class="table-responsive text-nowrap mb-4">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>Variation #</th>
                                    <th>Scope Title</th>
                                    <th class="text-end">Cost Impact</th>
                                    <th class="text-end">Quoted Value</th>
                                    <th>Status</th>
                                    <th>E-Signed By</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($job->variations as $v)
                                <tr>
                                    <td><span class="fw-bold font-monospace text-primary">{{ $v->variation_number }}</span></td>
                                    <td>{{ $v->title }}</td>
                                    <td class="text-end tabular-nums">£{{ number_format($v->cost_amount, 2) }}</td>
                                    <td class="text-end fw-bold text-primary tabular-nums">£{{ number_format($v->quoted_amount, 2) }}</td>
                                    <td>
                                        <span class="badge {{ $v->status === 'approved' ? 'bg-label-success' : 'bg-label-secondary' }}">
                                            {{ ucfirst($v->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $v->client_signer_name ?? 'Pending client sign-off' }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="text-center py-3 text-muted">No variations logged.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <h6 class="fw-bold mb-2">Progress Valuations (Applications for Payment)</h6>
                    <div class="table-responsive text-nowrap">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>Application #</th>
                                    <th>Valuation Date</th>
                                    <th class="text-end">Gross Valuation</th>
                                    <th class="text-end">Retention Deducted</th>
                                    <th class="text-end">Net Certified Claim</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($job->valuations as $val)
                                <tr>
                                    <td>
                                        <a href="{{ route('commercial.valuations.show', $val) }}" class="fw-bold font-monospace text-primary">
                                            {{ $val->application_number }}
                                        </a>
                                    </td>
                                    <td>{{ $val->valuation_date->format('d M Y') }}</td>
                                    <td class="text-end tabular-nums">£{{ number_format($val->gross_valuation, 2) }}</td>
                                    <td class="text-end text-danger tabular-nums">-£{{ number_format($val->retention_deduction, 2) }}</td>
                                    <td class="text-end fw-bold text-success tabular-nums">£{{ number_format($val->certified_amount ?? $val->net_claim_amount, 2) }}</td>
                                    <td>
                                        <span class="badge {{ $val->status === 'paid' ? 'bg-label-success' : 'bg-label-info' }}">
                                            {{ ucfirst($val->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="text-center py-3 text-muted">No payment applications issued.</td></tr>
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
