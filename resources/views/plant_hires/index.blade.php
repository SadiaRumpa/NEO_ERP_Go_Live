@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-1 mb-1"><span class="text-muted fw-light">Commercial Plant /</span> Plant & Equipment Hire Register</h4>
            <p class="text-muted mb-0">External equipment hire, off-hire tracking, proactive standby alerts, and real-time WIP accruals.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('plant-hires.alerts') }}" class="btn btn-outline-danger position-relative">
                <i class="bx bx-bell me-1"></i> Off-Hire Alerts
                @if(count($alerts['overdue']) + count($alerts['expiring']) > 0)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    {{ count($alerts['overdue']) + count($alerts['expiring']) }}
                </span>
                @endif
            </a>
            <a href="{{ route('plant-hires.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Book Plant Hire
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Enterprise KPI Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="neo-kpi-card kpi-primary h-100 shadow-sm">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-subtitle">Active Site Plant</div>
                        <div class="kpi-value text-primary">{{ $alerts['active_count'] }}</div>
                        <div class="kpi-footnote"><i class="bx bx-check-circle text-success me-1"></i>Currently deployed on site</div>
                    </div>
                    <div class="p-2 rounded bg-label-primary">
                        <i class="bx bx-wrench fs-4 text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="neo-kpi-card kpi-warning h-100 shadow-sm">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-subtitle">Expiring in 48h</div>
                        <div class="kpi-value text-warning">{{ count($alerts['expiring']) }}</div>
                        <div class="kpi-footnote"><i class="bx bx-time text-warning me-1"></i>Prepare off-hire reference</div>
                    </div>
                    <div class="p-2 rounded bg-label-warning">
                        <i class="bx bx-alarm-exclamation fs-4 text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="neo-kpi-card kpi-danger h-100 shadow-sm">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-subtitle">Overdue Standby Items</div>
                        <div class="kpi-value text-danger">{{ count($alerts['overdue']) }}</div>
                        <div class="kpi-footnote"><i class="bx bx-trending-down text-danger me-1"></i>Eroding project gross margin</div>
                    </div>
                    <div class="p-2 rounded bg-label-danger">
                        <i class="bx bx-error-alt fs-4 text-danger"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="neo-kpi-card kpi-info h-100 shadow-sm">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-subtitle">Active Accrued Cost</div>
                        <div class="kpi-value text-info tabular-nums">£{{ number_format($alerts['total_accrued_active'], 2) }}</div>
                        <div class="kpi-footnote"><i class="bx bx-line-chart text-info me-1"></i>Live Job WIP commitment</div>
                    </div>
                    <div class="p-2 rounded bg-label-info">
                        <i class="bx bx-pound fs-4 text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Table -->
    <div class="card">
        <div class="card-header border-bottom">
            <form method="GET" action="{{ route('plant-hires.index') }}" class="row g-3">
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active (On-Hire)</option>
                        <option value="off_hired" {{ request('status') == 'off_hired' ? 'selected' : '' }}>Off-Hired (Completed)</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        <option value="scaffolding" {{ request('category') == 'scaffolding' ? 'selected' : '' }}>Scaffolding</option>
                        <option value="mewp" {{ request('category') == 'mewp' ? 'selected' : '' }}>MEWPs / Powered Access</option>
                        <option value="telehandler" {{ request('category') == 'telehandler' ? 'selected' : '' }}>Telehandler / Forklift</option>
                        <option value="skips" {{ request('category') == 'skips' ? 'selected' : '' }}>Waste & Skips</option>
                        <option value="tools" {{ request('category') == 'tools' ? 'selected' : '' }}>Small Plant & Tools</option>
                        <option value="other" {{ request('category') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="supplier_id" class="form-select">
                        <option value="">All Suppliers</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}" {{ request('supplier_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1"><i class="bx bx-filter-alt me-1"></i> Filter</button>
                    <a href="{{ route('plant-hires.index') }}" class="btn btn-outline-secondary"><i class="bx bx-reset"></i></a>
                </div>
            </form>
        </div>
        <div class="px-4 py-3 d-flex justify-content-between align-items-center flex-wrap gap-2 border-bottom">
            @include('partials.per-page-selector', ['paginator' => $plantHires])
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Hire Ref</th>
                        <th>Equipment / Item</th>
                        <th>Supplier</th>
                        <th>Allocated Job</th>
                        <th>Hire Window</th>
                        <th class="text-end">Daily / Wkly Rate</th>
                        <th class="text-end">Accrued Cost</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($plantHires as $hire)
                    <tr>
                        <td>
                            <a href="{{ route('plant-hires.show', $hire) }}" class="fw-bold text-primary font-monospace">
                                {{ $hire->hire_reference }}
                            </a>
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $hire->item_name }}</div>
                            <div class="small text-muted text-capitalize"><i class="bx bx-tag-alt me-1"></i>{{ str_replace('_', ' ', $hire->category) }}</div>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $hire->supplier->name ?? 'N/A' }}</div>
                        </td>
                        <td>
                            <span class="badge bg-label-secondary">
                                Job #{{ $hire->job_id }} &bull; {{ Str::limit($hire->job->job_title ?? $hire->job->job_name, 18) }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-1">
                                <i class="bx bx-calendar text-muted"></i>
                                <span>{{ $hire->start_date->format('d M Y') }}</span>
                            </div>
                            <div class="small mt-1 {{ $hire->isOverdue() ? 'text-danger fw-bold' : ($hire->isExpiringSoon() ? 'text-warning fw-bold' : 'text-muted') }}">
                                Expected off: {{ $hire->expected_end_date->format('d M Y') }}
                                @if($hire->isOverdue())
                                    <span class="badge bg-danger ms-1"><span class="status-indicator-dot dot-critical me-1"></span>OVERDUE</span>
                                @elseif($hire->isExpiringSoon())
                                    <span class="badge bg-warning ms-1"><span class="status-indicator-dot dot-warning me-1"></span>DUE SOON</span>
                                @endif
                            </div>
                        </td>
                        <td class="text-end tabular-nums">
                            @if($hire->weekly_rate > 0)
                                £{{ number_format($hire->weekly_rate, 2) }} <span class="text-muted small">/wk</span>
                            @else
                                £{{ number_format($hire->daily_rate, 2) }} <span class="text-muted small">/day</span>
                            @endif
                        </td>
                        <td class="text-end fw-bold text-dark tabular-nums">
                            £{{ number_format($hire->accrued_hire_cost, 2) }}
                        </td>
                        <td>
                            @if($hire->status === 'active')
                                <span class="badge bg-label-success">
                                    <span class="status-indicator-dot dot-healthy me-1"></span>Active On-Hire
                                </span>
                            @elseif($hire->status === 'off_hired')
                                <span class="badge bg-label-secondary">Off-Hired</span>
                            @else
                                <span class="badge bg-label-warning">{{ ucfirst($hire->status) }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="{{ route('plant-hires.show', $hire) }}">
                                        <i class="bx bx-show-alt me-1"></i> View / Off-Hire
                                    </a>
                                    <a class="dropdown-item" href="{{ route('plant-hires.edit', $hire) }}">
                                        <i class="bx bx-edit-alt me-1"></i> Edit
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">No plant & equipment hire records found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="text-muted small">
                Showing {{ $plantHires->firstItem() ?? 0 }} to {{ $plantHires->lastItem() ?? 0 }} of {{ $plantHires->total() }} results
            </div>
            <div>
                {{ $plantHires->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
