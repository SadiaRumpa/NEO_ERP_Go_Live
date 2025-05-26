@extends('layouts.master')

@section('title', 'Quotations')

@section('scripts')
@parent

<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
<script src="{{asset('assets/vendor/libs/moment/moment.js')}}"></script>
<script src="{{asset('assets/vendor/libs/flatpickr/flatpickr.js')}}"></script>
<script src="https://code.jquery.com/ui/1.14.1/jquery-ui.js"></script>
<script>
    $(function() {
        $("#sortable").sortable();
    });
</script>

@endsection

@section('styles')
@parent
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/flatpickr/flatpickr.css')}}" />
<link rel="stylesheet" href="https://code.jquery.com/ui/1.14.1/themes/base/jquery-ui.css">

<!-- Row Group CSS -->
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-rowgroup-bs5/rowgroup.bootstrap5.css')}}" />

@endsection

@section('content')

<!-- Content -->
<div class="container-xxl flex-grow-1 container-p-y">
    <div
        class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-6 row-gap-2">
        <div class="d-flex flex-column justify-content-center">
            <div class="mb-1">
                <a href="{{ route( 'quotations.lead', $quotation->lead->id) }}"
                    class="btn btn-icon btn-label-secondary waves-effect btn-sm me-2">
                    <span class="icon-base ti tabler-corner-up-left-double icon-20px"></span>
                </a>
                <span class="h5">QUOTATION FOR LEAD #{{ $quotation->lead->lead_name }} </span>
                <span class="badge bg-label-success me-1 ms-2">{{ $quotation->quotation_version }}</span>
                @hasrole('lead-manager')
                <div class="btn-group">
                    @if($quotation->status == 'draft')
                    <button type="button" class="btn btn-label-info waves-effect">{{ $quotation->status }}</button>
                    @elseif($quotation->status == 'selected')
                    <button type="button" class="btn btn-label-success waves-effect">{{ $quotation->status }}</button>
                    @elseif($quotation->status == 'rejected')
                    <button type="button" class="btn btn-label-danger waves-effect">{{ $quotation->status }}</button>
                    @endif
                    <button type="button" class="btn btn-label-primary dropdown-toggle dropdown-toggle-split waves-effect" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="visually-hidden">Toggle Dropdown</span>
                    </button>
                    <ul class="dropdown-menu" style="">
                        <li><a class="dropdown-item waves-effect update-quotation-status" data-status="selected" data-id="{{ $quotation->id }}" href="#">Selected</a></li>
                        <li><a class="dropdown-item waves-effect update-quotation-status" data-status="rejected" data-id="{{ $quotation->id }}" href="#">Rejected</a></li>
                    </ul>
                </div>
                @else
                <span class="badge bg-label-primary me-1 ms-2">{{ $quotation->status }}</span>
                @endrole
                <span class="justify-content-center ms-12">
                    <a href="{{ route('quotations.download-pdf', $quotation->id) }}" class="btn btn-sm btn-label-dark">Download PDF</a>
                </span>
            </div>
        </div>

    </div>

    <div class="row">
        <div class="col-12 col-lg-8">
            <div class="card mb-6">
                <div class="card-header">
                    <div class="row">
                        <div class="col-6">
                            <p class="mb-6">
                                {{ \Carbon\Carbon::parse($quotation->quotation_date)->format('F jS Y') }}
                                <br><br>
                                {{ $quotation->customer->contact_firstname }} {{ $quotation->customer->contact_lastname }}
                                <br>
                                {{ $quotation->customer->company_name }}
                                <br>
                                @isset($quotation->customer->billingaddress)
                                <span style="text-transform: capitalize;">
                                    {{ $quotation->customer->billingaddress->address }} , {{ $quotation->customer->billingaddress->county }}
                                </span>
                                @endisset
                            </p>
                        </div>
                        <div class="col-6 text-end">
                            <p class="mb-6">
                                <img src="https://www.practicalroofing.co.uk/wp-content/uploads/media/xlogo-main.webp.pagespeed.ic.X6MBz-BBzW.webp" alt="Practical Roofing" class="mb-4" width="100">
                                <br>
                                Tel: 0800 924 7663
                                <br>
                                Email: info@practicalroofing.co.uk
                                <br>
                                Website: www.practicalroofing.co.uk
                            </p>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-1">
                    <div id="sortable">
                        @foreach ( $quotation->sections as $sections )
                        <div class="ui-state-default mb-4 p-4">
                            <span class="ui-icon ui-icon-arrowthick-2-n-s"></span>
                            <div class="card-title header-elements">
                                <h5 class="m-0 me-2">{{ $sections->section_name }}:</h5>
                                <div class="card-title-elements ms-auto">
                                    <button type="button" class="btn btn-icon btn-sm btn-warning waves-effect waves-light"
                                        data-bs-toggle="modal" data-bs-target="#modal{{ $sections->section_slug }}">
                                        <span class="icon-base ti tabler-edit icon-20px"></span>
                                    </button>

                                </div>
                            </div>

                            @if ($sections->section_type == "textbox")
                            <p class="mb-6">
                                @isset($sections->content->sample_source)
                                {!! $sections->content->sample_source !!}

                                <!-- Modal -->
                                <div class="modal fade" id="modal{{ $sections->section_slug }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="modalCenterTitle">Modal title</h5>
                                                <button
                                                    type="button"
                                                    class="btn-close"
                                                    data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col mb-4">
                                                        <label for="nameWithTitle" class="form-label">Name</label>
                                                        <input
                                                            type="text"
                                                            id="nameWithTitle"
                                                            class="form-control"
                                                            placeholder="Enter Name" />
                                                    </div>
                                                </div>
                                                <div class="row g-4">
                                                    <div class="col mb-0">
                                                        <label for="emailWithTitle" class="form-label">Email</label>
                                                        <input
                                                            type="email"
                                                            id="emailWithTitle"
                                                            class="form-control"
                                                            placeholder="xxxx@xxx.xx" />
                                                    </div>
                                                    <div class="col mb-0">
                                                        <label for="dobWithTitle" class="form-label">DOB</label>
                                                        <input type="date" id="dobWithTitle" class="form-control" />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
                                                    Close
                                                </button>
                                                <button type="button" class="btn btn-primary">Save changes</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endisset
                            </p>
                            @endif
                            @if($sections->has_attachments)
                            <div class="row">
                                @foreach ($sections->attachments as $saa)
                                <div class="col-6 mb-4">
                                    <img src="{{ $saa->source == 's3' ? $saa->attachment_url : asset($saa->attachment_path) }}" alt="{{ $saa->attachment_name }}" width="100%" class="img-fluid">
                                </div>
                                @endforeach
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>
<!-- / Content -->

@endsection