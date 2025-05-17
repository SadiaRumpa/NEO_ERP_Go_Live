@extends('layouts.master')

@section('title', 'Lead Details')

@section('scripts')
@parent
<script src="{{asset('assets/vendor/libs/quill/katex.js')}}"></script>
<script src="{{asset('assets/vendor/libs/highlight/highlight.js')}}"></script>
<script src="{{asset('assets/vendor/libs/quill/quill.js')}}"></script>
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>
<script src="{{asset('assets/vendor/libs/jquery-repeater/jquery-repeater.js')}}"></script>
<script src="{{asset('assets/vendor/libs/flatpickr/flatpickr.js')}}"></script>
<script src="{{asset('assets/vendor/libs/tagify/tagify.js')}}"></script>
<!-- Vendors JS -->
<script src="{{asset('assets/vendor/libs/swiper/swiper.js')}}"></script>
<!-- Page JS -->
<script src="{{asset('assets/js/ui-carousel.js')}}"></script>

<script>
    $(function() {
        // Datepicker
        const date = new Date();

        const productDate = document.querySelector('.product-date');

        if (productDate) {
            productDate.flatpickr({
                monthSelectorType: 'static',
                defaultDate: date
            });
        }
    });
    $(function() {
        // Select2
        var select2 = $('.select2');
        if (select2.length) {
            select2.each(function() {
                var $this = $(this);
                $this.wrap('<div class="position-relative"></div>').select2({
                    dropdownParent: $this.parent(),
                    placeholder: $this.data('placeholder') // for dynamic placeholder
                });
            });
        }
    });
</script>
<script>
    let quill;
    document.addEventListener('DOMContentLoaded', function() {
        const editorContainer = document.querySelector('#lead-description-edit');
        if (editorContainer) {
            quill = new Quill(editorContainer, {
                modules: {
                    toolbar: '.lead-toolbar'
                },
                placeholder: 'lead Description',
                theme: 'snow'
            });
        }
    });

    function updateleadDetails() {
        const htmlContent = quill.root.innerHTML.trim();
        const textContent = quill.getText().trim();

        if (textContent === '') {
            alert('lead description is required.');
            return;
        }

        // Add to hidden input
        document.getElementById('leaddescription-hidden').value = htmlContent;
        const form = document.getElementById('leadForm');
        const formData = new FormData(form);
        fetch(`/lead/updatedescription/{{ $lead->id }}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert(data.message);
                    // Optionally close modal
                    // $('#yourModalId').modal('hide');
                    location.reload(); // This will reload the entire page
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Something went wrong!');
            });
    }
</script>
<script>
    function addNotes(leadId) {
        const note = $('#lead_note').val();

        if (!note.trim()) {
            $('#note-message').html('<div class="alert alert-warning">Note cannot be empty.</div>');
            return;
        }

        $.ajax({
            url: "{{ route('leads.add-note') }}", // Your Laravel route
            method: "POST",
            data: {
                lead_id: leadId,
                note: note,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.status === 'success') {
                    $('#note-message').html('<div class="alert alert-success">' + response.message + '</div>');
                    $('#lead_note').val('');
                    window.location.reload(); // Reload the page to see the new note
                } else {
                    $('#note-message').html('<div class="alert alert-danger">' + response.message + '</div>');
                }
            },
            error: function(xhr) {
                let message = "Something went wrong.";
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    message = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                }
                $('#note-message').html('<div class="alert alert-danger">' + message + '</div>');
            }
        });
    }
</script>
<script>
    const quillm = new Quill('#mailmessage', {
        theme: 'snow',
        placeholder: 'Write your message...',
    });

    function selectMailTemplate() {
        const templateId = document.getElementById('mailTemplateSelect').value;
        if (!templateId) return;

        $.ajax({
            url: `/mail-template/${templateId}`,
            method: 'GET',
            success: function(res) {
                quillm.root.innerHTML = res.message;
            },
            error: function() {
                console.error('Failed to load mail template.');
                quillm.root.innerHTML = '';
            }
        });
    }

    // On form submit
    $('form').on('submit', function() {
        $('#mailmessage_input').val(quill.root.innerHTML);
    });
</script>
<script>
    $(document).ready(function() {
        $('.delete-note').click(function() {
            const noteId = $(this).data('id');
            const confirmation = confirm('Are you sure you want to delete this note?');
            if (confirmation) {
                $.ajax({
                    url: `/enquiries/delete-note/${noteId}`,
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            alert(response.message);
                            window.location.reload(); // Reload the page to see the changes
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function(xhr) {
                        alert('Something went wrong!');
                    }
                });
            }
        });
    });
</script>
<script>
    $(document).ready(function() {
        $('.delete-image').click(function() {
            const imageId = $(this).data('id');
            const confirmation = confirm('Are you sure you want to delete this image?');
            if (confirmation) {
                $.ajax({
                    url: `/enquiries/delete-image/${imageId}`,
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            alert(response.message);
                            window.location.reload(); // Reload the page to see the changes
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function(xhr) {
                        alert('Something went wrong!');
                    }
                });
            }
        });
    });
</script>
<script>
    $(document).ready(function() {
        $('.update-status-btn').on('click', function() {
            let leadId = $(this).data('id');
            let status = $(this).data('status');

            // Confirmation dialog
            if (!confirm(`Are you sure you want to change the status to "${status}"?`)) {
                return; // Stop if user cancels
            }

            $.ajax({
                url: '/lead-to-lead-conversion',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    status: status,
                    lead_id: leadId
                },
                success: function(response) {
                    $('#status-alert')
                        .removeClass('alert-danger')
                        .addClass('alert-success')
                        .text(response.message || 'Status updated successfully.')
                        .show();

                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                },
                error: function(xhr) {
                    let errorMsg = 'An error occurred.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }

                    $('#status-alert')
                        .removeClass('alert-success')
                        .addClass('alert-danger')
                        .text(errorMsg)
                        .show();
                }
            });
        });
    });
</script>

<script>
    $(document).on('click', '.update-lead-status', function(e) {
        e.preventDefault();

        let status = $(this).data('status');
        let leadId = $(this).data('id');

        alert(`Are you sure you want to change the status to "${status}"?`);

        $.ajax({
            url: `/leads/updatestatus/${leadId}`,
            type: 'POST',
            data: {
                lead_status: status,
                _token: $('meta[name="csrf-token"]').attr('content') // Make sure CSRF token is available
            },
            success: function(response) {
                toastr.success('Status updated successfully');
                location.reload();
            },
            error: function(xhr) {
                toastr.danger('Something went wrong');
            }
        });
    });
</script>


@endsection

@section('styles')
@parent
<link rel="stylesheet" href="{{asset('assets/vendor/libs/quill/typography.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/quill/katex.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/quill/editor.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/flatpickr/flatpickr.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/tagify/tagify.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/dropzone/dropzone.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/swiper/swiper.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/css/pages/ui-carousel.css')}}" />
@endsection

@section('content')
<!-- Content -->
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="app-ecommerce">
        @if(session('success'))
        <div class="alert alert-success mt-2">{{ session('success') }}</div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger mt-2">{{ session('error') }}</div>
        @endif
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-6 row-gap-4">
            <div class="d-flex flex-column justify-content-center">
                <h4 class="mb-1">LEAD : {{ $lead->lead_name }}

                    <div class="btn-group">
                        @if($lead->lead_status == 'job')
                        <button type="button" class="btn btn-label-info waves-effect">{{ $lead->lead_status }}</button>
                        @elseif($lead->lead_status == 'new')
                        <button type="button" class="btn btn-label-primary waves-effect">{{ $lead->lead_status }}</button>
                        @elseif($lead->lead_status == 'inprogress')
                        <button type="button" class="btn btn-label-warning waves-effect">{{ $lead->lead_status }}</button>
                        @elseif($lead->lead_status == 'completed')
                        <button type="button" class="btn btn-label-success waves-effect">{{ $lead->lead_status }}</button>
                        @elseif($lead->lead_status == 'rejected')
                        <button type="button" class="btn btn-label-danger waves-effect">{{ $lead->lead_status }}</button>
                        @endif
                        <button type="button" class="btn btn-label-primary dropdown-toggle dropdown-toggle-split waves-effect" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="visually-hidden">Toggle Dropdown</span>
                        </button>
                        <ul class="dropdown-menu" style="">
                            <li><a class="dropdown-item waves-effect update-lead-status" data-status="inprogress" data-id="{{ $lead->id }}" href="#">Inprogress</a></li>
                            <li><a class="dropdown-item waves-effect update-lead-status" data-status="completed" data-id="{{ $lead->id }}" href="#">Completed</a></li>
                        </ul>
                    </div>

                </h4>
            </div>
            @if($lead->lead_status == 'new' || $lead->lead_status == 'inprogress')
            <div class="d-flex align-content-center flex-wrap gap-4">
                <!-- Alert message area -->
                <div id="status-alert" style="display:none;" class="alert mt-2"></div>
                <div class="d-flex gap-4">
                    <a class="btn btn-label-primary" href="{{ route('quotations.lead', $lead->id) }}">
                        GENERATE QUOTATION
                    </a>

                    <button
                        type="button"
                        class="btn btn-danger waves-effect waves-light"
                        data-bs-toggle="modal"
                        data-bs-target="#rejectleadModal">
                        REJECT LEAD
                    </button>



                    <!-- <button class="btn btn-label-success update-status-btn"
                        data-id="{{ $lead->id }}"
                        data-status="lead">
                        SEND LEAD MANAGER
                    </button> -->
                </div>
            </div>
            @endif
        </div>

        <!-- Reject mail Modal -->
        <div
            class="modal-onboarding modal fade animate__animated"
            id="rejectleadModal"
            tabindex="-1">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <a
                            class="text-body-secondary close-label"
                            href="javascript:void(0);"
                            data-bs-dismiss="modal">Skip Intro</a>
                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-0">
                        <div class="onboarding-content mb-0">
                            <strong class="onboarding-title text-body">Reject lead</strong>
                            <div class="row">
                                <div class="col-sm-12 col-md-12 mb-4">
                                    <div class="input-group">
                                        <select class="form-select" id="mailTemplateSelect" aria-label="Choose mail template">
                                            <option selected disabled>Choose mail template</option>
                                            @foreach ($mailtemplates as $mt)
                                            <option value="{{ $mt->id }}">{{ $mt->name }}</option>
                                            @endforeach
                                        </select>

                                        <button class="btn btn-outline-primary waves-effect" type="button"
                                            onclick="selectMailTemplate()">
                                            Select Mail
                                        </button>

                                    </div>
                                </div>
                            </div>
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="mb-4">
                                                <label for="sendtoemail" class="form-label">Send to</label>
                                                <input type="email" class="form-control" id="sendtoemail" placeholder="name@example.com" name="sendtoemail" value="{{ $lead->customer->contact_email }}">
                                            </div>
                                            <div class="mb-4">
                                                <label for="mailsubject" class="form-label">Subject</label>
                                                <input class="form-control" type="text" id="mailsubject" aria-label="readonly input example" name="mailsubject" value="lead {{ $lead->lead_name }} has been rejected">
                                            </div>
                                            <div class="form-control p-0">
                                                <div class="border-0 pb-6" id="mailmessage"></div>
                                            </div>
                                            <input type="hidden" id="mailmessage_input" name="mailmessage">
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
                            Close
                        </button>
                        <button type="button" class="btn btn-primary">Submit</button>
                    </div>
                </div>
            </div>
        </div>
        <!--/ Reject mail Modal -->

        <div class="row mb-6 gy-6">
            <!-- lead column-->
            <div class="col-12 col-lg-8">
                <!-- lead Information -->
                <div class="card mb-6">
                    <div class="card-header header-elements">
                        <h5 class="mb-0 me-2">lead information</h5>
                        @if($lead->lead_status == 'new' || $lead->lead_status == 'inprogress')
                        <div class="card-header-elements ms-auto">
                            <div class="btn-group">

                                <button
                                    type="button"
                                    class="btn btn-warning waves-effect waves-light"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editdescriptionModal">
                                    Edit
                                </button>

                            </div>
                        </div>
                        @endif
                    </div>
                    <div class="card-body">
                        <!-- Description -->
                        <div class="mb-6">
                            <div class="form-control p-2">
                                {!! $lead->lead_description !!}
                            </div>
                        </div>

                        <!-- Form with Image Modal -->
                        <div
                            class="modal-onboarding modal fade animate__animated"
                            id="editdescriptionModal"
                            tabindex="-1">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content text-center">
                                    <div class="modal-header border-0">
                                        <a
                                            class="text-body-secondary close-label"
                                            href="javascript:void(0);"
                                            data-bs-dismiss="modal">Skip Intro</a>
                                        <button
                                            type="button"
                                            class="btn-close"
                                            data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <form id="leadForm">
                                        @csrf
                                        <div class="modal-body p-0">
                                            <div class="onboarding-content mb-0">
                                                <div class="row">
                                                    <div class="col-sm-12 col-md-12 mb-4">
                                                        <div class="mb-6">
                                                            <label class="mb-1">Description</label>

                                                            <input type="hidden" name="leaddescription" id="leaddescription-hidden">
                                                            <input type="hidden" name="lead_id" value="{{ $lead->id }}">

                                                            <div class="form-control p-0">
                                                                <div class="lead-toolbar border-0 border-bottom">
                                                                    <div class="d-flex justify-content-start">
                                                                        <span class="ql-formats me-0">
                                                                            <button class="ql-bold"></button>
                                                                            <button class="ql-italic"></button>
                                                                            <button class="ql-underline"></button>
                                                                            <button class="ql-list" value="ordered"></button>
                                                                            <button class="ql-list" value="bullet"></button>
                                                                            <button class="ql-link"></button>
                                                                            <button class="ql-image"></button>
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                                <div class="border-0 pb-6" id="lead-description-edit">
                                                                    {!! $lead->lead_description !!}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-0">
                                            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
                                                Close
                                            </button>
                                            <button type="button" class="btn btn-success" onclick="updateleadDetails()">Save</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!--/ Form with Image Modal -->
                    </div>
                </div>
                <!-- lead Information -->
                <!-- lead Images -->
                <div class="card mb-6">
                    <div class="card-header header-elements">
                        <h5 class="mb-0 me-2">lead Images</h5>

                        <div class="card-header-elements ms-auto">
                            <div class="btn-group">
                                @if($lead->lead_status == 'new' || $lead->lead_status == 'inprogress')
                                <!-- Image Modal -->
                                <button
                                    type="button"
                                    class="btn btn-warning waves-effect waves-light"
                                    data-bs-toggle="modal"
                                    data-bs-target="#imageAddModal">
                                    Add More
                                </button>
                                <!--/ Image Modal -->
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="swiper-gallery">
                            <div class="swiper gallery-top">
                                <div class="swiper-wrapper">
                                    @foreach ($lead->leadimages as $enim)
                                    <div class="swiper-slide position-relative" style="background-image: url({{ $enim->source == 's3' ? $enim->image_url : asset($enim->image_path) }})">
                                        @if($lead->lead_status == 'new' || $lead->lead_status == 'inprogress')
                                        <div class="position-absolute top-0 end-0 p-2">
                                            <button class="btn btn-sm btn-danger delete-image" data-id="{{ $enim->id }}" style="z-index: 10;">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                        @endif
                                        <div class="text-white bg-dark bg-opacity-50 p-1 position-absolute bottom-0 w-100 text-center">
                                            {{ $enim->image_caption }}
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                <!-- Add Arrows -->
                                <div class="swiper-button-next swiper-button-white"></div>
                                <div class="swiper-button-prev swiper-button-white"></div>
                            </div>
                            <div class="swiper gallery-thumbs">
                                <div class="swiper-wrapper">
                                    @foreach ($lead->leadimages as $enim)
                                    @if($enim->source == "s3")
                                    <div class="swiper-slide" style="background-image: url({{ $enim->image_url }})">
                                        {{ $enim->image_caption }}
                                    </div>
                                    @else
                                    <div class="swiper-slide" style="background-image: url({{ asset('/') }}/{{ $enim->image_path }})">
                                        {{ $enim->image_caption }}
                                    </div>
                                    @endif
                                    @endforeach

                                </div>
                            </div>
                        </div>

                        <!-- Form with Image Modal -->
                        <div
                            class="modal-onboarding modal fade animate__animated"
                            id="imageAddModal"
                            tabindex="-1"
                            aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content text-center">
                                    <div class="modal-header border-0">
                                        <a
                                            class="text-body-secondary close-label"
                                            href="javascript:void(0);"
                                            data-bs-dismiss="modal">Skip Intro</a>
                                        <button
                                            type="button"
                                            class="btn-close"
                                            data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <form accept="application/json" method="POST" action="{{ route('leads.add-images') }}" enctype="multipart/form-data">
                                        @csrf

                                        <div class="modal-body p-0">
                                            <div class="onboarding-media">
                                                <div class="mx-2">
                                                    <!-- Optional illustration here -->
                                                </div>
                                            </div>

                                            <div class="onboarding-content mb-0">
                                                <div class="row">
                                                    <div class="col-sm-12 col-md-12 mb-4">
                                                        <div class="mb-4">
                                                            <label for="basic-default-upload-file" class="form-label">Select Image</label>
                                                            <input type="file" class="form-control" id="basic-default-upload-file" name="leadphoto" accept=".jpg, .jpeg, .png" required />
                                                            <input type="hidden" name="lead_id" value="{{ $lead->id }}">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal-footer border-0">
                                            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-success">Add</button>
                                        </div>
                                    </form>

                                </div>
                            </div>
                        </div>
                        <!--/ Form with Image Modal -->

                    </div>
                </div>
                <!-- lead Images -->
                <!-- lead Notes -->
                <div class="row mb-6 gy-6">
                    <div class="col-xl">
                        <div class="card mb-6">
                            <div class="card-header header-elements">
                                <h5 class="mb-0 me-2">Notes</h5>
                            </div>
                            <div class="card-body">
                                <ul class="timeline mb-0">
                                    @foreach ($lead->leadnotes as $enot)
                                    <li class="timeline-item timeline-item-transparent">
                                        <span class="timeline-point timeline-point-primary"></span>
                                        <div class="timeline-event">
                                            <div class="timeline-header mb-3">
                                                <h6 class="mb-0">EM</h6>
                                                <small class="text-body-secondary">
                                                    <span class="badge rounded-pill bg-label-danger">
                                                        <i class="fa-solid fa-trash delete-note" data-id="{{ $enot->id }}" style="cursor: pointer;"></i>
                                                    </span>

                                                </small>
                                            </div>
                                            <p class="mb-2">{{ $enot->note }}</p>
                                        </div>
                                    </li>
                                    @endforeach
                                </ul>
                                <div class="border-1 border-light border-dashed my-4"></div>

                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="Make note" id="lead_note" aria-label="Note" aria-describedby="button-addon2">
                                    <button class="btn btn-outline-primary waves-effect" type="button" id="button-addon2" onclick="addNotes({{ $lead->id }})">
                                        <i class="fa-solid fa-paper-plane fa-xl"></i>
                                    </button>
                                </div>
                                <div id="note-message" class="mt-2"></div>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- lead Notes -->
            </div>
            <!-- lead column -->

            <!-- lead location column -->
            <div class="col-12 col-lg-4">
                <div class="row mb-6 gy-6">
                    <div class="col-xl">
                        <div class="card mb-6">
                            <div class="card-header header-elements">
                                <h5 class="mb-0 me-2">lead Location</h5>

                                <div class="card-header-elements ms-auto">
                                    <div class="btn-group">

                                        @if($lead->lead_status == 'new' || $lead->lead_status == 'inprogress')
                                        <!-- Edit location -->
                                        <button
                                            type="button"
                                            class="btn btn-warning waves-effect waves-light"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editlocationModal">
                                            Edit
                                        </button>
                                        <!--/ Edit location -->
                                        @endif

                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="timeline-event ps-1">
                                    <iframe
                                        width="100%"
                                        height="450"
                                        frameborder="0"
                                        style="border:0"
                                        referrerpolicy="no-referrer-when-downgrade"
                                        src="https://www.google.com/maps/embed/v1/place?key={{ config('services.google.maps_api_key') }}&q={{ $lead->leadaddress->address }}, {{ $lead->leadaddress->county }}, {{ $lead->leadaddress->postcode }}+{{ $lead->leadaddress->address }}, {{ $lead->leadaddress->county }}, {{ $lead->leadaddress->country }}"
                                        allowfullscreen>
                                    </iframe>

                                </div>
                                <ul class="timeline mb-0">
                                    <li class="timeline-item ps-6 border-0">
                                        <span class="timeline-indicator-advanced timeline-indicator-primary border-0 shadow-none">
                                            <i class="icon-base ti tabler-map-pin"></i>
                                        </span>
                                        <div class="timeline-event ps-1">
                                            <div class="timeline-header">
                                                <small class="text-primary text-uppercase">lead Address</small>
                                            </div>
                                            <!-- <h6 class="my-50">Barry Schowalter</h6> -->
                                            <p class="text-body mb-0">{{ $lead->leadaddress->address }}, {{ $lead->leadaddress->county }}, {{ $lead->leadaddress->postcode }}</p>
                                        </div>
                                    </li>
                                </ul>

                                <!-- Edit location Modal -->
                                <div
                                    class="modal-onboarding modal fade animate__animated"
                                    id="editlocationModal"
                                    tabindex="-1"
                                    aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content text-center">
                                            <div class="modal-header border-0">
                                                <a
                                                    class="text-body-secondary close-label"
                                                    href="javascript:void(0);"
                                                    data-bs-dismiss="modal">Skip Intro</a>
                                                <button
                                                    type="button"
                                                    class="btn-close"
                                                    data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <form accept="application/json" method="POST" action="{{ route('leads.updateaddress', $lead->id) }}">
                                                @csrf
                                                <div class="modal-body p-0">
                                                    <div class="onboarding-content mb-2">
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <label class="form-label" for="formtabs-lead-address">Address</label>
                                                                <textarea class="form-control" id="formtabs-lead-address" name="lead_address" required>{{ $lead->leadaddress->address }}</textarea>
                                                            </div>
                                                            <div class="col-sm-6">
                                                                <label class="form-label" for="formtabs-lead-county">County</label>
                                                                <input class="form-control" id="formtabs-lead-county" name="lead_county" value="{{ $lead->leadaddress->county }}" required />
                                                            </div>
                                                            <div class="col-sm-6">
                                                                <label class="form-label" for="formtabs-lead-postcode">Postalcode</label>
                                                                <input class="form-control" id="formtabs-lead-postcode" name="lead_postcode" value="{{ $lead->leadaddress->postcode }}" required />
                                                            </div>
                                                            <input type="hidden" name="lead_id" value="{{ $lead->id }}">
                                                        </div>

                                                    </div>
                                                </div>
                                                <div class="modal-footer border-0">
                                                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
                                                        Close
                                                    </button>
                                                    <button type="submit" class="btn btn-success">Update</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <!--/ Edit location Modal -->
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mb-6 gy-6">
                    <div class="col-xl">
                        <div class="card h-100">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <div class="card-title mb-0">
                                    <h5 class="mb-1">Customer details</h5>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-text-secondary btn-icon rounded-pill text-body-secondary border-0 me-n1 waves-effect" type="button" id="salesByCountryTabs" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="icon-base ti tabler-dots-vertical icon-22px text-body-secondary"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="salesByCountryTabs">
                                        <a class="dropdown-item waves-effect" href="{{ route('customers.show', $lead->customer->id) }}">Update</a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="nav-align-top">
                                    <ul class="nav nav-tabs nav-fill rounded-0 timeline-indicator-advanced" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button type="button" class="nav-link active waves-effect" role="tab" data-bs-toggle="tab" data-bs-target="#navs-justified-details" aria-controls="navs-justified-new" aria-selected="true">
                                                Details
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button type="button" class="nav-link waves-effect" role="tab" data-bs-toggle="tab" data-bs-target="#navs-justified-link-billing" aria-controls="navs-justified-link-preparing" aria-selected="false" tabindex="-1">
                                                Billing
                                            </button>
                                        </li>
                                    </ul>
                                    <div class="tab-content border-0 mx-1">
                                        <div class="tab-pane fade show active" id="navs-justified-details" role="tabpanel">
                                            <ul class="timeline mb-0">
                                                <li class="timeline-item ps-6 border-dashed">
                                                    <span class="timeline-indicator-advanced timeline-indicator-success border-0 shadow-none">
                                                        <i class="icon-base ti tabler-circle-check"></i>
                                                    </span>
                                                    <div class="timeline-event ps-1">
                                                        <div class="timeline-header">
                                                            <small class="text-success text-uppercase">{{ $lead->customer->company_name }}</small>
                                                        </div>
                                                        <h6 class="my-50">
                                                            <span class="fw-medium">Contact: </span><span>{{ $lead->customer->contact_firstname }}</span> <span>{{ $lead->customer->contact_lastname }}</span>
                                                        </h6>

                                                        <p class="text-body mb-0">
                                                            <span class="text-heading fw-medium">eMail: </span> <span>{{ $lead->customer->contact_email }}</span> <br>
                                                            <span class="text-heading fw-medium">Phone: </span> <span>{{ $lead->customer->contact_phone }}</span> <br>
                                                            <span class="text-heading fw-medium">Address: </span> <span>{{ $lead->customer->contact_mobile }}</span> <br>
                                                        </p>
                                                    </div>
                                                </li>
                                            </ul>
                                            <div class="border-1 border-light border-dashed my-4"></div>

                                            <ul class="timeline mb-0">
                                                <li class="timeline-item ps-6 border-dashed">
                                                    <span class="timeline-indicator-advanced timeline-indicator-success border-0 shadow-none">
                                                        <i class="icon-base ti tabler-circle-check"></i>
                                                    </span>
                                                    <div class="timeline-event ps-1">
                                                        <div class="timeline-header">
                                                            <small class="text-success text-uppercase">
                                                                {{ $lead->customer->findetails->company_name ?? 'N/A' }} <br />
                                                                Reg No.: {{ $lead->customer->findetails->registration_number  ?? 'N/A' }}
                                                            </small>
                                                            <small class="text-success text-uppercase">
                                                                <button
                                                                    type="button"
                                                                    class="btn btn-primary btn-sm waves-effect waves-light"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#leaddetailseditmodal">
                                                                    Edit
                                                                </button>
                                                            </small>
                                                        </div>
                                                        <p class="text-body mb-0">
                                                            <span class="text-heading fw-medium">Assets: </span> <span>{{ $lead->customer->findetails->assets ?? 'N/A' }}</span> <br>
                                                            <span class="text-heading fw-medium">Net Assets: </span> <span>{{ $lead->customer->findetails->net_assets ?? 'N/A' }}</span> <br>
                                                            <span class="text-heading fw-medium">Liabilities: </span> <span>{{ $lead->customer->findetails->liabilities ?? 'N/A' }}</span> <br>
                                                            <span class="text-heading fw-medium">Cash in Bank: </span> <span>{{ $lead->customer->findetails->cash_in_bank ?? 'N/A' }}</span> <br>
                                                        </p>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="tab-pane fade" id="navs-justified-link-billing" role="tabpanel">
                                            @isset($lead->customer->billingaddress)
                                            <ul class="timeline mb-0">
                                                <li class="timeline-item ps-6 border-dashed">
                                                    <span class="timeline-indicator-advanced timeline-indicator-success border-0 shadow-none">
                                                        <i class="icon-base ti tabler-circle-check"></i>
                                                    </span>
                                                    <div class="timeline-event ps-1">
                                                        <div class="timeline-header">
                                                            <small class="text-success text-uppercase">{{ $lead->customer->billingaddress->contact_firstname ?? 'N/A' }} {{ $lead->customer->billingaddress->contact_lastname ?? 'N/A' }}</small>
                                                        </div>
                                                        <p class="text-body mb-0">
                                                            <span class="text-heading fw-medium">eMail: </span> <span>{{ $lead->customer->billingaddress->contact_email ?? 'N/A' }}</span> <br>
                                                            <span class="text-heading fw-medium">Phone: </span> <span>{{ $lead->customer->billingaddress->contact_phone ?? 'N/A' }}</span> <br>
                                                            <span class="text-heading fw-medium">Mobile: </span> <span>{{ $lead->customer->billingaddress->contact_mobile ?? 'N/A' }}</span> <br>
                                                        </p>
                                                    </div>
                                                </li>
                                                <li class="timeline-item ps-6 border-0">
                                                    <span class="timeline-indicator-advanced timeline-indicator-primary border-0 shadow-none">
                                                        <i class="icon-base ti tabler-map-pin"></i>
                                                    </span>
                                                    <div class="timeline-event ps-1">
                                                        <div class="timeline-header">
                                                            <small class="text-primary text-uppercase">Billing Address</small>
                                                        </div>
                                                        <h6 class="my-50">
                                                            <span class="fw-medium">Address: </span><span>{{ $lead->customer->billingaddress->address ?? 'N/A' }}</span> <br>
                                                            <span class="fw-medium">County: </span><span>{{ $lead->customer->billingaddress->county ?? 'N/A' }}</span> <br>
                                                            <span class="fw-medium">Postcode: </span><span>{{ $lead->customer->billingaddress->postcode ?? 'N/A' }}</span> <br>
                                                            <span class="fw-medium">Country: </span><span>{{ $lead->customer->billingaddress->country ?? 'N/A' }}</span> <br>
                                                        </h6>
                                                    </div>
                                                </li>
                                            </ul>
                                            @endisset
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Customar Financial Modal -->
        <div class="modal fade" id="leaddetailseditmodal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel1">Update Details</h5>
                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <form accept="application/json" method="POST" action="{{ route('customer.updatefinancialdetails', $lead->customer->id) }}">
                        @csrf
                        <div class="modal-body">
                            <div class="row g-6">
                                <div class="col-md-12">
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label text-sm-end" for="formtabs-contact-company">Company Name</label>
                                        <div class="col-sm-6">
                                            <input
                                                type="text"
                                                class="form-control"
                                                name="company_name"
                                                value="{{ $lead->customer->company_name }}" readonly />
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label text-sm-end" for="formtabs-assets">Reg. Number</label>
                                        <div class="col-sm-6">
                                            <input type="text"
                                                id="formtabs-assets"
                                                name="registration_no"
                                                class="form-control"
                                                placeholder="registration number"
                                                value="{{ $lead->customer->findetails->registration_number ?? '' }}" />
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-sm-end" for="formtabs-assets">Assets</label>
                                        <div class="col-sm-8">
                                            <input type="number"
                                                min="0"
                                                id="formtabs-assets"
                                                name="assets"
                                                class="form-control"
                                                placeholder="0.00"
                                                value="{{ $lead->customer->findetails->assets ?? 0 }}" />
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-sm-end" for="formtabs-net-assets">Net Assets</label>
                                        <div class="col-sm-8">
                                            <input type="number"
                                                min="0"
                                                id="formtabs-net-assets"
                                                name="netAssets"
                                                class="form-control"
                                                placeholder="0.00"
                                                value="{{ $lead->customer->findetails->net_assets ?? 0 }}" />
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-sm-end" for="formtabs-liabilities">Liabilities</label>
                                        <div class="col-sm-8">
                                            <input type="number"
                                                min="0"
                                                id="formtabs-liabilities"
                                                name="liabilities"
                                                class="form-control"
                                                placeholder="0.00"
                                                value="{{ $lead->customer->findetails->liabilities ?? 0 }}" />
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-sm-end" for="formtabs-cash-in-bank">Cash in Bank</label>
                                        <div class="col-sm-8">
                                            <input type="number"
                                                min="0"
                                                id="formtabs-cash-in-bank"
                                                name="cashInBank"
                                                class="form-control"
                                                placeholder="0.00"
                                                value="{{ $lead->customer->findetails->cash_in_bank ?? 0 }}" />
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
                                Close
                            </button>
                            <button type="submit" class="btn btn-primary">Save changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>
<!-- / Content -->

@endsection