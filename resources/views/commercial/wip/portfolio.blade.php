@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Breadcrumb & Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-label-primary px-2 py-1"><i class="ti tabler-chart-pie me-1"></i> Commercial Intelligence</span>
                <span class="text-muted fs-7">&bull; Multi-Project Cost Control</span>
            </div>
            <h4 class="fw-bold mb-1 text-dark">
                Live WIP Job Costing & Commercial Portfolio
            </h4>
            <p class="text-muted mb-0 small">Real-time 4-pillar cost ingestion (Materials, Labor, Subcontractors, Plant), earned value metrics, and balance sheet WIP valuation.</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary" onclick="window.print();">
                <i class="ti tabler-printer me-1"></i> Print Portfolio Report
            </button>
            <a href="{{ route('commercial.valuations.index') }}" class="btn btn-primary">
                <i class="ti tabler-receipt me-1"></i> Payment Valuations
            </a>
        </div>
    </div>

    <!-- Executive KPI Metric Widgets -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="neo-kpi-card kpi-primary h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-subtitle">Portfolio Contract Value</div>
                        <div class="kpi-value text-primary">£{{ number_format($overview['total_contract_sum'], 2) }}</div>
                    </div>
                    <span class="badge bg-label-primary p-2 rounded-3"><i class="ti tabler-file-certificate fs-4"></i></span>
                </div>
                <div class="kpi-footnote">Quoted revenue + approved client variations</div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="neo-kpi-card kpi-danger h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-subtitle">Total Incurred Cost (4 Pillars)</div>
                        <div class="kpi-value text-danger">£{{ number_format($overview['total_actual_costs'], 2) }}</div>
                    </div>
                    <span class="badge bg-label-danger p-2 rounded-3"><i class="ti tabler-coins fs-4"></i></span>
                </div>
                <div class="kpi-footnote">Materials &bull; Burdened Labor &bull; CIS &bull; Plant Accruals</div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="neo-kpi-card kpi-success h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-subtitle">Live Gross Margin</div>
                        <div class="kpi-value text-success">{{ number_format($overview['average_margin_percent'], 1) }}%</div>
                    </div>
                    <span class="badge bg-label-success p-2 rounded-3"><i class="ti tabler-chart-arrows-vertical fs-4"></i></span>
                </div>
                <div class="kpi-footnote">£{{ number_format($overview['total_gross_profit'], 2) }} commercial gross profit</div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="neo-kpi-card kpi-info h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-subtitle">Net WIP Asset Valuation</div>
                        <div class="kpi-value text-info">£{{ number_format($overview['total_wip_asset'], 2) }}</div>
                    </div>
                    <span class="badge bg-label-info p-2 rounded-3"><i class="ti tabler-scale fs-4"></i></span>
                </div>
                <div class="kpi-footnote">Incurred unbilled expenditure asset</div>
            </div>
        </div>
    </div>

    <!-- Commercial Health Monitor & 4-Pillar Visual Breakdown -->
    @php
        $totMat = collect($overview['jobs'])->sum(fn($j) => $j['pillars']['materials']['actual_cost']);
        $totLab = collect($overview['jobs'])->sum(fn($j) => $j['pillars']['labor']['burdened_cost']);
        $totSub = collect($overview['jobs'])->sum(fn($j) => $j['pillars']['subcontractors']['gross_cost']);
        $totPlt = collect($overview['jobs'])->sum(fn($j) => $j['pillars']['plant']['accrued_cost']);
        $grandCost = max(0.01, $totMat + $totLab + $totSub + $totPlt);
        $pMat = round(($totMat / $grandCost) * 100, 1);
        $pLab = round(($totLab / $grandCost) * 100, 1);
        $pSub = round(($totSub / $grandCost) * 100, 1);
        $pPlt = round(($totPlt / $grandCost) * 100, 1);
    @endphp

    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center g-3">
                <div class="col-lg-6 border-end-lg">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-bold small text-uppercase text-muted">Portfolio 4-Cost Pillar Absorption Ratio</span>
                        <span class="text-muted small">Total Cost: £{{ number_format($grandCost, 2) }}</span>
                    </div>
                    <div class="neo-pillar-progress-track">
                        <div class="neo-pillar-segment segment-materials" style="width: {{ $pMat }}%;" title="Materials: {{ $pMat }}%"></div>
                        <div class="neo-pillar-segment segment-labor" style="width: {{ $pLab }}%;" title="Labor: {{ $pLab }}%"></div>
                        <div class="neo-pillar-segment segment-subs" style="width: {{ $pSub }}%;" title="Subcontractors: {{ $pSub }}%"></div>
                        <div class="neo-pillar-segment segment-plant" style="width: {{ $pPlt }}%;" title="Plant Hire: {{ $pPlt }}%"></div>
                    </div>
                    <div class="neo-pillar-legend">
                        <div class="neo-pillar-legend-item">
                            <span class="neo-legend-swatch segment-materials"></span>
                            <span>Materials {{ $pMat }}%</span>
                        </div>
                        <div class="neo-pillar-legend-item">
                            <span class="neo-legend-swatch segment-labor"></span>
                            <span>Labor {{ $pLab }}%</span>
                        </div>
                        <div class="neo-pillar-legend-item">
                            <span class="neo-legend-swatch segment-subs"></span>
                            <span>Subcontractors {{ $pSub }}%</span>
                        </div>
                        <div class="neo-pillar-legend-item">
                            <span class="neo-legend-swatch segment-plant"></span>
                            <span>Plant Hire {{ $pPlt }}%</span>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 ps-lg-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold small text-uppercase text-muted">Active Project Margin Health</span>
                        <span class="badge bg-label-secondary">{{ $overview['total_jobs_tracked'] }} Projects Tracked</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pt-1">
                        <div class="d-flex align-items-center gap-2">
                            <span class="status-indicator-dot dot-healthy"></span>
                            <div>
                                <span class="fw-bold text-dark">{{ $overview['health_breakdown']['healthy'] }}</span>
                                <small class="text-muted d-block" style="font-size: 0.72rem;">Healthy (&ge; 30%)</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="status-indicator-dot dot-warning"></span>
                            <div>
                                <span class="fw-bold text-dark">{{ $overview['health_breakdown']['warning'] }}</span>
                                <small class="text-muted d-block" style="font-size: 0.72rem;">Warning (20-30%)</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="status-indicator-dot dot-critical"></span>
                            <div>
                                <span class="fw-bold text-dark">{{ $overview['health_breakdown']['critical'] }}</span>
                                <small class="text-muted d-block" style="font-size: 0.72rem;">Critical (&lt; 20%)</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Commercial Projects Ledger -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2 border-bottom">
            <div>
                <h5 class="card-title mb-0">Project Financial Cost Ledger & Work-in-Progress</h5>
                <small class="text-muted">Live reconciliation across all contract commitments and revenue certifications</small>
            </div>
            <div>
                <input type="text" id="projectSearch" class="form-control form-control-sm" placeholder="Quick filter projects..." style="width: 220px;">
            </div>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover" id="projectsTable">
                <thead>
                    <tr>
                        <th>Job Ref</th>
                        <th>Project & Client</th>
                        <th class="text-end">Contract Sum (£)</th>
                        <th class="text-end">Materials (£)</th>
                        <th class="text-end">Labor (£)</th>
                        <th class="text-end">Subcontractors (£)</th>
                        <th class="text-end">Plant Hire (£)</th>
                        <th class="text-end text-danger fw-bold">Total Incurred</th>
                        <th class="text-end fw-bold">Live Margin</th>
                        <th class="text-end">WIP Position</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($overview['jobs'] as $row)
                    <tr>
                        <td>
                            <a href="{{ route('commercial.wip.job', $row['job_id']) }}" class="fw-bold text-primary font-monospace">
                                JOB-{{ str_pad($row['job_id'], 4, '0', STR_PAD_LEFT) }}
                            </a>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                @if($row['commercial_status'] === 'healthy')
                                    <span class="status-indicator-dot dot-healthy" title="Healthy Margin >= 30%"></span>
                                @elseif($row['commercial_status'] === 'warning')
                                    <span class="status-indicator-dot dot-warning" title="Warning Margin 20-30%"></span>
                                @else
                                    <span class="status-indicator-dot dot-critical" title="Critical Margin < 20%"></span>
                                @endif
                                <div>
                                    <strong class="text-dark">{{ Str::limit($row['job_name'], 25) }}</strong>
                                    <div class="small text-muted">{{ $row['customer_name'] }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="text-end fw-bold text-dark tabular-nums">
                            £{{ number_format($row['revised_contract_sum'], 2) }}
                            @if($row['approved_variations'] > 0)
                                <small class="text-success d-block fw-normal">+£{{ number_format($row['approved_variations'], 2) }} var</small>
                            @endif
                        </td>
                        <td class="text-end tabular-nums">£{{ number_format($row['pillars']['materials']['actual_cost'], 2) }}</td>
                        <td class="text-end tabular-nums">£{{ number_format($row['pillars']['labor']['burdened_cost'], 2) }}</td>
                        <td class="text-end tabular-nums">£{{ number_format($row['pillars']['subcontractors']['gross_cost'], 2) }}</td>
                        <td class="text-end tabular-nums">£{{ number_format($row['pillars']['plant']['accrued_cost'], 2) }}</td>
                        <td class="text-end fw-bold text-danger tabular-nums">
                            £{{ number_format($row['total_actual_cost'], 2) }}
                        </td>
                        <td class="text-end">
                            @if($row['commercial_status'] === 'healthy')
                                <span class="badge bg-label-success fs-7">
                                    <i class="ti tabler-trending-up me-1"></i>{{ number_format($row['live_gross_margin_percent'], 1) }}%
                                </span>
                            @elseif($row['commercial_status'] === 'warning')
                                <span class="badge bg-label-warning fs-7">
                                    <i class="ti tabler-alert-triangle me-1"></i>{{ number_format($row['live_gross_margin_percent'], 1) }}%
                                </span>
                            @else
                                <span class="badge bg-label-danger fs-7">
                                    <i class="ti tabler-trending-down me-1"></i>{{ number_format($row['live_gross_margin_percent'], 1) }}%
                                </span>
                            @endif
                            <small class="text-muted d-block mt-1 tabular-nums">GP: £{{ number_format($row['live_gross_profit'], 2) }}</small>
                        </td>
                        <td class="text-end">
                            @if($row['wip_balance'] >= 0)
                                <span class="text-primary fw-bold tabular-nums">£{{ number_format($row['wip_balance'], 2) }}</span>
                                <small class="text-muted d-block">Unbilled Cost</small>
                            @else
                                <span class="text-secondary fw-semibold tabular-nums">£{{ number_format(abs($row['wip_balance']), 2) }}</span>
                                <small class="text-muted d-block">Overbilled</small>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('commercial.wip.job', $row['job_id']) }}" class="btn btn-sm btn-outline-primary">
                                <i class="ti tabler-chart-bar me-1"></i> Cost Sheet
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center py-5 text-muted">
                            <i class="ti tabler-folder-off fs-1 d-block mb-2 text-secondary"></i>
                            No commercial projects found in portfolio.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('projectSearch');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const q = this.value.toLowerCase();
                const rows = document.querySelectorAll('#projectsTable tbody tr');
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(q) ? '' : 'none';
                });
            });
        }
    });
</script>
@endsection
