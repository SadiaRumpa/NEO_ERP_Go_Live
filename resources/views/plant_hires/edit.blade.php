@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-1 mb-1"><span class="text-muted fw-light">Commercial Plant /</span> Edit Plant Hire {{ $plantHire->hire_reference }}</h4>
            <p class="text-muted mb-0">Update equipment hire contract terms, rates, or scheduled return dates.</p>
        </div>
        <div>
            <a href="{{ route('plant-hires.show', $plantHire) }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Back to Details
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
            <form method="POST" action="{{ route('plant-hires.update', $plantHire) }}">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label required">Hire Item / Equipment Name</label>
                        <input type="text" name="item_name" class="form-control" value="{{ old('item_name', $plantHire->item_name) }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label required">Category</label>
                        <select name="category" class="form-select" required>
                            <option value="mewp" {{ old('category', $plantHire->category) == 'mewp' ? 'selected' : '' }}>MEWPs / Powered Access</option>
                            <option value="scaffolding" {{ old('category', $plantHire->category) == 'scaffolding' ? 'selected' : '' }}>Scaffolding & Towers</option>
                            <option value="telehandler" {{ old('category', $plantHire->category) == 'telehandler' ? 'selected' : '' }}>Telehandler / Forklift</option>
                            <option value="skips" {{ old('category', $plantHire->category) == 'skips' ? 'selected' : '' }}>Waste & Skips</option>
                            <option value="tools" {{ old('category', $plantHire->category) == 'tools' ? 'selected' : '' }}>Small Plant & Tools</option>
                            <option value="other" {{ old('category', $plantHire->category) == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Plant Serial / Asset Tag</label>
                        <input type="text" name="serial_asset_tag" class="form-control" value="{{ old('serial_asset_tag', $plantHire->serial_asset_tag) }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label required">Hire Supplier</label>
                        <select name="supplier_id" class="form-select" required>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ old('supplier_id', $plantHire->supplier_id) == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label required">Allocated Job</label>
                        <select name="job_id" class="form-select" required>
                            @foreach($jobs as $job)
                                <option value="{{ $job->id }}" {{ old('job_id', $plantHire->job_id) == $job->id ? 'selected' : '' }}>
                                    Job #{{ $job->id }} - {{ $job->job_title ?? $job->job_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label required">Hire Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="{{ old('start_date', $plantHire->start_date->format('Y-m-d')) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label required">Target Off-Hire Date</label>
                        <input type="date" name="expected_end_date" class="form-control" value="{{ old('expected_end_date', $plantHire->expected_end_date->format('Y-m-d')) }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Daily Hire Rate (£)</label>
                        <input type="number" step="0.01" name="daily_rate" class="form-control" value="{{ old('daily_rate', $plantHire->daily_rate) }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Weekly Hire Rate (£)</label>
                        <input type="number" step="0.01" name="weekly_rate" class="form-control" value="{{ old('weekly_rate', $plantHire->weekly_rate) }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Delivery Charge (£)</label>
                        <input type="number" step="0.01" name="delivery_charge" class="form-control" value="{{ old('delivery_charge', $plantHire->delivery_charge) }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Collection Charge (£)</label>
                        <input type="number" step="0.01" name="collection_charge" class="form-control" value="{{ old('collection_charge', $plantHire->collection_charge) }}">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Hire Notes</label>
                        <textarea name="notes" class="form-control" rows="3">{{ old('notes', $plantHire->notes) }}</textarea>
                    </div>

                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save me-1"></i> Update Hire Contract
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
