@extends('layouts.master')

@section('title', 'Quotations')

@section('scripts')
@parent

<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
<script src="{{asset('assets/vendor/libs/moment/moment.js')}}"></script>
<script src="{{asset('assets/vendor/libs/flatpickr/flatpickr.js')}}"></script>


@endsection

@section('styles')
@parent
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/flatpickr/flatpickr.css')}}" />

<!-- Row Group CSS -->
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-rowgroup-bs5/rowgroup.bootstrap5.css')}}" />

@endsection

@section('content')

<!-- Content -->
<div class="container-xxl flex-grow-1 container-p-y">
    <div
        class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-6 row-gap-4">
        <div class="d-flex flex-column justify-content-center">
            <div class="mb-1">
                <a href="{{ route( 'quotations.lead', $quotation->lead->id) }}"
                    class="btn btn-icon btn-label-secondary waves-effect btn-sm me-2">
                    <span class="icon-base ti tabler-corner-up-left-double icon-20px"></span>
                </a>
                <span class="h5">QUOTATION FOR LEAD #{{ $quotation->lead->lead_name }} </span>
                <span class="badge bg-label-success me-1 ms-2">{{ $quotation->quotation_version }}</span>
                <span class="badge bg-label-info">{{ $quotation->status }}</span>
            </div>

        </div>
        <div class="d-flex align-content-center flex-wrap gap-2">
            <a href="{{ route('quotations.download-pdf', $quotation->id) }}" class="btn btn-sm btn-label-primary">Download PDF</a>
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
                                <span style="text-transform: capitalize;">
                                    {{ $quotation->customer->billingaddress->address }} , {{ $quotation->customer->billingaddress->county }}
                                </span>
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
                    @foreach ( $quotation->sections as $sections )
                    <h5>{{ $sections->section_name }}: </h5>

                    @if ($sections->section_type == "textbox")
                    <p class="mb-6">
                        @isset($sections->content->sample_source)
                        {!! $sections->content->sample_source !!}
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

                    @endforeach
                </div>
            </div>
        </div>

    </div>

</div>
<!-- / Content -->

@endsection