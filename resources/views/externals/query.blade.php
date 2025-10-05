@extends('layouts.blank')

@section('title', 'Create Enquiry')
@section('scripts')
    @parent
    <script>
        function checkaddress() {
            let postcode = $('#basic-default-postcode').val().replace(/\s/g, '');

            if (!postcode) {
                toastr.error("Please enter a postcode.");
                return;
            }

            $('#loading-spinner').show();
            $('#address-suggestions').hide().empty(); // Clear old suggestions

            $.ajax({
                url: `/checkpostcode/${postcode}`,
                method: 'GET',
                success: function(response) {
                    $('#loading-spinner').hide();

                    if (!response || response.error || !response.address || !Array.isArray(response.address
                            .addresses)) {
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
                    $('#queryForm').trigger('reset');
                    toastr.success("Your query has been submitted successfully!");
                    window.location.reload();
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON.message ||
                        "An error occurred while submitting the form.");
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
    <script>
        function previewFile(input, previewId) {
            const file = input.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = e => {
                    const img = document.querySelector(previewId);
                    img.src = e.target.result;
                    img.classList.remove("d-none");
                };
                reader.readAsDataURL(file);
            }
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

            <div class="card shadow-lg rounded-2xl border-0 overflow-hidden">
                <div class="card-header bg-primary text-white text-center py-3">
                    <h4 class="mb-0">Submit Your Roofing Enquiry</h4>
                </div>
                <div class="card-body p-4">
                    <form id="queryForm" class="needs-validation" enctype="multipart/form-data" novalidate
                        action="{{ route('enquiries.store') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">First Name</label>
                                <input type="text" class="form-control" name="firstname" placeholder="John" required />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Last Name</label>
                                <input type="text" class="form-control" name="lastname" placeholder="Doe" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Company</label>
                                <input type="text" class="form-control" name="company" placeholder="ACME Inc."
                                    required />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Phone No</label>
                                <input type="text" name="phone" class="form-control" placeholder="14169884411"
                                    maxlength="11" pattern="\d{11}" inputmode="numeric"
                                    oninput="this.value = this.value.replace(/\D/g, '').slice(0, 11);" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Mobile No</label>
                                <input type="text" name="mobile" class="form-control" placeholder="14169884411"
                                    maxlength="11" pattern="\d{11}" inputmode="numeric"
                                    oninput="this.value = this.value.replace(/\D/g, '').slice(0, 11);" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Email</label>
                                <input type="email" class="form-control" name="email" placeholder="example@email.com"
                                    required />
                                <div class="form-text">You can use letters, numbers & periods</div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Description of Job</label>
                                <textarea class="form-control" placeholder="Hi, My roof needs some care." name="jobdescription" rows="3" required></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="basic-default-postcode">Postcode</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="basic-default-postcode" name="postcode"
                                        placeholder="WR11 7EJ" aria-label="WR11 7EJ" aria-describedby="button-addon2"
                                        oninput="this.value = this.value.toUpperCase()" required />
                                    <button class="btn btn-outline-primary" type="button" id="button-addon2"
                                        onclick="checkaddress()">Check</button>
                                </div>
                                <div id="loading-spinner" style="display: none;">
                                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                    Searching...
                                </div>

                                <!-- Suggestions Container -->
                                <div id="address-suggestions" class="list-group mt-1" style="display:none;"></div>
                                <input type="hidden" id="posttown" value="" />
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Address</label>
                                <textarea id="basic-default-address" class="form-control" name="address" rows="2" required></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">County</label>
                                <input type="text" id="basic-default-county" name="county" class="form-control"
                                    placeholder="Worcestershire" required />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Country</label>
                                <input type="text" id="basic-default-country" name="country" class="form-control"
                                    placeholder="England" required />
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Photo 1</label>
                                <input type="file" class="form-control" name="photo1" accept=".jpg,.jpeg,.png"
                                    required onchange="previewFile(this, '#preview1')" />
                                <img id="preview1" class="img-thumbnail mt-2 d-none" style="max-height: 120px;" />
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Photo 2</label>
                                <input type="file" class="form-control" name="photo2" accept=".jpg,.jpeg,.png"
                                    onchange="previewFile(this, '#preview2')" />
                                <img id="preview2" class="img-thumbnail mt-2 d-none" style="max-height: 120px;" />
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Photo 3</label>
                                <input type="file" class="form-control" name="photo3" accept=".jpg,.jpeg,.png"
                                    onchange="previewFile(this, '#preview3')" />
                                <img id="preview3" class="img-thumbnail mt-2 d-none" style="max-height: 120px;" />
                            </div>
                            <div class="col-12">
                                <input type="hidden" name="enquiry_source" id="enquiry_source">
                                {!! \Anhskohbo\NoCaptcha\Facades\NoCaptcha::display() !!}
                                {!! \Anhskohbo\NoCaptcha\Facades\NoCaptcha::renderJs() !!}
                            </div>
                            <div class="col-12 d-flex justify-content-between align-items-center mt-3">
                                <button type="submit" class="btn btn-primary w-50 me-2">
                                    <span id="submit-text">SUBMIT</span>
                                    <span id="submit-spinner" class="spinner-border spinner-border-sm d-none"
                                        role="status" aria-hidden="true"></span>
                                </button>
                                <button type="reset" class="btn btn-secondary w-50">RESET</button>
                            </div>
                        </div>
                    </form>
                    <hr class="my-4" />
                    <div>
                        <h5 class="card-title">Before you upload your roofing photos:</h5>
                        <p class="card-text">Please review these tips to ensure a smooth upload:</p>
                        <ul class="list-unstyled ps-2">
                            <li>✔️ Ensure you know where your photos are stored on your computer.</li>
                            <li>✔️ Rename your photos so you can identify them easily.</li>
                            <li>✔️ Crop/resize photos if possible for clarity.</li>
                            <li>✔️ Take photos of problem areas and include access points.</li>
                            <li><strong>📌 Note:</strong> We can receive up to <strong>10MB total</strong> photo size.</li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <!-- / Content -->



@endsection
