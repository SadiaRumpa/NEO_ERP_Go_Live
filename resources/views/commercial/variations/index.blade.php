@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-1 mb-1">
                <span class="text-muted fw-light">Commercial Contracting /</span> Variations & Change Orders
            </h4>
            <p class="text-muted mb-0">Extra works, scope variations, digital client sign-off, and revised contract values.</p>
        </div>
        <div>
            <a href="{{ route('commercial.variations.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> New Variation
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
        <div class="card-header border-bottom">
            <form method="GET" action="{{ route('commercial.variations.index') }}" class="row g-3">
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
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved (Contract Revised)</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1"><i class="bx bx-filter-alt me-1"></i> Filter</button>
                    <a href="{{ route('commercial.variations.index') }}" class="btn btn-outline-secondary"><i class="bx bx-reset"></i></a>
                </div>
            </form>
        </div>
        <div class="px-4 py-3 d-flex justify-content-between align-items-center flex-wrap gap-2 border-bottom">
            @include('partials.per-page-selector', ['paginator' => $variations])
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Variation #</th>
                        <th>Title / Scope</th>
                        <th>Job</th>
                        <th class="text-end">Cost Impact (£)</th>
                        <th class="text-end">Markup %</th>
                        <th class="text-end">Quoted Value (£)</th>
                        <th>Status</th>
                        <th>Sign-Off Details</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($variations as $var)
                    <tr>
                        <td>
                            <a href="{{ route('commercial.variations.show', $var) }}" class="fw-bold text-primary font-monospace">
                                {{ $var->variation_number }}
                            </a>
                        </td>
                        <td>
                            <strong class="text-dark">{{ $var->title }}</strong>
                            @if($var->description)
                            <div class="small text-muted text-truncate" style="max-width: 250px;">{{ $var->description }}</div>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-label-secondary">
                                Job #{{ $var->job_id }} &bull; {{ Str::limit($var->job->job_title ?? $var->job->job_name, 20) }}
                            </span>
                        </td>
                        <td class="text-end tabular-nums">£{{ number_format($var->cost_amount, 2) }}</td>
                        <td class="text-end tabular-nums">{{ number_format($var->markup_percentage, 1) }}%</td>
                        <td class="text-end fw-bold text-dark tabular-nums fs-6">£{{ number_format($var->quoted_amount, 2) }}</td>
                        <td>
                            @if($var->status === 'approved')
                                <span class="badge bg-label-success">
                                    <span class="status-indicator-dot dot-healthy me-1"></span> Approved
                                </span>
                            @elseif($var->status === 'rejected')
                                <span class="badge bg-label-danger">
                                    <span class="status-indicator-dot dot-critical me-1"></span> Rejected
                                </span>
                            @else
                                <span class="badge bg-label-warning">
                                    <span class="status-indicator-dot dot-warning me-1"></span> Draft
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($var->signed_at)
                                <div class="small text-success fw-bold"><i class="bx bx-check-shield me-1"></i>{{ $var->client_signer_name }}</div>
                                <small class="text-muted">{{ $var->signed_at->format('d M Y H:i') }}</small>
                            @else
                                <span class="badge bg-label-warning"><i class="bx bx-time me-1"></i>Awaiting E-Sign</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('commercial.variations.show', $var) }}" class="btn btn-sm btn-outline-primary shadow-sm">
                                <i class="bx bx-show me-1"></i> View
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">No variations logged.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="text-muted small">
                Showing {{ $variations->firstItem() ?? 0 }} to {{ $variations->lastItem() ?? 0 }} of {{ $variations->total() }} results
            </div>
            <div>
                {{ $variations->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
