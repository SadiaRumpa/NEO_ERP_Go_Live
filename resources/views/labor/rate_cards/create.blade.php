@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="mb-4">
        <h4 class="fw-bold py-1 mb-1"><span class="text-muted fw-light">Labor / Rate Cards /</span> Add Rate Card</h4>
        <p class="text-muted mb-0">Configure base hourly pay, overtime rates, and statutory employment on-costs.</p>
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
            <form method="POST" action="{{ route('labor.rate-cards.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label required">Operative / Worker</label>
                        <select name="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                            <option value="">Select Worker...</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label required">Base Hourly Rate (£)</label>
                        <input type="number" step="0.01" min="0" name="base_hourly_rate" class="form-control" value="{{ old('base_hourly_rate', '22.50') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label required">Effective From</label>
                        <input type="date" name="effective_from" class="form-control" value="{{ old('effective_from', date('Y-m-d')) }}" required>
                    </div>

                    <div class="col-md-6">
                        <div class="card border p-3">
                            <h6 class="fw-bold mb-2">Overtime Multipliers</h6>
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-label">Overtime 1.5x Multiplier</label>
                                    <input type="number" step="0.01" name="overtime_multiplier_1_5" class="form-control" value="{{ old('overtime_multiplier_1_5', '1.50') }}">
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Double Time 2.0x Multiplier</label>
                                    <input type="number" step="0.01" name="overtime_multiplier_2_0" class="form-control" value="{{ old('overtime_multiplier_2_0', '2.00') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card border p-3">
                            <h6 class="fw-bold mb-2">Employment On-Cost Burden Percentages</h6>
                            <div class="row g-2">
                                <div class="col-4">
                                    <label class="form-label">Employer NI (%)</label>
                                    <input type="number" step="0.01" name="ni_burden_percent" class="form-control" value="{{ old('ni_burden_percent', '13.80') }}">
                                </div>
                                <div class="col-4">
                                    <label class="form-label">Pension (%)</label>
                                    <input type="number" step="0.01" name="pension_burden_percent" class="form-control" value="{{ old('pension_burden_percent', '3.00') }}">
                                </div>
                                <div class="col-4">
                                    <label class="form-label">Overhead (%)</label>
                                    <input type="number" step="0.01" name="overhead_burden_percent" class="form-control" value="{{ old('overhead_burden_percent', '10.00') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="is_active" class="form-select">
                            <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Archived</option>
                        </select>
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-end gap-2">
                    <a href="{{ route('labor.rate-cards.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-check me-1"></i> Save Rate Card</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
