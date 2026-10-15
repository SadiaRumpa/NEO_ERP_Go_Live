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

    <!-- KPI Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-2">
            <div class="card bg-label-primary">
                <div class="card-body">
                    <h6 class="card-subtitle text-primary mb-1 fw-bold">Active Plant Hires</h6>
                    <h3 class="card-title fw-bold mb-0 text-primary">{{ $alerts['active_count'] }}</h3>
                    <small class="text-muted">Currently deployed on site</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="card bg-label-warning">
                <div class="card-body">
                    <h6 class="card-subtitle text-warning mb-1 fw-bold">Expiring in 48h</h6>
                    <h3 class="card-title fw-bold mb-0 text-warning">{{ count($alerts['expiring']) }}</h3>
                    <small class="text-muted">Prepare off-hire reference</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="card bg-label-danger">
                <div class="card-body">
                    <h6 class="card-subtitle text-danger mb-1 fw-bold">Overdue Standby Items</h6>
                    <h3 class="card-title fw-bold mb-0 text-danger">{{ count($alerts['overdue']) }}</h3>
                    <small class="text-muted">Eroding project gross margin</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="card bg-label-info">
                <div class="card-body">
                    <h6 class="card-subtitle text-info mb-1 fw-bold">Active Accrued Hire Cost</h6>
                    <h3 class="card-title fw-bold mb-0 text-info">£{{ number_format($alerts['total_accrued_active'], 2) }}</h3>
                    <small class="text-muted">Direct Job WIP commitment</small>
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
                            <a href="{{ route('plant-hires.show', $hire) }}" class="fw-bold text-primary">
                                {{ $hire->hire_reference }}
                            </a>
                        </td>
                        <td>
                            <strong>{{ $hire->item_name }}</strong>
                            <div class="small text-muted text-capitalize">{{ str_replace('_', ' ', $hire->category) }}</div>
                        </td>
                        <td>{{ $hire->supplier->name ?? 'N/A' }}</td>
                        <td>
                            <span class="badge bg-label-secondary">
                                Job #{{ $hire->job_id }} - {{ Str::limit($hire->job->job_title ?? $hire->job->job_name, 18) }}
                            </span>
                        </td>
                        <td>
                            <div><i class="bx bx-calendar me-1 text-muted"></i>{{ $hire->start_date->format('d M Y') }}</div>
                            <small class="{{ $hire->isOverdue() ? 'text-danger fw-bold' : ($hire->isExpiringSoon() ? 'text-warning fw-bold' : 'text-muted') }}">
                                Expected off: {{ $hire->expected_end_date->format('d M Y') }}
                                @if($hire->isOverdue())
                                    <span class="badge bg-danger ms-1">OVERDUE</span>
                                @elseif($hire->isExpiringSoon())
                                    <span class="badge bg-warning ms-1">DUE SOON</span>
                                @endif
                            </small>
                        </td>
                        <td class="text-end">
                            @if($hire->weekly_rate > 0)
                                £{{ number_format($hire->weekly_rate, 2) }} /wk
                            @else
                                £{{ number_format($hire->daily_rate, 2) }} /day
                            @endif
                        </td>
                        <td class="text-end fw-bold text-dark">
                            £{{ number_format($hire->accrued_hire_cost, 2) }}
                        </td>
                        <td>
                            @if($hire->status === 'active')
                                <span class="badge bg-label-success">Active On-Hire</span>
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
        @if($plantHires->hasPages())
        <div class="card-footer border-top">
            {{ $plantHires->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
