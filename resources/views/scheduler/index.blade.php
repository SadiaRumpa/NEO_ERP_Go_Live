@extends('layouts.master')

@section('title', 'Scheduler')

@section('scripts')
    @parent
    <script src="{{ asset('assets/vendor/libs/quill/katex.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/quill/quill.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/moment/moment.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/pickr/pickr.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
    <script src="{{ asset('assets/js/forms-pickers.js') }}"></script>

    <script>
        // select2 (jquery)
        $(function() {
            // Init custom option check
            window.Helpers.initCustomOptionCheck();

            // Select2 Country
            var select2 = $('.select2');
            if (select2.length) {
                select2.each(function() {
                    var $this = $(this);
                    $this.wrap('<div class="position-relative"></div>').select2({
                        placeholder: 'Select value',
                        dropdownParent: $this.parent()
                    });
                });
            }


        });
        $('#monthFilterForm').on('submit', function(e) {
            e.preventDefault();

            $.ajax({
                url: "{{ route('scheduler.workers-calendar') }}",
                method: "POST",
                data: $(this).serialize(),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    $('#calendar-table').html('<p>Loading...</p>');
                },
                success: function(response) {
                    $('#calendar-table').html(response.html);
                },
                error: function() {
                    alert("Something went wrong. Please try again.");
                }
            });
        });
    </script>
    <script>
        const quillm = new Quill('#instruction', {
            theme: 'snow',
            placeholder: 'Write your message...',
        });

        // Submit the form on Save button click
        $('#saveScheduleBtn').on('click', function(e) {
            e.preventDefault();

            // Get the instruction content (e.g., from Quill, TinyMCE, or plain div)
            const instructionHtml = $('#instruction').html();
            $('#instruction_input').val(instructionHtml);

            const form = $('#scheduleForm')[0];
            const formData = new FormData(form);

            $.ajax({
                url: "{{ route('scheduler.store-job-schedule') }}", // Update with your actual route
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    $('#saveScheduleBtn').prop('disabled', true);
                },
                success: function(response) {
                    toastr.success('Schedule saved successfully!');
                    $('#scheduleForm')[0].reset();
                    $('#saveScheduleBtn').prop('disabled', false);
                    $('.modal').modal('hide'); // Hide modal
                    // Optional: refresh DataTable or update page
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON.message || "Error saving schedule.");
                    $('#saveScheduleBtn').prop('disabled', false);
                    console.log(xhr.responseText);
                }
            });
        });


        // On form submit
        $('form').on('submit', function() {
            $('#instruction_input').val(quill.root.innerHTML);
        });
    </script>

@endsection

@section('styles')
    @parent
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/quill/typography.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/quill/katex.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/quill/editor.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
    <link rel="stylesheet"
        href="{{ asset('assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.csss') }}" />
    <style>
        .calendar-table th,
        .calendar-table td {
            font-size: 10px;
            white-space: nowrap;
            text-align: center;
            padding: 3px;
        }

        .sticky-col {
            position: sticky;
            left: 0;
            background-color: #fff;
            z-index: 1;
        }

        .calendar-wrapper {
            overflow-x: auto;
            max-width: 100%;
        }
    </style>
@endsection

@section('content')

    <!-- Content -->
    <div class="container-xxl flex-grow-1 container-p-y">
        <div
            class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-6 row-gap-4">
            <div class="d-flex flex-column justify-content-center">
                <h5 class="mb-1">Scheduler</h5>
            </div>
            <div class="d-flex align-content-center flex-wrap gap-4">
                <div class="d-flex gap-4">
                    <h5 class="mb-3">Worker Calendar - {{ now()->year }}</h5>
                </div>
            </div>
        </div>
        <div class="card mb-4">
            <div class="card-header d-flex  justify-content-between">
                <h5 class="card-title">Calendar</h5>
                {{-- Month selection filter --}}
                <form id="monthFilterForm" class="mb-3">
                    <div class="row">
                        @foreach ($allMonths as $month)
                            <div class="col-auto">
                                <label>
                                    <input type="checkbox" name="months[]" value="{{ $month }}"
                                        {{ in_array($month, $selectedMonths) ? 'checked' : '' }}>
                                    {{ $month }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-2">
                        <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                    </div>
                </form>
                <button type="button" class="btn btn-warning btn-md" data-bs-toggle="modal"
                    data-bs-target="#backDropModal">
                    <i class="icon-base ti tabler-browser-plus"></i> Schedule
                </button>
                <!-- Modal -->
                <div class="modal fade" id="backDropModal" data-bs-backdrop="static" tabindex="-1">
                    <div class="modal-dialog">
                        <form class="modal-content" id="scheduleForm">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title" id="backDropModalTitle">Schedular</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col mb-4">
                                        <label class="form-label" for="multicol-job">Jobs</label>
                                        <select id="multicol-job" class="select2 form-select" data-allow-clear="true"
                                            name="job_id">
                                            <option value="">Select</option>
                                            @foreach ($jobs as $job)
                                                <option value="{{ $job->id }}">{{ $job->job_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="row g-4">
                                    <div class="col mb-0">
                                        <label class="form-label" for="multicol-stuffs">Stuffs</label>
                                        <select id="multicol-stuffs" class="select2 form-select" name="workers[]" multiple>
                                            <option value="">Select</option>
                                            @foreach ($stuffs as $stuff)
                                                <option value="{{ $stuff->id }}">{{ $stuff->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col mb-0">
                                        <label for="flatpickr-range" class="form-label">Range Picker</label>
                                        <input type="text" class="form-control" placeholder="YYYY-MM-DD to YYYY-MM-DD"
                                            id="flatpickr-range" name="daterange" />
                                    </div>
                                </div>
                                <div class="row g-4">
                                    <div class="col mb-0">
                                        <label for="flatpickr-time" class="form-label">Instructions</label>
                                        <div class="form-control p-0">
                                            <div class="border-0 pb-6" id="instruction"></div>
                                        </div>
                                        
                                        <input type="hidden" name="instructions" id="instruction_input">
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
                                    Close
                                </button>
                                <button type="button" class="btn btn-primary" id="saveScheduleBtn">Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="card-body calendar-wrapper">
                {{-- Calendar table --}}
                <div id="calendar-table">
                    @include('partials.worker_calendar_table', [
                        'datesByMonth' => $datesByMonth,
                        'workers' => $workers,
                    ])
                </div>
            </div>
        </div>
    </div>
    <!-- / Content -->

@endsection
