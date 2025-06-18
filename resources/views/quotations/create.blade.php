@extends('layouts.master')

@section('title', 'Create Quotation')

@section('scripts')
@parent
<script src="{{asset('assets/vendor/libs/quill/katex.js')}}"></script>
<script src="{{asset('assets/vendor/libs/highlight/highlight.js')}}"></script>
<script src="{{asset('assets/vendor/libs/quill/quill.js')}}"></script>
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>
<!-- Vendors JS -->
<script src="{{asset('assets/vendor/libs/swiper/swiper.js')}}"></script>
<!-- Page JS -->
<script src="{{asset('assets/js/ui-carousel.js')}}"></script>
<!-- Page JS -->
<script>
    const fullToolbar = [
        [{
            header: [1, 2, false]
        }],
        ['bold', 'italic', 'underline'],
        ['code-block', 'blockquote'],
        [{
            'list': 'ordered'
        }, {
            'list': 'bullet'
        }],
        ['link', 'image'],
        ['clean']
    ];

    $(document).ready(function() {
        @foreach($quotationTemplate->sections as $qts)
        @if($qts -> section_type == 'textbox')
        const quill_{{$qts -> id}} = new Quill('#{{ $qts->section_slug }}', {
            placeholder: 'Type here...',
            modules: {
                syntax: true,
                toolbar: fullToolbar
            },
            theme: 'snow'
        });

        // Sync content before form submission
        $('form').on('submit', function() {
            $('#input-{{ $qts->section_slug }}').val(quill_{{$qts -> id}}.root.innerHTML);
        });
        @endif
        @endforeach
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('input[type="file"][id^="attachments-"]').forEach(input => {
            input.addEventListener('change', function(event) {
                const sectionId = this.id.split('-')[1];
                const previewContainer = document.getElementById(`preview-${sectionId}`);
                previewContainer.innerHTML = ''; // Clear previous previews

                Array.from(this.files).forEach(file => {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const img = document.createElement('img');
                            img.src = e.target.result;
                            img.style.height = '80px';
                            img.style.border = '1px solid #ccc';
                            img.style.padding = '2px';
                            img.style.borderRadius = '4px';
                            previewContainer.appendChild(img);
                        };
                        reader.readAsDataURL(file);
                    }
                });
            });
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
<link rel="stylesheet" href="{{asset('assets/vendor/libs/swiper/swiper.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/css/pages/ui-carousel.css')}}" />
@endsection

@section('content')
<!-- Content -->
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row gy-6">
        <!-- Quotation Templates -->
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div class="d-flex flex-column justify-content-center">
                        <div class="d-flex align-items-center">
                            <div class="mb-1">
                                <a href="{{ route( 'quotations.lead', $leaddata->id) }}"
                                    class="btn btn-icon btn-label-secondary waves-effect btn-sm me-2">
                                    <span class="icon-base ti tabler-corner-up-left-double icon-20px"></span>
                                </a>
                                <span class="h4">Create Quotation</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <form action="{{ route('quotations.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="features-icon-wrapper row gx-0 gy-6 g-sm-12">
                            <div class="col-lg-4 col-sm-6 text-center features-icon-box">
                                <div class="mb-4 text-primary text-center">
                                    <svg width="64" height="65" viewBox="0 0 64 65" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path opacity="0.2" d="M10 44.4663V18.4663C10 17.4054 10.4214 16.388 11.1716 15.6379C11.9217 14.8877 12.9391 14.4663 14 14.4663H50C51.0609 14.4663 52.0783 14.8877 52.8284 15.6379C53.5786 16.388 54 17.4054 54 18.4663V44.4663H10Z" fill="currentColor"></path>
                                        <path d="M10 44.4663V18.4663C10 17.4054 10.4214 16.388 11.1716 15.6379C11.9217 14.8877 12.9391 14.4663 14 14.4663H50C51.0609 14.4663 52.0783 14.8877 52.8284 15.6379C53.5786 16.388 54 17.4054 54 18.4663V44.4663M36 22.4663H28M6 44.4663H58V48.4663C58 49.5272 57.5786 50.5446 56.8284 51.2947C56.0783 52.0449 55.0609 52.4663 54 52.4663H10C8.93913 52.4663 7.92172 52.0449 7.17157 51.2947C6.42143 50.5446 6 49.5272 6 48.4663V44.4663Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                </div>
                                <h5 class="mb-2">Lead Name: {{ $leaddata->lead_name }}</h5>
                                <p class="features-icon-description">
                                    <span><strong>Lead Details:</strong></span> <span>
                                        {!! \Illuminate\Support\Str::limit($leaddata->lead_description, 30, '...') !!}
                                    </span>
                                </p>
                                <p><strong>Template Name: </strong> {{ $quotationTemplate->template_name }}</p>
                            </div>
                            <div class="col-lg-4 col-sm-6 features-icon-box">
                                <h5 class="mb-2">Company: {{ $customerdata->company_name }}</h5>
                                <ul class="list-unstyled my-3 py-1">
                                    <li class="d-flex align-items-center mb-4">
                                        <i class="icon-base ti tabler-user icon-lg"></i>
                                        <span class="fw-medium mx-2">Contact Person:</span>
                                        <span>{{ $customerdata->contact_firstname }} {{ $customerdata->contact_lastname }}</span>
                                    </li>
                                    <li class="d-flex align-items-center mb-4">
                                        <i class="icon-base ti tabler-phone-call icon-lg"></i><span class="fw-medium mx-2">Contact:</span>
                                        <span>{{ $customerdata->contact_phone }}</span>
                                    </li>
                                    <li class="d-flex align-items-center mb-4">
                                        <i class="icon-base ti tabler-messages icon-lg"></i>
                                        <span class="fw-medium mx-2">Mobile:</span> <span>{{ $customerdata->contact_mobile }}</span>
                                    </li>
                                    <li class="d-flex align-items-center mb-4">
                                        <i class="icon-base ti tabler-mail icon-lg"></i><span class="fw-medium mx-2">Email:</span>
                                        <span>{{ $customerdata->contact_email }}</span>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-lg-4 col-sm-6 features-icon-box">
                                <h5 class="mb-2">Site Address</h5>
                                <ul class="list-unstyled my-3 py-1">
                                    <li class="d-flex align-items-center mb-4">
                                        <i class="icon-base ti tabler-map-pin icon-lg"></i>
                                        <span class="fw-medium mx-2">Address:</span>
                                        <span>{{ $leaddata->leadaddress->address }}</span>
                                    </li>
                                    <li class="d-flex align-items-center mb-4">
                                        <i class="icon-base ti tabler-map-pin icon-lg"></i>
                                        <span class="fw-medium mx-2">County:</span>
                                        <span>{{ $leaddata->leadaddress->county }}</span>
                                    </li>
                                    <li class="d-flex align-items-center mb-4">
                                        <i class="icon-base ti tabler-map-pin icon-lg"></i>
                                        <span class="fw-medium mx-2">Country:</span>
                                        <span>{{ $leaddata->leadaddress->country }}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="row g-6">
                            <div class="col-md-4 mb-6">
                                <label for="quotationVersion" class="form-label">Quotation Version</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="quotationVersion"
                                    placeholder="version 1.0"
                                    name="quotation_version" required />
                            </div>
                            <div class="col-md-4 mb-6">
                                <label for="html5-date-input" class="form-label">Valid Until</label>
                                <div class="col-md-10">
                                    <input class="form-control" type="date" id="html5-date-input" name="quotation_date" required />
                                </div>
                            </div>
                            <div class="col-md-4 mb-6">
                                <label for="html5-date-input" class="form-label">Approximate Cost</label>
                                <div class="col-md-10">
                                    <input class="form-control" type="number" id="html5-date-input" name="quotation_amount" min="0" step="0.1" required />
                                </div>
                            </div>
                        </div>
                        <div class="row g-6">
                            @foreach ($quotationTemplate->sections as $qts)
                            @if($qts->section_type == 'textbox')
                            <div class="col-md-12 mb-6">
                                <label class="form-label" for="{{ $qts->section_slug }}">{{ $qts->section_name }}</label>

                                {{-- Hidden input to store the HTML from Quill --}}
                                <input type="hidden" name="{{ $qts->section_slug }}" id="input-{{ $qts->section_slug }}">

                                {{-- Div for the Quill editor --}}
                                <div id="{{ $qts->section_slug }}" class="form-control" style="min-height: 120px;">
                                    {!! $qts->samples->first() ? $qts->samples->first()->sample_source : '' !!}
                                </div>
                            </div>

                            @if($qts->has_attachments)
                            <div class="col-md-12 mb-4">
                                <label class="form-label" for="attachments-{{ $qts->id }}">Attachments (Multiple Images)</label>
                                <input type="file" class="form-control" id="attachments-{{ $qts->id }}" name="attachments[{{ $qts->section_slug }}][]" accept="image/*" multiple>

                                {{-- Preview container --}}
                                <div class="image-preview mt-2 d-flex flex-wrap gap-2" id="preview-{{ $qts->id }}"></div>
                            </div>
                            @endif

                            @endif
                            @endforeach
                        </div>

                        <input type="hidden" name="lead_id" value="{{ $leaddata->id }}">
                        <input type="hidden" name="customer_id" value="{{ $customerdata->id }}">
                        <input type="hidden" name="quotation_template_id" value="{{ $quotationTemplate->id }}">
                        <input type="hidden" name="job_type_id" value="{{ $quotationTemplate->job_type_id }}">

                        <div class="pt-6">
                            <button type="submit" class="btn btn-primary me-4">Update</button>
                            <button type="reset" class="btn btn-label-secondary">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- /Quotation Templates -->
    </div>
</div>
@endsection