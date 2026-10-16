@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-1 mb-1"><span class="text-muted fw-light">Labor & Operations /</span> Rate Cards</h4>
            <p class="text-muted mb-0">Worker loaded hourly rates, NI burden, pension, and overhead multipliers for live job costing.</p>
        </div>
        <div>
            <a href="{{ route('labor.rate-cards.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Add Rate Card
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card">
        <div class="px-4 py-3 d-flex justify-content-between align-items-center flex-wrap gap-2 border-bottom">
            @include('partials.per-page-selector', ['paginator' => $rateCards])
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Operative / Worker</th>
                        <th>Base Rate (£/hr)</th>
                        <th>Overtime (1.5x / 2.0x)</th>
                        <th>Burden Markup</th>
                        <th>Fully Burdened Rate</th>
                        <th>Effective From</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rateCards as $rc)
                    <tr>
                        <td>
                            <strong>{{ $rc->user->name ?? 'User #' . $rc->user_id }}</strong>
                            <div class="text-muted small">{{ $rc->user->email ?? '' }}</div>
                        </td>
                        <td class="fw-bold">£{{ number_format($rc->base_hourly_rate, 2) }}/hr</td>
                        <td>
                            £{{ number_format($rc->base_hourly_rate * $rc->overtime_multiplier_1_5, 2) }} /
                            £{{ number_format($rc->base_hourly_rate * $rc->overtime_multiplier_2_0, 2) }}
                        </td>
                        <td>
                            <span class="badge bg-label-info">+{{ number_format($rc->total_burden_percent, 1) }}%</span>
                            <small class="text-muted">({{ $rc->burden_multiplier }}x)</small>
                        </td>
                        <td class="fw-bold text-success fs-6">
                            £{{ number_format($rc->burdened_hourly_rate, 2) }}/hr
                        </td>
                        <td>{{ $rc->effective_from?->format('d M Y') }}</td>
                        <td>
                            @if($rc->is_active)
                                <span class="badge bg-label-success">Active</span>
                            @else
                                <span class="badge bg-label-secondary">Archived</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('labor.rate-cards.edit', $rc) }}" class="btn btn-sm btn-icon btn-outline-primary" title="Edit Rate Card">
                                <i class="bx bx-edit-alt"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            <i class="bx bx-id-card fs-1 d-block mb-2"></i>
                            No labor rate cards configured yet. Click "Add Rate Card" to set loaded operative rates.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="text-muted small">
                Showing {{ $rateCards->firstItem() ?? 0 }} to {{ $rateCards->lastItem() ?? 0 }} of {{ $rateCards->total() }} results
            </div>
            <div>
                {{ $rateCards->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
