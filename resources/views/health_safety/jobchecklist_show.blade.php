@extends('layouts.master')

@section('title', 'Create JOB Health & Safety Check')

@section('scripts')
    @parent
    <script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
@endsection

@section('styles')
    @parent

    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}" />


@endsection

@section('content')
    <!-- Content -->
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Sticky Actions -->
        <div class="row">
            <div class="col-12">
                <div class="card">

                    <form action="{{ route('fjob-hs-check.store') }}" method="POST" enctype="multipart/form-data" class="row g-6">
                        @csrf
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        <div
                            class="card-header sticky-element bg-label-secondary d-flex justify-content-sm-between align-items-sm-center flex-column flex-sm-row">
                            <h5 class="card-title mb-sm-0 me-2">
                                JOB Health & Safety Check
                            </h5>
                            <div class="action-btns">
                                <button class="btn btn-label-primary me-4">
                                    <span class="align-middle"> Back</span>
                                </button>
                                <button type="submit" class="btn btn-primary">Save</button>
                            </div>
                        </div>
                        <div class="card-body pt-6">
                            <div class="row">
                                <!-- First column -->
                                <div class="col-12 col-lg-12">
                                    <!-- Basic Info Card -->
                                    <div class="card mb-6">
                                        <div class="card-body">
                                            <div class="row g-6">
                                                <div class="col-md-4">
                                                    <label class="form-label" for="jobs">Jobs</label>
                                                    <select id="select2jobs" class="select2 form-select"
                                                        data-allow-clear="true" name="job_id">
                                                        @foreach ($jobs as $job)
                                                            <option value="{{ $job->id }}">{{ $job->job_title }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                
                                                <div class="col-md-4">
                                                    <label class="form-label" for="Notes">Notes</label>
                                                    <textarea id="Notes" class="form-control" rows="2" name="notes" placeholder="Enter notes here..."></textarea>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                    <!-- /First column -->

                                    <!-- Second column-->
                                    <div class="col-12 col-lg-12">
                                        <div class="card mb-4">
                                            <div class="card-header bg-primary text-white mb-2">
                                                Checklists
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-2">
                                                    @foreach ($checklists as $checklist)
                                                        <strong>{{ $checklist->title }}</strong>
                                                        <label class="me-2">
                                                            <input type="radio"
                                                                name="checklist[{{ $checklist->id }}][status]"
                                                                value="1">
                                                            ✓ OK
                                                        </label>
                                                        <label class="me-2">
                                                            <input type="radio"
                                                                name="checklist[{{ $checklist->id }}][status]"
                                                                value="2">
                                                            ✗ Not OK
                                                        </label>
                                                        <label class="me-2">
                                                            <input type="radio"
                                                                name="checklist[{{ $checklist->id }}][status]"
                                                                value="0">
                                                            N/A
                                                        </label>

                                                        {{-- Short note input --}}
                                                        <input type="text" name="checklist[{{ $checklist->id }}][note]"
                                                            class="form-control d-inline-block w-auto ms-2"
                                                            placeholder="Short note">
                                                    @endforeach


                                                </div>

                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
        <!-- /Sticky Actions -->
    </div>
    <!-- / Content -->

@endsection
