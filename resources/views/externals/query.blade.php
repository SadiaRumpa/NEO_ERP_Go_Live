@extends('layouts.blank')

@section('title', 'Create Enquiry')
@section('scripts')
@parent
<script>
    function checkaddress() {
        let postcode = $('#basic-default-postcode').val().replace(/\s/g, '');

        if (!postcode) {
            alert("Please enter a postcode.");
            return;
        }

        $('#loading-spinner').show();
        $('#address-suggestions').hide().empty(); // Clear old suggestions

        $.ajax({
            url: `/checkpostcode/${postcode}`,
            method: 'GET',
            success: function(response) {
                $('#loading-spinner').hide();

                if (!response || response.error || !response.address || !Array.isArray(response.address.addresses)) {
                    $('#basic-default-address').val("No address found.");
                    return;
                }

                let suggestions = response.address.addresses;

                if (suggestions.length === 0) {
                    $('#basic-default-address').val("No address found.");
                    return;
                }

                let suggestionList = suggestions.map(address =>
                    `<a href="#" class="list-group-item list-group-item-action suggestion-item">${address}</a>`
                ).join('');

                $('#address-suggestions').html(suggestionList).show();
                $('#posttown').val(response.address.post_town);
            },
            error: function() {
                $('#loading-spinner').hide();
                $('#address-suggestions').hide();
                $('#basic-default-address').val("Error fetching address.");
            }
        });
    }
    $(document).ready(function() {
        // Event listener to handle clicking on a suggestion (run this once on page load)
        $(document).on('click', '.suggestion-item', function(e) {
            e.preventDefault();
            let selectedAddress = $(this).text();
            let posttown = $('#posttown').val();
            $.ajax({
                url: `/checkposttown/${posttown}`,
                method: 'GET',
                success: function(response) {
                    if (response && response.address) {
                        $('#basic-default-address').val(selectedAddress);
                        $('#basic-default-county').val(response.address.county);
                        $('#basic-default-country').val(response.address.country);
                        $('#address-suggestions').hide();
                    } else {
                        $('#basic-default-address').val("Error fetching address.");
                    }
                },
                error: function() {
                    $('#basic-default-address').val("Error fetching address.");
                }
            });


        });

        // Hide suggestions when clicking outside
        $(document).click(function(event) {
            if (!$(event.target).closest('#basic-default-postcode, #address-suggestions').length) {
                $('#address-suggestions').hide();
            }
        });
    });
</script>

<script>
    $('#queryForm').on('submit', function(e) {
        e.preventDefault();

        let formData = new FormData(this);

        $.ajax({
            url: "{{ route('query.submit') }}", // Adjust as needed
            method: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                const successBox = $('#success-message');

                successBox.show();
                $('html, body').animate({
                    scrollTop: successBox.offset().top - 100 // Adjust offset if needed
                }, 500, function() {
                    successBox.focus();
                });

                $('#queryForm')[0].reset();

                setTimeout(() => {
                    location.reload();
                }, 2000);
            },
            error: function(xhr) {
                alert("Something went wrong. Please check and try again.");
                console.log(xhr.responseText);
            }
        });
    });
</script>
<script>
    // Try to get the referrer or parent domain (if allowed)
    try {
        const referrer = document.referrer || window.top.document.referrer;
        document.getElementById('enquiry_source').value = referrer || 'unknown';
    } catch (e) {
        document.getElementById('enquiry_source').value = 'unknown';
    }
</script>

@endsection

@section('styles')
@parent
<style>
    .suggestion-item {
        cursor: pointer;
    }

    .suggestion-item:hover {
        background-color: #f0f0f0;
    }

    #address-suggestions {
        max-height: 200px;
        overflow-y: auto;
        border: 1px solid #ccc;
        position: absolute;
        z-index: 9999;
        background-color: rgb(255, 157, 157);
        color: rgb(0, 0, 0);
        margin-top: -10px;
    }
</style>
@endsection

@section('content')
<!-- Content -->
<div class="row">
    <div class="col-xl">

        <div class="card">
            <div id="success-message" class="alert alert-success" style="display: none;" tabindex="-1">
                Your query has been submitted successfully!
            </div>
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Ask us</h5>
            </div>
            <div class="card-body">
                <div class="row mb-6 gy-6">
                    <div class="col-md-4 col-4">
                        <form id="queryForm" class="needs-validation" enctype="multipart/form-data" novalidate action="{{ route('enquiries.store') }}" method="POST">
                            @csrf
                            <div class="mb-6">
                                <label class="form-label" for="basic-default-firstname">First Name</label>
                                <input type="text" class="form-control" id="basic-default-firstname" name="firstname" placeholder="John" required />
                            </div>
                            <div class="mb-6">
                                <label class="form-label" for="basic-default-lastname">Last Name</label>
                                <input type="text" class="form-control" id="basic-default-lastname" name="lastname" placeholder="Doe" />
                            </div>
                            <div class="mb-6">
                                <label class="form-label" for="basic-default-company">Company</label>
                                <input type="text" class="form-control" id="basic-default-company" name="company" placeholder="ACME Inc." required />
                            </div>
                            <div class="mb-6">
                                <label class="form-label" for="basic-default-phone">Phone No</label>
                                <input
                                    type="text"
                                    id="basic-default-phone"
                                    class="form-control phone-mask"
                                    name="phone"
                                    placeholder="(123) 456-7890"
                                    required />
                            </div>
                            <div class="mb-6">
                                <label class="form-label" for="basic-default-email">Email</label>
                                <input
                                    type="text"
                                    id="basic-default-email"
                                    class="form-control"
                                    name="email"
                                    placeholder="john.doe@email.com"
                                    aria-label="john.doe@email.com"
                                    aria-describedby="basic-default-email" required />

                                <div class="form-text">You can use letters, numbers & periods</div>
                            </div>

                            <div class="mb-6">
                                <label class="form-label" for="basic-default-message">Description of Job</label>
                                <textarea
                                    id="basic-default-message"
                                    class="form-control"
                                    placeholder="Hi, My roof needs some care."
                                    name="jobdescription" required></textarea>
                            </div>

                            <label class="form-label" for="basic-default-postcode">Postcode</label>
                            <div class="input-group">
                                <input
                                    type="text"
                                    class="form-control"
                                    id="basic-default-postcode"
                                    name="postcode"
                                    placeholder="WR11 7EJ"
                                    aria-label="WR11 7EJ"
                                    aria-describedby="button-addon2"
                                    oninput="this.value = this.value.toUpperCase()" required />
                                <button class="btn btn-outline-primary" type="button" id="button-addon2" onclick="checkaddress()">Check</button>
                            </div>
                            <div id="loading-spinner" style="display: none;">
                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                Searching...
                            </div>
                            <!-- Suggestions Container -->
                            <div id="address-suggestions" class="list-group mt-1" style="display:none;"></div>
                            <input type="hidden" id="posttown" value="" />

                            <div class="mb-6">
                                <label class="form-label" for="basic-default-address">Address</label>
                                <textarea
                                    id="basic-default-address"
                                    class="form-control"
                                    name="address"
                                    name="basic-default-address" required></textarea>
                            </div>

                            <div class="mb-6">
                                <label class="form-label" for="basic-default-county">County</label>
                                <input
                                    type="text"
                                    id="basic-default-county"
                                    name="county"
                                    class="form-control"
                                    placeholder="Worcestershire" />
                            </div>

                            <div class="mb-6">
                                <label class="form-label" for="basic-default-country">Country</label>
                                <input
                                    type="text"
                                    id="basic-default-country"
                                    name="country"
                                    class="form-control"
                                    placeholder="England" />
                            </div>

                            <div class="mb-6">
                                <label class="form-label" for="basic-default-upload-file">Photo 1</label>
                                <input type="file" class="form-control" id="basic-default-upload-file" name="photo1" accept=".jpg, .jpeg, .png" required />
                            </div>
                            <div class="mb-6">
                                <label class="form-label" for="basic-default-upload-file">Photo 2</label>
                                <input type="file" class="form-control" id="basic-default-upload-file" accept=".jpg, .jpeg, .png" name="photo2" />
                            </div>
                            <div class="mb-6">
                                <label class="form-label" for="basic-default-upload-file">Photo 3</label>
                                <input type="file" class="form-control" id="basic-default-upload-file" accept=".jpg, .jpeg, .png" name="photo3" />
                            </div>

                            <input type="hidden" name="enquiry_source" id="enquiry_source">

                            {!! \Anhskohbo\NoCaptcha\Facades\NoCaptcha::display() !!}
                            {!! \Anhskohbo\NoCaptcha\Facades\NoCaptcha::renderJs() !!}
                            <input type="hidden" name="g-recaptcha-response" value="some-token">
                            <button type="submit" class="btn btn-primary">SUBMIT</button>
                            <button type="reset" class="btn btn-secondary">RESET</button>
                        </form>

                    </div>
                </div>
                <div class="row mb-6 gy-6">
                    <h5 class="card-title">Before you upload your roofing photos:</h5>
                    <p class="card-text">Here are a few things to get straight before trying to upload:</p>
                    <ul class="ps-3">
                        <li>
                            Know where your photos are on your computer. Pay particular attention to the folder used by your camera download program, if appropriate. If you cannot find the folder, take the photos off your camera again and use the save-as command to put them where you can find them (a folder on the desktop is a good choice).
                        </li>
                        <li>
                            Rename the photos so you can figure out where they are.
                        </li>
                        <li>
                            If you know how to crop and/or resize the photos.
                        </li>
                        <li>
                            Take photos of the problem areas, and include photos showing access to the property.
                        </li>
                        <li>
                            <strong>Please Note!</strong> We can receive photos of up to <strong>10MB total in size</strong>.
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- / Content -->



@endsection