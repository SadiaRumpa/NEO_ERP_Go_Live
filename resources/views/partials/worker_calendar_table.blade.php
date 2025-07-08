<div class="table-responsive text-nowrap">
    <table class="table table-hover">
        <thead class="table-light">
            <tr>
                <th class="sticky-col bg-white" rowspan="2">Worker Name</th>
                @foreach ($datesByMonth as $month => $dates)
                    <th colspan="{{ count($dates) }}" class="text-center">{{ $month }}</th>
                @endforeach
            </tr>
            <tr>
                @foreach ($datesByMonth as $month => $dates)
                    @foreach ($dates as $date)
                        <th>{{ \Carbon\Carbon::parse($date)->format('d') }}</th>
                    @endforeach
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($workers as $worker)
                <tr class="position-relative" style="height: 50px;"> {{-- Control row height --}}
                    <td class="sticky-col bg-white"><strong>{{ $worker->name }}</strong></td>

                    @php
                        $allDatesArray = $allDates->toArray();
                        $skipDates = []; // Track which dates to skip due to colspan
                    @endphp

                    @foreach ($allDatesArray as $date)
                        @php
                            $dateKey = \Carbon\Carbon::parse($date)->format('Y-m-d');

                            // Get all jobs assigned to this worker that intersect this date
                            $jobSchedules = $worker->jobSchedules->filter(function ($job) use ($dateKey) {
                                return $dateKey >= $job->start_date && $dateKey <= $job->end_date;
                            });
                            
                        @endphp

                        <td class="calendar-cell">
                            @if ($jobSchedules->isNotEmpty())
                            
                                @foreach ($jobSchedules as $jobSchedule)
                                    @php
                                        $startdate = $jobSchedule->start_date;
                                        $enddate = $jobSchedule->end_date;
                                    @endphp

                                    <div class="mb-1">
                                        <button type="button" class="btn btn-sm btn-primary w-100 text-start"
                                            data-bs-toggle="modal" data-bs-target="#scheduleUpdateModal"
                                            data-id="{{ $jobSchedule->id }}">
                                            {{ $jobSchedule->job_name }} - {{  $jobSchedule->job_title }}
                                        </button>
                                    </div>
                                @endforeach
                            @else
                                -
                            @endif
                        </td>
                    @endforeach


                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<!-- Modal -->
<div class="modal fade" id="scheduleUpdateModal" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <form class="modal-content" id="scheduleUpdateForm">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title" id="backDropModalTitle">Schedule Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col mb-4">
                        <div class="row">
                            <div class="col mb-4">
                                <label class="form-label" for="multicol-job">Job</label>
                                <input type="text" id="jobname" class="form-control" disabled />
                            </div>
                        </div>
                        <div class="row g-4">
                            <div class="col mb-0">
                                <label for="select2Info" class="form-label">Stuffs</label>
                                <div class="select2-info">
                                    <select id="select2Info" class="select2 form-select" name="workers[]" multiple>

                                    </select>
                                </div>
                            </div>
                            <div class="col mb-0">
                                <label for="flatpickr-range" class="form-label">Date</label>
                                <input type="text" class="form-control" placeholder="YYYY-MM-DD to YYYY-MM-DD"
                                    id="flatpickr-range2" name="daterange" />
                            </div>
                        </div>
                        <div class="row g-4">
                            <div class="col mb-0">
                                <label for="instructionu" class="form-label">Instructions</label>
                                <div class="form-control p-0">
                                    <div class="border-0 pb-6" id="instructionu"></div>
                                </div>

                                <input type="hidden" name="instructions" id="instructionu_input">
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="row">
                            <div class="modal-job-details" style="height: 80%; overflow-y: auto;">

                                <iframe id="jobMap" width="100%" height="250" frameborder="0" style="border:0"
                                    referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>

                                <h5>Contact Info</h5>
                                <div class="row contact-info">
                                    <div class="col-6 mb-3">
                                        <strong>Company:</strong> <span id="companyName"></span>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <strong>Contact:</strong> <span id="contactName"></span>
                                    </div>
                                    <div class="col-4 mb-3">
                                        <strong>Email:</strong> <span id="contactEmail"></span>
                                    </div>
                                    <div class="col-4 mb-3">
                                        <strong>Phone:</strong> <span id="contactPhone"></span>
                                    </div>
                                    <div class="col-4 mb-3">
                                        <strong>Mobile:</strong> <span id="contactMobile"></span>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <strong>Address:</strong> <span id="jobAddress"></span>
                                    </div>
                                </div>
                                <p><strong>Description:</strong> <span id="jobDescription"></span></p>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <input type="hidden" id="jobScheduleId">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
                    Close
                </button>
                <button type="button" class="btn btn-primary" id="updateScheduleBtn">Update</button>
                <button class="btn btn-md btn-danger"
                    onclick="removeAssignment(this.closest('form').querySelector('#jobScheduleId').value); return false;">
                    <i class="bi bi-trash"></i>
                    Delete Assignment
                </button>
            </div>
        </form>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('scheduleUpdateModal');

        if (!modal) return;

        const flatpickrRange2 = document.querySelector('#flatpickr-range2');
        if (typeof flatpickrRange2 != undefined) {
            flatpickrRange2.flatpickr({
                mode: 'range',
                static: true,
                allowInput: false,
                dateFormat: 'Y-m-d',
            });
        }

        const quillmu = new Quill('#instructionu', {
            theme: 'snow',
            placeholder: 'Write your message...',
        });


        modal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const jobScheduleId = button?.getAttribute('data-id');

            // 🛠️ Delay execution slightly to allow modal content to load
            setTimeout(() => {
                const jobNameInput = document.getElementById('jobname');
                const dateRangeInput = document.getElementById('flatpickr-range2');
                const instructionDiv = document.getElementById('instructionu');
                const instructionInput = document.getElementById('instructionu_input');
                const workerSelect = document.getElementById('select2Info');
                const jobScheduleIdInput = document.getElementById('jobScheduleId');


                if (!jobNameInput || !dateRangeInput || !instructionDiv || !workerSelect) {
                    console.error('Some modal fields are missing in DOM.');
                    return;
                }

                fetch(`/scheduler/job-schedule/${jobScheduleId}`)
                    .then(response => response.json())
                    .then(data => {
                        const schedule = data.schedule;
                        const workers = data.workers;
                        const workerslist = data.workerslist;
                        const job = data.job;
                        const jobAddress = data.jobAddress;
                        const jobCustomer = data.customer;
                        const GOOGLE_MAPS_API_KEY =
                            "{{ config('services.google.maps_api_key') }}";

                        jobNameInput.value = schedule.job_name || '';
                        dateRangeInput.value =
                            `${schedule.start_date} to ${schedule.end_date}`;

                        quillmu.root.innerHTML = ''; // Optional: clear existing content
                        quillmu.clipboard.dangerouslyPasteHTML(schedule.instructions || '');
                        instructionInput.value = schedule.instructions || '';

                        const assignedWorkerIds = workers.map(w => w.id.toString());

                        // Clear old options (in case this is reused)
                        workerSelect.innerHTML = '';

                        // Populate all options
                        workerslist.forEach(worker => {
                            const option = document.createElement('option');
                            option.value = worker.id;
                            option.textContent = worker.name;

                            // Mark as selected if this worker is assigned
                            if (assignedWorkerIds.includes(worker.id.toString())) {
                                option.selected = true;
                            }

                            workerSelect.appendChild(option);
                        });

                        // Trigger Select2 refresh
                        if ($(workerSelect).hasClass("select2-hidden-accessible")) {
                            $(workerSelect).trigger('change');
                        }

                        if (jobScheduleId) {
                            jobScheduleIdInput.value = schedule.id;
                        }

                        // Fill job info
                        document.getElementById('jobDescription').innerHTML = job.job_description || '';

                        // Fill contact info
                        document.getElementById('companyName').textContent =
                            `${jobCustomer.company_name}`
                            .trim();
                        document.getElementById('contactName').textContent =
                            `${jobAddress.contact_firstname} ${jobAddress.contact_lastname}`
                            .trim();
                        document.getElementById('contactEmail').textContent = jobAddress
                            .contact_email || '';
                        document.getElementById('contactPhone').textContent = jobAddress
                            .contact_phone || '';

                        // Fill address
                        document.getElementById('jobAddress').textContent = jobAddress
                            .address + ', ' + jobAddress.county + ', ' + jobAddress
                            .country || '';

                        // Update Google Map iframe
                        const googleMap = document.getElementById('jobMap');
                        const addressParam = encodeURIComponent(
                            `${jobAddress.address}, ${jobAddress.county}, ${jobAddress.country}`
                        );
                        googleMap.src =
                            `https://www.google.com/maps/embed/v1/place?key=${GOOGLE_MAPS_API_KEY}&q=${addressParam}`;

                    });
            }, 100); // Slight delay helps if modal rendering is async
        });

        // 🧼 Reset modal on close
        modal.addEventListener('hidden.bs.modal', function() {
            // Reset modal fields
            document.getElementById('jobname').value = '';
            document.getElementById('flatpickr-range2').value = '';
            document.getElementById('instructionu_input').value = '';
            quillmu.root.innerHTML = '';
            document.getElementById('select2Info').innerHTML = '';
            document.getElementById('jobScheduleId').value = '';

            // Reinitialize empty Select2 if needed
            const select2 = $('#select2Info');
            if (select2.hasClass("select2-hidden-accessible")) {
                select2.val(null).trigger('change');
            }

            // ✅ Move focus to a safe, visible element outside the modal
            setTimeout(() => {
                document.activeElement.blur(); // Remove focus from any lingering element
                const fallbackFocus = document.querySelector('[data-bs-toggle="modal"]') ||
                    document.body;
                fallbackFocus.focus();
            }, 0);
        });
    });
</script>
<script>
    function removeAssignment(assignmentId) {
        if (!confirm('Are you sure you want to remove this assignment?')) return;

        fetch(`/scheduler/delete-schedule/${assignmentId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
            })
            .then(async response => {
                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Something went wrong');
                }

                toastr.success(data.message || 'Schedule deleted successfully');
                // Optionally: update UI dynamically instead of full reload
                setTimeout(() => location.reload(), 1000);
            })
            .catch(error => {
                console.error('Delete failed:', error);
                toastr.error(error.message || 'Failed to delete schedule.');
            });
    }
</script>
