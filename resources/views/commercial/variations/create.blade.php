@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-1 mb-1">
                <span class="text-muted fw-light">Commercial /</span> Log Contract Variation
            </h4>
            <p class="text-muted mb-0">Record out-of-scope work, extra materials, or design modifications.</p>
        </div>
        <div>
            <a href="{{ route('commercial.variations.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Back to Variations
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
            <form method="POST" action="{{ route('commercial.variations.store') }}">
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

                    <div class="col-md-6">
                        <label class="form-label required">Variation Title</label>
                        <input type="text" name="title" class="form-control" value="{{ old('title') }}" placeholder="e.g. Additional Acoustic Ceiling Insulation to Grid 4" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Scope Description & Reason for Change</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Client instruction reference, specification alteration details...">{{ old('description') }}</textarea>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label required">Internal Incurred Cost (£)</label>
                        <input type="number" step="0.01" name="cost_amount" id="cost_amount" class="form-control" value="{{ old('cost_amount', '0.00') }}" required>
                        <small class="text-muted">Estimated materials, labor & plant cost</small>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Commercial Markup %</label>
                        <input type="number" step="0.01" name="markup_percentage" id="markup_percentage" class="form-control" value="{{ old('markup_percentage', '20.00') }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Quoted Price to Client (£)</label>
                        <input type="number" step="0.01" name="quoted_amount" id="quoted_amount" class="form-control" value="{{ old('quoted_amount') }}" placeholder="Leave blank to calculate from markup">
                    </div>

                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-check me-1"></i> Save Variation Draft
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const costInput = document.getElementById('cost_amount');
        const markupInput = document.getElementById('markup_percentage');
        const quotedInput = document.getElementById('quoted_amount');

        function recalc() {
            const cost = parseFloat(costInput.value) || 0;
            const markup = parseFloat(markupInput.value) || 0;
            if (cost > 0 && !quotedInput.dataset.manual) {
                const quoted = cost * (1 + (markup / 100));
                quotedInput.value = quoted.toFixed(2);
            }
        }

        costInput.addEventListener('input', recalc);
        markupInput.addEventListener('input', recalc);
        quotedInput.addEventListener('input', function() {
            this.dataset.manual = 'true';
        });
    });
</script>
@endsection
