@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="mb-4">
        <h4 class="fw-bold py-1 mb-1"><span class="text-muted fw-light">Labor / Timesheets /</span> Submit Timesheet</h4>
        <p class="text-muted mb-0">Record daily site hours, travel time, and overtime for automated burdened cost absorption.</p>
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
            <form method="POST" action="{{ route('labor.timesheets.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label required">Operative / Engineer</label>
                        <select name="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                            <option value="">Select Worker...</option>
                            @foreach($workers as $w)
                                <option value="{{ $w->id }}" {{ old('user_id', auth()->id()) == $w->id ? 'selected' : '' }}>
                                    {{ $w->name }} ({{ $w->email }})
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
                    <div class="col-md-4">
                        <label class="form-label required">Work Date</label>
                        <input type="date" name="work_date" class="form-control" value="{{ old('work_date', date('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Site Arrival Time</label>
                        <input type="time" name="start_time" class="form-control" value="{{ old('start_time', '08:00') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Site Departure Time</label>
                        <input type="time" name="end_time" class="form-control" value="{{ old('end_time', '16:30') }}">
                    </div>

                    <div class="col-md-12">
                        <div class="card border p-3">
                            <h6 class="fw-bold mb-3">Hours Breakdown</h6>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label required">Standard On-Site Hours</label>
                                    <input type="number" step="0.25" min="0" max="24" name="standard_hours" class="form-control fw-bold" value="{{ old('standard_hours', '8.00') }}" required>
                                    <small class="text-muted">Paid at 1.0x base rate</small>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Travel Time (Hours)</label>
                                    <input type="number" step="0.25" min="0" max="12" name="travel_hours" class="form-control" value="{{ old('travel_hours', '0.00') }}">
                                    <small class="text-muted">Commute / depot transit</small>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Overtime 1.5x (Hours)</label>
                                    <input type="number" step="0.25" min="0" max="12" name="overtime_1_5_hours" class="form-control" value="{{ old('overtime_1_5_hours', '0.00') }}">
                                    <small class="text-muted">Evening / Saturday shifts</small>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Double Time 2.0x (Hours)</label>
                                    <input type="number" step="0.25" min="0" max="12" name="overtime_2_0_hours" class="form-control" value="{{ old('overtime_2_0_hours', '0.00') }}">
                                    <small class="text-muted">Sunday / Bank Holiday shifts</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Work Summary & Progress Notes</label>
                        <textarea name="notes" rows="3" class="form-control" placeholder="e.g. Completed battens installation on south roof pitch. Commenced tile setting.">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-end gap-2">
                    <a href="{{ route('labor.timesheets.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-check me-1"></i> Submit Timesheet</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
