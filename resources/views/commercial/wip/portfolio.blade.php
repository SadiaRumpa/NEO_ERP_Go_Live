@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-1 mb-1">
                <span class="text-muted fw-light">Commercial Finance /</span> Live WIP Job Costing & Commercial Portfolio
            </h4>
            <p class="text-muted mb-0">Live 4-pillar cost ingestion (Materials, Labor, Subcontractors, Plant), real-time margins, and WIP valuation.</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-primary" onclick="window.print();">
                <i class="bx bx-printer me-1"></i> Print Portfolio Statement
            </button>
        </div>
    </div>

    <!-- Executive KPI Bar -->
    <div class="row mb-4">
        <div class="col-md-3 mb-2">
            <div class="card bg-label-primary">
                <div class="card-body">
                    <h6 class="card-subtitle text-primary mb-1 fw-bold">Portfolio Revised Contract Sum</h6>
                    <h3 class="card-title fw-bold mb-0 text-primary">£{{ number_format($overview['total_contract_sum'], 2) }}</h3>
                    <small class="text-muted">Quoted revenue + approved variations</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="card bg-label-danger">
                <div class="card-body">
                    <h6 class="card-subtitle text-danger mb-1 fw-bold">Total Incurred Costs (4 Pillars)</h6>
                    <h3 class="card-title fw-bold mb-0 text-danger">£{{ number_format($overview['total_actual_costs'], 2) }}</h3>
                    <small class="text-muted">Materials &bull; Labor &bull; Subs &bull; Plant</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="card bg-label-success">
                <div class="card-body">
                    <h6 class="card-subtitle text-success mb-1 fw-bold">Live Portfolio Gross Margin</h6>
                    <h3 class="card-title fw-bold mb-0 text-success">
                        {{ number_format($overview['average_margin_percent'], 1) }}%
                    </h3>
                    <small class="text-muted">£{{ number_format($overview['total_gross_profit'], 2) }} live gross profit</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="card bg-label-info">
                <div class="card-body">
                    <h6 class="card-subtitle text-info mb-1 fw-bold">Work-in-Progress (WIP) Asset</h6>
                    <h3 class="card-title fw-bold mb-0 text-info">£{{ number_format($overview['total_wip_asset'], 2) }}</h3>
                    <small class="text-muted">Incurred costs unbilled on valuations</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Commercial Health Monitor Strip -->
    <div class="card mb-4">
        <div class="card-body py-3">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-success p-2"><i class="bx bx-check-circle"></i></span>
                    <div>
                        <div class="fw-bold">{{ $overview['health_breakdown']['healthy'] }} Jobs Healthy</div>
                        <small class="text-muted">Margin &ge; 30%</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-warning p-2"><i class="bx bx-error"></i></span>
                    <div>
                        <div class="fw-bold">{{ $overview['health_breakdown']['warning'] }} Jobs Warning</div>
                        <small class="text-muted">Margin 20% - 29.9%</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-danger p-2"><i class="bx bx-error-circle"></i></span>
                    <div>
                        <div class="fw-bold">{{ $overview['health_breakdown']['critical'] }} Jobs Critical</div>
                        <small class="text-muted">Margin &lt; 20% (Cost Erosion)</small>
                    </div>
                </div>
                <div class="text-end">
                    <span class="badge bg-label-secondary fs-6">{{ $overview['total_jobs_tracked'] }} Projects Active</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Commercial Jobs Ledger -->
    <div class="card">
        <div class="card-header border-bottom">
            <h5 class="card-title mb-0">Live Commercial Projects Cost & WIP Ledger</h5>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Job #</th>
                        <th>Project Title & Client</th>
                        <th class="text-end">Contract Sum (£)</th>
                        <th class="text-end">Materials (£)</th>
                        <th class="text-end">Labor (£)</th>
                        <th class="text-end">Subcontractors (£)</th>
                        <th class="text-end">Plant (£)</th>
                        <th class="text-end fw-bold text-danger">Total Cost (£)</th>
                        <th class="text-end fw-bold">Gross Margin</th>
                        <th class="text-end">WIP Position</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($overview['jobs'] as $row)
                    <tr>
                        <td>
                            <a href="{{ route('commercial.wip.job', $row['job_id']) }}" class="fw-bold text-primary">
                                Job #{{ $row['job_id'] }}
                            </a>
                        </td>
                        <td>
                            <strong>{{ Str::limit($row['job_name'], 25) }}</strong>
                            <div class="small text-muted">{{ $row['customer_name'] }}</div>
                        </td>
                        <td class="text-end fw-semibold">
                            £{{ number_format($row['revised_contract_sum'], 2) }}
                            @if($row['approved_variations'] > 0)
                                <small class="text-success d-block">+£{{ number_format($row['approved_variations'], 2) }} var</small>
                            @endif
                        </td>
                        <td class="text-end">£{{ number_format($row['pillars']['materials']['actual_cost'], 2) }}</td>
                        <td class="text-end">£{{ number_format($row['pillars']['labor']['burdened_cost'], 2) }}</td>
                        <td class="text-end">£{{ number_format($row['pillars']['subcontractors']['gross_cost'], 2) }}</td>
                        <td class="text-end">£{{ number_format($row['pillars']['plant']['accrued_cost'], 2) }}</td>
                        <td class="text-end fw-bold text-danger">
                            £{{ number_format($row['total_actual_cost'], 2) }}
                        </td>
                        <td class="text-end">
                            @if($row['commercial_status'] === 'healthy')
                                <span class="badge bg-label-success fs-6">{{ number_format($row['live_gross_margin_percent'], 1) }}%</span>
                            @elseif($row['commercial_status'] === 'warning')
                                <span class="badge bg-label-warning fs-6">{{ number_format($row['live_gross_margin_percent'], 1) }}%</span>
                            @else
                                <span class="badge bg-label-danger fs-6">{{ number_format($row['live_gross_margin_percent'], 1) }}%</span>
                            @endif
                            <small class="text-muted d-block">£{{ number_format($row['live_gross_profit'], 2) }}</small>
                        </td>
                        <td class="text-end">
                            @if($row['wip_balance'] >= 0)
                                <span class="text-primary fw-bold">£{{ number_format($row['wip_balance'], 2) }}</span>
                                <small class="text-muted d-block">Unbilled Cost</small>
                            @else
                                <span class="text-secondary fw-semibold">£{{ number_format(abs($row['wip_balance']), 2) }}</span>
                                <small class="text-muted d-block">Overbilled</small>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('commercial.wip.job', $row['job_id']) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bx bx-bar-chart-alt-2 me-1"></i> Cost Ledger
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center py-4 text-muted">No projects found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
