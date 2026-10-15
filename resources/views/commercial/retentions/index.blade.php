@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-1 mb-1">
                <span class="text-muted fw-light">Commercial Contracting /</span> Retention Accounting Ledger
            </h4>
            <p class="text-muted mb-0">Track cash retention withheld on progress valuations, Practical Completion (PC) releases, and Defects Liability releases.</p>
        </div>
        <div>
            <a href="{{ route('commercial.valuations.index') }}" class="btn btn-outline-primary">
                <i class="bx bx-receipt me-1"></i> Applications for Payment
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
        <div class="col-md-4">
            <div class="neo-kpi-card kpi-warning h-100 shadow-sm">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-subtitle">Total Cash Retention Held</div>
                        <div class="kpi-value text-warning tabular-nums">£{{ number_format($totalHeld, 2) }}</div>
                        <div class="kpi-footnote"><i class="bx bx-lock-alt me-1"></i>Awaiting PC or Defects liability release</div>
                    </div>
                    <div class="p-2 rounded bg-label-warning">
                        <i class="bx bx-shield-quarter fs-4 text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="neo-kpi-card kpi-success h-100 shadow-sm">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-subtitle">Total Retention Released</div>
                        <div class="kpi-value text-success tabular-nums">£{{ number_format($totalReleased, 2) }}</div>
                        <div class="kpi-footnote"><i class="bx bx-check-circle text-success me-1"></i>Released to contractor cashflow</div>
                    </div>
                    <div class="p-2 rounded bg-label-success">
                        <i class="bx bx-coin-stack fs-4 text-success"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="neo-kpi-card kpi-info h-100 shadow-sm">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-subtitle">Active Retention Contracts</div>
                        <div class="kpi-value text-info">{{ $retentions->total() }}</div>
                        <div class="kpi-footnote"><i class="bx bx-buildings text-info me-1"></i>Linked across commercial contracts</div>
                    </div>
                    <div class="p-2 rounded bg-label-info">
                        <i class="bx bx-file-blank fs-4 text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="card">
        <div class="card-header border-bottom">
            <form method="GET" action="{{ route('commercial.retentions.index') }}" class="row g-3">
                <div class="col-md-4">
                    <select name="job_id" class="form-select">
                        <option value="">All Jobs</option>
                        @foreach($jobs as $j)
                            <option value="{{ $j->id }}" {{ request('job_id') == $j->id ? 'selected' : '' }}>
                                Job #{{ $j->id }} - {{ Str::limit($j->job_title ?? $j->job_name, 30) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="held" {{ request('status') == 'held' ? 'selected' : '' }}>Held (Full Amount Withheld)</option>
                        <option value="partially_released" {{ request('status') == 'partially_released' ? 'selected' : '' }}>Partially Released (PC Done)</option>
                        <option value="fully_released" {{ request('status') == 'fully_released' ? 'selected' : '' }}>Fully Released (Defects Done)</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1"><i class="bx bx-filter-alt me-1"></i> Filter</button>
                    <a href="{{ route('commercial.retentions.index') }}" class="btn btn-outline-secondary"><i class="bx bx-reset"></i></a>
                </div>
            </form>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Retention Ref</th>
                        <th>Job</th>
                        <th>Valuation Application</th>
                        <th class="text-end">Total Retained</th>
                        <th class="text-end">PC Release (50%)</th>
                        <th class="text-end">Defects Release (50%)</th>
                        <th class="text-end">Balance Held</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($retentions as $ret)
                    <tr>
                        <td><span class="fw-bold font-monospace text-primary fs-6">{{ $ret->retention_reference }}</span></td>
                        <td>
                            <span class="badge bg-label-secondary">
                                Job #{{ $ret->job_id }} &bull; {{ Str::limit($ret->job->job_title ?? $ret->job->job_name, 20) }}
                            </span>
                        </td>
                        <td>
                            @if($ret->valuation)
                            <a href="{{ route('commercial.valuations.show', $ret->valuation) }}" class="fw-semibold text-primary">
                                <i class="bx bx-receipt me-1"></i>{{ $ret->valuation->application_number }}
                            </a>
                            @else
                            <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td class="text-end fw-semibold tabular-nums">£{{ number_format($ret->total_retained_amount, 2) }}</td>
                        <td class="text-end tabular-nums">
                            £{{ number_format($ret->pc_release_amount, 2) }}
                            @if($ret->pc_released_at)
                                <div class="badge bg-label-success d-block mt-1"><i class="bx bx-check me-1"></i>{{ $ret->pc_released_at->format('d/m/y') }}</div>
                            @endif
                        </td>
                        <td class="text-end tabular-nums">
                            £{{ number_format($ret->defects_release_amount, 2) }}
                            @if($ret->defects_released_at)
                                <div class="badge bg-label-success d-block mt-1"><i class="bx bx-check me-1"></i>{{ $ret->defects_released_at->format('d/m/y') }}</div>
                            @endif
                        </td>
                        <td class="text-end fw-bold text-warning fs-6 tabular-nums">
                            £{{ number_format($ret->balance_held, 2) }}
                        </td>
                        <td>
                            @if($ret->status === 'fully_released')
                                <span class="badge bg-label-success">
                                    <span class="status-indicator-dot dot-healthy me-1"></span>Fully Released
                                </span>
                            @elseif($ret->status === 'partially_released')
                                <span class="badge bg-label-info">
                                    <span class="status-indicator-dot dot-warning me-1"></span>PC Released (50%)
                                </span>
                            @else
                                <span class="badge bg-label-warning">
                                    <span class="status-indicator-dot dot-warning me-1"></span>Held in Escrow
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                @if(!$ret->pc_released_at)
                                <form method="POST" action="{{ route('commercial.retentions.release-pc', $ret) }}" onsubmit="return confirm('Authorize Practical Completion 50% retention release?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-info" title="Release Practical Completion (50%)">
                                        <i class="bx bx-check me-1"></i> Release PC
                                    </button>
                                </form>
                                @elseif(!$ret->defects_released_at)
                                <form method="POST" action="{{ route('commercial.retentions.release-defects', $ret) }}" onsubmit="return confirm('Authorize Defects Liability remaining retention release?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Release Defects Liability (Final 50%)">
                                        <i class="bx bx-check-double me-1"></i> Release Final
                                    </button>
                                </form>
                                @else
                                <span class="badge bg-label-secondary">Settled</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">No retention ledger records found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($retentions->hasPages())
        <div class="card-footer border-top">
            {{ $retentions->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
