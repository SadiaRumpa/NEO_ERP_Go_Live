@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-1 mb-1">
                <span class="text-muted fw-light">Commercial Contracting /</span> Applications for Payment (Valuations)
            </h4>
            <p class="text-muted mb-0">Monthly progress claims, cumulative gross valuations, retention deductions, and certified amounts.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('commercial.retentions.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-lock-alt me-1"></i> Retention Ledger
            </a>
            <a href="{{ route('commercial.valuations.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> New Application
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
            <form method="GET" action="{{ route('commercial.valuations.index') }}" class="row g-3">
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
                        <option value="certified" {{ request('status') == 'certified' ? 'selected' : '' }}>Certified</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1"><i class="bx bx-filter-alt me-1"></i> Filter</button>
                    <a href="{{ route('commercial.valuations.index') }}" class="btn btn-outline-secondary"><i class="bx bx-reset"></i></a>
                </div>
            </form>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Application #</th>
                        <th>Job</th>
                        <th>Valuation Date</th>
                        <th class="text-end">Gross Valuation (£)</th>
                        <th class="text-end">Previously Certified (£)</th>
                        <th class="text-end">Retention Held (£)</th>
                        <th class="text-end">Net Claim (£)</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($valuations as $val)
                    <tr>
                        <td>
                            <a href="{{ route('commercial.valuations.show', $val) }}" class="fw-bold text-primary">
                                {{ $val->application_number }}
                            </a>
                        </td>
                        <td>Job #{{ $val->job_id }} - {{ Str::limit($val->job->job_title ?? $val->job->job_name, 20) }}</td>
                        <td>{{ $val->valuation_date->format('d M Y') }}</td>
                        <td class="text-end fw-semibold">£{{ number_format($val->gross_valuation, 2) }}</td>
                        <td class="text-end text-muted">£{{ number_format($val->previous_certified_amount, 2) }}</td>
                        <td class="text-end text-danger fw-semibold">
                            -£{{ number_format($val->retention_deduction, 2) }}
                            <small class="text-muted d-block">({{ number_format($val->retention_rate, 1) }}%)</small>
                        </td>
                        <td class="text-end fw-bold text-success fs-6">
                            £{{ number_format($val->certified_amount ?? $val->net_claim_amount, 2) }}
                        </td>
                        <td>
                            @if($val->status === 'paid')
                                <span class="badge bg-label-success">Paid</span>
                            @elseif($val->status === 'certified')
                                <span class="badge bg-label-info">Certified</span>
                            @else
                                <span class="badge bg-label-secondary">Draft Claim</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('commercial.valuations.show', $val) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bx bx-show me-1"></i> View / Certify
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">No applications for payment found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($valuations->hasPages())
        <div class="card-footer border-top">
            {{ $valuations->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
