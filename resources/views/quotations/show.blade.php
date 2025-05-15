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
                <span class="h5">QUOTATION FOR LEAD #{{ $quotation->lead->lead_name }} </span>
                <span class="badge bg-label-success me-1 ms-2">{{ $quotation->quotation_version }}</span>
                <span class="badge bg-label-info">{{ $quotation->status }}</span>
            </div>
            <p class="mb-0">{{ \Carbon\Carbon::parse($quotation->quotation_date)->format('F jS Y') }}</p>
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
                                {{ $quotation->customer->company_name }}
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
                    <strong>{{ $sections->section_name }}: </strong>
                    @if ($sections->content->sample_type == "textbox")
                    <p class="mb-6">
                        {!! $sections->content->sample_source !!}
                    </p>
                    @endif
                    @endforeach
                </div>
            </div>
        </div>

    </div>

</div>
<!-- / Content -->

@endsection