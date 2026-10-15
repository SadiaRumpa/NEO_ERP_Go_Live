@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-1 mb-1">
                <span class="text-muted fw-light">Plant Management /</span> Off-Hire Proactive Alerts
            </h4>
            <p class="text-muted mb-0">Prevent standby fee cost erosion by taking timely off-hire action on active site plant.</p>
        </div>
        <div>
            <a href="{{ route('plant-hires.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Full Hire Register
            </a>
        </div>
    </div>

    <!-- Overdue Plant Standby Section -->
    <div class="card mb-4 border-danger shadow-sm">
        <div class="card-header bg-label-danger d-flex justify-content-between align-items-center py-3">
            <div>
                <h5 class="card-title text-danger mb-0 d-flex align-items-center gap-2">
                    <span class="status-indicator-dot dot-critical"></span>
                    <i class="bx bx-error-circle fs-5"></i> Overdue Plant Hires (Margin Standby Erosion)
                </h5>
                <small class="text-danger opacity-75">Contract term expired — continuous daily standby rates eroding contract margin</small>
            </div>
            <span class="badge bg-danger fs-6 px-3 py-2">
                <i class="bx bx-alarm-exclamation me-1"></i> {{ count($alerts['overdue']) }} Overdue
            </span>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Hire Ref</th>
                        <th>Equipment</th>
                        <th>Supplier</th>
                        <th>Allocated Job</th>
                        <th>Scheduled Off-Hire</th>
                        <th class="text-end">Daily Rate</th>
                        <th class="text-end">Accrued to Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($alerts['overdue'] as $hire)
                    <tr>
                        <td>
                            <a href="{{ route('plant-hires.show', $hire) }}" class="fw-bold text-danger font-monospace">
                                {{ $hire->hire_reference }}
                            </a>
                        </td>
                        <td>
                            <strong class="text-dark">{{ $hire->item_name }}</strong>
                            <div class="small text-muted text-capitalize"><i class="bx bx-tag-alt me-1"></i>{{ str_replace('_', ' ', $hire->category) }}</div>
                        </td>
                        <td><span class="fw-semibold">{{ $hire->supplier->name ?? 'N/A' }}</span></td>
                        <td>
                            <span class="badge bg-label-secondary">
                                Job #{{ $hire->job_id }} &bull; {{ Str::limit($hire->job->job_title ?? $hire->job->job_name, 20) }}
                            </span>
                        </td>
                        <td class="text-danger fw-bold">
                            <div><i class="bx bx-calendar-x me-1"></i>{{ $hire->expected_end_date->format('d M Y') }}</div>
                            <small class="badge bg-label-danger mt-1">{{ $hire->expected_end_date->diffForHumans() }}</small>
                        </td>
                        <td class="text-end tabular-nums font-monospace">£{{ number_format($hire->daily_rate, 2) }}</td>
                        <td class="text-end fw-bold text-danger tabular-nums font-monospace fs-6">£{{ number_format($hire->accrued_hire_cost, 2) }}</td>
                        <td>
                            <a href="{{ route('plant-hires.show', $hire) }}" class="btn btn-sm btn-danger shadow-sm">
                                <i class="bx bx-power-off me-1"></i> Off-Hire Now
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-success fw-bold">
                            <i class="bx bx-check-circle me-1"></i> No overdue plant hires! All equipment is within scheduled terms.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Expiring within 48 Hours Section -->
    <div class="card border-warning shadow-sm">
        <div class="card-header bg-label-warning d-flex justify-content-between align-items-center py-3">
            <div>
                <h5 class="card-title text-warning mb-0 d-flex align-items-center gap-2">
                    <span class="status-indicator-dot dot-warning"></span>
                    <i class="bx bx-time-five fs-5"></i> Expiring Within 48 Hours (Action Required)
                </h5>
                <small class="text-warning opacity-75">Obtain supplier off-hire reference before expiry to prevent automatic renewal charges</small>
            </div>
            <span class="badge bg-warning fs-6 px-3 py-2">
                <i class="bx bx-time me-1"></i> {{ count($alerts['expiring']) }} Due Soon
            </span>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Hire Ref</th>
                        <th>Equipment</th>
                        <th>Supplier</th>
                        <th>Allocated Job</th>
                        <th>Scheduled Off-Hire</th>
                        <th class="text-end">Daily Rate</th>
                        <th class="text-end">Accrued to Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($alerts['expiring'] as $hire)
                    <tr>
                        <td>
                            <a href="{{ route('plant-hires.show', $hire) }}" class="fw-bold text-warning font-monospace">
                                {{ $hire->hire_reference }}
                            </a>
                        </td>
                        <td>
                            <strong class="text-dark">{{ $hire->item_name }}</strong>
                            <div class="small text-muted text-capitalize"><i class="bx bx-tag-alt me-1"></i>{{ str_replace('_', ' ', $hire->category) }}</div>
                        </td>
                        <td><span class="fw-semibold">{{ $hire->supplier->name ?? 'N/A' }}</span></td>
                        <td>
                            <span class="badge bg-label-secondary">
                                Job #{{ $hire->job_id }} &bull; {{ Str::limit($hire->job->job_title ?? $hire->job->job_name, 20) }}
                            </span>
                        </td>
                        <td class="text-warning fw-bold">
                            <div><i class="bx bx-calendar me-1"></i>{{ $hire->expected_end_date->format('d M Y') }}</div>
                            <small class="badge bg-label-warning mt-1">{{ $hire->expected_end_date->diffForHumans() }}</small>
                        </td>
                        <td class="text-end tabular-nums font-monospace">£{{ number_format($hire->daily_rate, 2) }}</td>
                        <td class="text-end fw-bold text-dark tabular-nums font-monospace fs-6">£{{ number_format($hire->accrued_hire_cost, 2) }}</td>
                        <td>
                            <a href="{{ route('plant-hires.show', $hire) }}" class="btn btn-sm btn-outline-warning shadow-sm">
                                <i class="bx bx-show me-1"></i> Review / Off-Hire
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            <i class="bx bx-check-circle me-1 text-success"></i> No plant hires scheduled to expire within the next 48 hours.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
