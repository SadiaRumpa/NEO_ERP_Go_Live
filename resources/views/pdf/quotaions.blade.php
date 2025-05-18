<!DOCTYPE html>
<html>

<head>
    <title>Quotation</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        .card {
            border: 1px solid #ccc;
            padding: 20px;
            margin-bottom: 20px;
        }

        .card-header,
        .card-body {
            margin-bottom: 20px;
        }

        .text-end {
            text-align: right;
        }
    </style>
</head>

<body>
    <div class="row">
        <div class="col-12 col-lg-8">
            <div class="card mb-6">
                <div class="card-header">
                    <div class="row">
                        <div class="col-6 text-end">
                            <p class="mb-6">
                                <img src="https://www.practicalroofing.co.uk/wp-content/uploads/media/xlogo-main.webp.pagespeed.ic.X6MBz-BBzW.webp" width="100" alt="Practical Roofing">
                                <br>
                                Tel: 0800 924 7663
                                <br>
                                Email: info@practicalroofing.co.uk
                                <br>
                                Website: www.practicalroofing.co.uk
                            </p>
                        </div>
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
                        
                    </div>
                </div>
                <div class="card-body pt-1">
                    @foreach ($quotation->sections as $sections)
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
</body>

</html>