@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="mb-4">
        <h4 class="fw-bold py-1 mb-1"><span class="text-muted fw-light">Subcontractors / Payment Certificates /</span> New Certificate</h4>
        <p class="text-muted mb-0">Certify subcontractor valuation, deduct materials exemptions, and calculate statutory HMRC CIS withholding.</p>
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
            <form method="POST" action="{{ route('subcontractors.certificates.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label required">Subcontractor</label>
                        <select name="subcontractor_id" id="subSelect" class="form-select @error('subcontractor_id') is-invalid @enderror" required>
                            <option value="">Select Subcontractor...</option>
                            @foreach($subcontractors as $sub)
                                <option value="{{ $sub->id }}" data-rate="{{ $sub->cis_tax_rate }}" data-status="{{ $sub->cis_tax_status }}" {{ (old('subcontractor_id', $selectedSubId) == $sub->id) ? 'selected' : '' }}>
                                    {{ $sub->company_name }} — {{ ucfirst($sub->cis_tax_status) }} ({{ $sub->cis_tax_rate }}% CIS)
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label required">Allocated Job</label>
                        <select name="job_id" class="form-select @error('job_id') is-invalid @enderror" required>
                            <option value="">Select Job...</option>
                            @foreach($jobs as $j)
                                <option value="{{ $j->id }}" {{ old('job_id') == $j->id ? 'selected' : '' }}>
                                    Job #{{ $j->id }} - {{ Str::limit($j->job_title ?? $j->job_name, 40) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label required">Valuation Period Start</label>
                        <input type="date" name="period_start" class="form-control" value="{{ old('period_start', date('Y-m-01')) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label required">Valuation Period End</label>
                        <input type="date" name="period_end" class="form-control" value="{{ old('period_end', date('Y-m-d')) }}" required>
                    </div>

                    <div class="col-md-12">
                        <div class="card border p-3 bg-light">
                            <h6 class="fw-bold mb-3 text-primary"><i class="bx bx-calculator me-1"></i> CIS Valuation & Deduction Breakdown</h6>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label required">Total Gross Claim Amount (£)</label>
                                    <input type="number" step="0.01" min="0.01" name="gross_amount" id="grossInput" class="form-control fw-bold" value="{{ old('gross_amount', '1000.00') }}" required>
                                    <small class="text-muted">Total invoice amount from subbie</small>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Materials Cost (CIS Exempt) (£)</label>
                                    <input type="number" step="0.01" min="0" name="materials_cost" id="matInput" class="form-control" value="{{ old('materials_cost', '0.00') }}">
                                    <small class="text-muted">Materials are exempt from CIS</small>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Labor Subject to CIS</label>
                                    <div class="form-control-plaintext fw-bold" id="laborDisplay">£1,000.00</div>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label text-danger">CIS Deduction</label>
                                    <div class="form-control-plaintext fw-bold text-danger" id="cisDisplay">-£200.00 (20%)</div>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label text-success">Net Payable</label>
                                    <div class="form-control-plaintext fw-bold text-success fs-5" id="netDisplay">£800.00</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Scope of Subcontract Works / Notes</label>
                        <textarea name="notes" rows="3" class="form-control" placeholder="e.g. Scaffolding erection to front and side elevations for roof replacement.">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-end gap-2">
                    <a href="{{ route('subcontractors.certificates.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-check-shield me-1"></i> Issue Payment Certificate</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const subSelect = document.getElementById('subSelect');
    const grossInput = document.getElementById('grossInput');
    const matInput = document.getElementById('matInput');
    const laborDisplay = document.getElementById('laborDisplay');
    const cisDisplay = document.getElementById('cisDisplay');
    const netDisplay = document.getElementById('netDisplay');

    function recalculate() {
        const gross = parseFloat(grossInput.value) || 0;
        const materials = parseFloat(matInput.value) || 0;
        const labor = Math.max(0, gross - materials);

        const selectedOpt = subSelect.options[subSelect.selectedIndex];
        const cisRate = selectedOpt ? parseFloat(selectedOpt.dataset.rate || 20) : 20;

        const cisDeduction = labor * (cisRate / 100);
        const net = gross - cisDeduction;

        laborDisplay.textContent = '£' + labor.toFixed(2);
        cisDisplay.textContent = '-£' + cisDeduction.toFixed(2) + ' (' + cisRate + '%)';
        netDisplay.textContent = '£' + net.toFixed(2);
    }

    grossInput.addEventListener('input', recalculate);
    matInput.addEventListener('input', recalculate);
    subSelect.addEventListener('change', recalculate);
    recalculate();
});
</script>
@endsection
