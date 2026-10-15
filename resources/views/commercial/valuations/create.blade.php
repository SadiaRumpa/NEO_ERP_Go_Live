@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-1 mb-1">
                <span class="text-muted fw-light">Commercial /</span> New Application for Payment (Valuation)
            </h4>
            <p class="text-muted mb-0">Prepare cumulative progress valuation claim with automated statutory retention deductions.</p>
        </div>
        <div>
            <a href="{{ route('commercial.valuations.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Back to Valuations
            </a>
        </div>
    </div>

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible" role="alert">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('commercial.valuations.store') }}">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label required">Target Job</label>
                        <select name="job_id" class="form-select" required>
                            <option value="">Select Job</option>
                            @foreach($jobs as $job)
                                <option value="{{ $job->id }}" {{ (old('job_id') ?? $selectedJobId) == $job->id ? 'selected' : '' }}>
                                    Job #{{ $job->id }} - {{ $job->job_title ?? $job->job_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label required">Valuation Date</label>
                        <input type="date" name="valuation_date" class="form-control" value="{{ old('valuation_date', date('Y-m-d')) }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label required">Period Ending Date</label>
                        <input type="date" name="period_end" class="form-control" value="{{ old('period_end', date('Y-m-d')) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label required">Cumulative Work Completed Value (£)</label>
                        <input type="number" step="0.01" name="cumulative_work_value" id="cumulative_work" class="form-control" value="{{ old('cumulative_work_value', '0.00') }}" required>
                        <small class="text-muted">Total value of certified works completed to date</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Materials on Site (MOS) Value (£)</label>
                        <input type="number" step="0.01" name="materials_on_site_value" id="mos_value" class="form-control" value="{{ old('materials_on_site_value', '0.00') }}">
                        <small class="text-muted">Unfixed materials properly stored on site</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Previously Certified Cumulative Gross (£)</label>
                        <input type="number" step="0.01" name="previous_certified_amount" id="prev_certified" class="form-control" value="{{ old('previous_certified_amount', '0.00') }}">
                        <small class="text-muted">Gross cumulative value of previous certificate (deducted from current claim)</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label required">Contract Retention Rate (%)</label>
                        <input type="number" step="0.01" name="retention_rate" id="retention_rate" class="form-control" value="{{ old('retention_rate', '5.00') }}" required>
                        <small class="text-muted">Standard industry withholding: 3% or 5%</small>
                    </div>

                    <!-- Live Calculation Preview -->
                    <div class="col-12">
                        <div class="card bg-label-secondary border">
                            <div class="card-body">
                                <h6 class="card-title fw-bold mb-2">Claim Calculation Preview</h6>
                                <div class="row text-center">
                                    <div class="col-md-3">
                                        <small class="text-muted d-block">Gross Valuation</small>
                                        <span class="fs-5 fw-bold" id="preview_gross">£0.00</span>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted d-block">Current Period Gross</small>
                                        <span class="fs-5 fw-bold text-dark" id="preview_current_gross">£0.00</span>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted d-block">Retention Withheld</small>
                                        <span class="fs-5 fw-bold text-danger" id="preview_retention">-£0.00</span>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted d-block">Net Application Claim</small>
                                        <span class="fs-4 fw-bold text-success" id="preview_net">£0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Notes & Executive Valuation Commentary</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Key milestones achieved this period, site progress summary...">{{ old('notes') }}</textarea>
                    </div>

                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-check me-1"></i> Issue Application for Payment
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const cumWork = document.getElementById('cumulative_work');
        const mos = document.getElementById('mos_value');
        const prevCert = document.getElementById('prev_certified');
        const retRate = document.getElementById('retention_rate');

        function updatePreview() {
            const w = parseFloat(cumWork.value) || 0;
            const m = parseFloat(mos.value) || 0;
            const p = parseFloat(prevCert.value) || 0;
            const r = parseFloat(retRate.value) || 0;

            const gross = w + m;
            const currentGross = Math.max(0, gross - p);
            const retention = currentGross * (r / 100);
            const net = Math.max(0, currentGross - retention);

            document.getElementById('preview_gross').textContent = '£' + gross.toLocaleString('en-GB', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('preview_current_gross').textContent = '£' + currentGross.toLocaleString('en-GB', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('preview_retention').textContent = '-£' + retention.toLocaleString('en-GB', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('preview_net').textContent = '£' + net.toLocaleString('en-GB', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }

        [cumWork, mos, prevCert, retRate].forEach(el => el.addEventListener('input', updatePreview));
        updatePreview();
    });
</script>
@endsection
