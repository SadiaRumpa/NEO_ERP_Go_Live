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
    <script src="{{ asset('assets/vendor/libs/tagify/tagify.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/bootstrap-select/bootstrap-select.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/typeahead-js/typeahead.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/bloodhound/bloodhound.js') }}"></script>
    <script src="{{ asset('assets/js/forms-pickers.js') }}"></script>

    <script src="{{ asset('assets/js/forms-selects.js') }}"></script>
    <script src="{{ asset('assets/js/forms-tagify.js') }}"></script>
    <script src="{{ asset('assets/js/forms-typeahead.js') }}"></script>

    <script>
        $(function() {
            const flatpickrRange = document.querySelector('#flatpickr-range');

            if (typeof flatpickrRange != undefined) {
                flatpickrRange.flatpickr({
                    mode: 'range',
                    static: true,
                    allowInput: false,
                    dateFormat: 'Y-m-d',
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

            const $btn = $(this);
            const $form = $('#scheduleForm');
            const instructionHtml = $('#instruction').html();
            $('#instruction_input').val(instructionHtml);

            const formData = new FormData($form[0]);

            $.ajax({
                url: "{{ route('scheduler.store-job-schedule') }}",
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    $btn.prop('disabled', true).html(
                        '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...'
                    );
                },
                success: function(response) {
                    toastr.success(response.message || 'Schedule saved successfully!');
                    $form[0].reset();
                    $('#instruction_input').val('');
                    $('#scheduleModal').modal('hide');
                    $btn.prop('disabled', false).html('Save');

                    // 🔁 Reload the page after success
                    setTimeout(() => {
                        location.reload();
                    }, 1000); // Optional small delay to allow toast to show
                },
                error: function(xhr) {
                    let errorMsg = "Error saving schedule.";
                    if (xhr.status === 422 && xhr.responseJSON.errors) {
                        const firstError = Object.values(xhr.responseJSON.errors)[0];
                        if (firstError && firstError.length) {
                            errorMsg = firstError[0];
                        }
                    } else if (xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }

                    toastr.error(errorMsg);
                    console.error('Schedule save error:', xhr.responseText);
                    $btn.prop('disabled', false).html('Save');
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
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/tagify/tagify.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/bootstrap-select/bootstrap-select.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/typeahead-js/typeahead.css') }}" />
    <link rel="stylesheet"
        href="{{ asset('assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.css') }}" />
    <style>
        .calendar-table th,
        .calendar-table td {
            font-size: 12px;
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

        .form-check-input:checked {
            background-color: #7367f0;
            border-color: #7367f0;
        }

        .form-check-input {
            width: 1.25rem;
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
            <div
                class="card-header py-3 px-4 bg-white shadow-sm rounded d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                <!-- Month Filter Form -->
                <form id="monthFilterForm" class="d-flex flex-wrap align-items-center gap-3">
                    <div class="d-flex align-items-center me-3">
                        <i class="icon-base ti tabler-calendar-week fs-4 me-2 text-primary"></i>
                        <h6 class="mb-0 fw-bold">Months</h6>
                    </div>

                    @foreach ($allMonths as $month)
                        <div class="form-check form-check-inline">
                            <input class="form-check-input rounded" type="checkbox" name="months[]"
                                value="{{ $month }}" id="month_{{ $month }}"
                                {{ in_array($month, $selectedMonths) ? 'checked' : '' }}>
                            <label class="form-check-label" for="month_{{ $month }}">{{ $month }}</label>
                        </div>
                    @endforeach

                    <button type="submit" class="btn btn-sm btn-primary d-flex align-items-center px-3 py-2">
                        <i class="icon-base ti tabler-adjustments-alt"></i> Filter
                    </button>
                </form>

                <!-- Schedule Button -->
                <div class="mt-3 mt-md-0">
                    <button type="button" class="btn btn-warning btn-md d-flex align-items-center px-3 py-2"
                        data-bs-toggle="modal" data-bs-target="#backDropModal">
                        <i class="icon-base ti tabler-browser-plus"></i> Schedule
                    </button>
                </div>

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
                                        <label for="select2Dark" class="form-label">Stuffs</label>
                                        <div class="select2-dark">
                                            <select id="select2Dark" class="select2 form-select" name="workers[]" multiple>
                                                @foreach ($stuffs as $stuff)
                                                    <option value="{{ $stuff->id }}">{{ $stuff->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
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
