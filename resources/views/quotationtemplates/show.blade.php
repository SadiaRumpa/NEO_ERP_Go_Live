@extends('layouts.master')

@section('title', 'Quotation Template')

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
                <h5 class="card-header">Quotation Templates</h5>
                <div class="card-body">
                    <form action="{{ route('quotationtemplate.section-populate') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @if (count($errors) > 0)
                        <div class="alert alert-danger">
                            <strong>Whoops!</strong> There were some problems with your input.<br><br>
                            <ul>
                                @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif

                        @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                        @endif

                        <div class="row g-6">
                            <div class="col-md-6 mb-6">
                                <p><strong>Job Type: </strong> {{ $quotationTemplate->job_type_name }}</p>
                            </div>
                            <div class="col-md-6 mb-6">
                                <p><strong>Template Name: </strong> {{ $quotationTemplate->template_name }}</p>
                                <input type="hidden" name="quotation_template_id" value="{{ $quotationTemplate->id }}">
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
                                    @if(count($qts->samples) > 0)
                                    {!! $qts->samples[0]->sample_source !!}
                                    @endif
                                </div>
                            </div>
                            @endif
                            @endforeach
                        </div>

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