@extends('layouts.master')

@section('title', 'Job Attributes')

@section('scripts')
    @parent
    <script src="{{ asset('assets/vendor/libs/moment/moment.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const yesCheckbox = document.getElementById('checkboxYes');
            const naCheckbox = document.getElementById('checkboxNa');
            const collapseElement = document.getElementById('collapseExample');
            const collapseInstance = new bootstrap.Collapse(collapseElement, {
                toggle: false
            });

            document.querySelectorAll('input[name="visibilityToggle"]').forEach(input => {
                input.addEventListener('change', function() {
                    if (this.value === 'yes') {
                        collapseInstance.show();
                    } else {
                        collapseInstance.hide();
                    }
                });
            });
        });
    </script>
@endsection

@section('styles')
    @parent
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}" />


@endsection

@section('content')

    <!-- Content -->
    <div class="container-xxl flex-grow-1 container-p-y">
        <div
            class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-6 row-gap-4">
            <div class="d-flex flex-column justify-content-center">
                <h4 class="mb-1">Job Attributes</h4>
            </div>
            <div class="d-flex align-content-center flex-wrap gap-4">
                <div class="d-flex gap-4">
                    <a href="{{ route('job.show', $job->id) }}" class="btn btn-label-warning">Back</a>

                </div>
            </div>
        </div>

        <form method="POST" action="">
            @csrf

            @foreach ($jobAttributes as $jatb)
                <div class="card mb-2">
                    <div class="card-body d-flex align-items-center gap-3">
                        <p class="card-text mb-0">{{ $jatb->name }}</p>

                        <!-- Parent Yes -->
                        <div class="form-check form-check-inline mb-0">
                            <input class="form-check-input toggle-parent" type="radio"
                                name="attributes[{{ $jatb->id }}][selected]" id="parentYes{{ $jatb->id }}"
                                value="yes" data-bs-toggle="collapse" data-bs-target="#collapseParent{{ $jatb->id }}"
                                aria-expanded="false" aria-controls="collapseParent{{ $jatb->id }}">
                            <label class="form-check-label" for="parentYes{{ $jatb->id }}">Yes</label>
                        </div>

                        <!-- Parent N/A -->
                        <div class="form-check form-check-inline mb-0">
                            <input class="form-check-input toggle-parent" type="radio"
                                name="attributes[{{ $jatb->id }}][selected]" id="parentNa{{ $jatb->id }}"
                                value="na" data-bs-toggle="collapse"
                                data-bs-target="#collapseParent{{ $jatb->id }}" aria-expanded="false"
                                aria-controls="collapseParent{{ $jatb->id }}">
                            <label class="form-check-label" for="parentNa{{ $jatb->id }}">N/A</label>
                        </div>
                    </div>

                    @if ($jatb->attributedetails->count())
                        <div class="collapse mt-3 px-3" id="collapseParent{{ $jatb->id }}">
                            <div class="border p-3 rounded">
                                @foreach ($jatb->attributedetails as $index => $detail)
                                    <div class="mb-2 d-flex align-items-center gap-3">
                                        <p class="mb-0">{{ $detail->value }}</p>

                                        <!-- Child Yes -->
                                        <div class="form-check form-check-inline mb-0">
                                            <input class="form-check-input" type="radio"
                                                name="attributes[{{ $jatb->id }}][children][{{ $detail->id }}]"
                                                id="childYes{{ $detail->id }}" value="yes">
                                            <label class="form-check-label" for="childYes{{ $detail->id }}">Yes</label>
                                        </div>

                                        <!-- Child N/A -->
                                        <div class="form-check form-check-inline mb-0">
                                            <input class="form-check-input" type="radio"
                                                name="attributes[{{ $jatb->id }}][children][{{ $detail->id }}]"
                                                id="childNa{{ $detail->id }}" value="na">
                                            <label class="form-check-label" for="childNa{{ $detail->id }}">N/A</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach

            <div class="text-end mt-4">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </form>

    </div>
    <!-- / Content -->

@endsection
