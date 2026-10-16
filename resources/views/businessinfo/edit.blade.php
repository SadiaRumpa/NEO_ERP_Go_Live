@extends('layouts.master')

@section('title', 'Edit Business Info')

@section('scripts')
@parent
<script src="{{asset('assets/vendor/libs/quill/katex.js')}}"></script>
<script src="{{asset('assets/vendor/libs/quill/quill.js')}}"></script>
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>
<script src="{{asset('assets/vendor/libs/dropzone/dropzone.js')}}"></script>
<script src="{{asset('assets/vendor/libs/jquery-repeater/jquery-repeater.js')}}"></script>
<script src="{{asset('assets/vendor/libs/flatpickr/flatpickr.js')}}"></script>
<script src="{{asset('assets/vendor/libs/tagify/tagify.js')}}"></script>
<!-- Page JS -->
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

    function triggerCustomFileInput(inputId, e) {
        if (e && e.target && e.target.closest('.custom-file-help-icon')) {
            return;
        }
        const fileInput = document.getElementById(inputId);
        if (fileInput) {
            fileInput.click();
        }
    }

    function handleCustomFileChange(input, labelSelector, previewSelector) {
        const file = input.files && input.files[0];
        const labelEl = document.querySelector(labelSelector);
        if (labelEl) {
            labelEl.textContent = file ? file.name : 'No file chosen';
            if (file) {
                labelEl.classList.remove('text-muted');
                labelEl.classList.add('text-body', 'fw-medium');
            } else {
                labelEl.classList.add('text-muted');
                labelEl.classList.remove('text-body', 'fw-medium');
            }
        }
        previewFile(input, previewSelector);
    }

    async function copyToClipboard(textToCopy) {
        if (navigator.clipboard && window.isSecureContext) {
            await navigator.clipboard.writeText(textToCopy);
        } else {
            const textArea = document.createElement("textarea");
            textArea.value = textToCopy;
            textArea.style.position = "absolute";
            textArea.style.left = "-999999px";
            document.body.prepend(textArea);
            textArea.select();
            try {
                document.execCommand('copy');
            } catch (error) {
                console.error(error);
            } finally {
                textArea.remove();
            }
        }
        if (typeof toastr !== 'undefined') {
            toastr.info("Image URL copied to clipboard!");
        }
    }

    const DEFAULT_SNEAT_SVG = `<span class="text-primary"><svg width="32" height="22" viewBox="0 0 32 22" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M0.00172773 0V6.85398C0.00172773 6.85398 -0.133178 9.01207 1.98092 10.8388L13.6912 21.9964L19.7809 21.9181L18.8042 9.88248L16.4951 7.17289L9.23799 0H0.00172773Z" fill="currentColor"></path><path opacity="0.06" fill-rule="evenodd" clip-rule="evenodd" d="M7.69824 16.4364L12.5199 3.23696L16.5541 7.25596L7.69824 16.4364Z" fill="#161616"></path><path opacity="0.06" fill-rule="evenodd" clip-rule="evenodd" d="M8.07751 15.9175L13.9419 4.63989L16.5849 7.28475L8.07751 15.9175Z" fill="#161616"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M7.77295 16.3566L23.6563 0H32V6.88383C32 6.88383 31.8262 9.17836 30.6591 10.4057L19.7824 22H13.6938L7.77295 16.3566Z" fill="currentColor"></path></svg></span>`;
    let activeLogoImageSrc = '';

    function updateSidebarLogoPreview() {
        const mode = $('input[name="brand_logo_mode"]:checked').val() || 'default';
        const appName = $('#formtabs-brand-app-name').val() || 'NEOERP';
        const showText = $('#brand_logo_show_text').is(':checked');
        const textHtml = showText ? `<span class="app-brand-text demo menu-text fw-bold ms-3">${appName}</span>` : '';

        if (mode === 'custom') {
            $('#logo-upload-container').addClass('d-none');
            $('#logo-custom-container').removeClass('d-none');
            const customVal = $('#business_brand_logo_on_top_left').val();
            $('#brand-logo-preview').html(customVal);
            $('#constructed_brand_logo').val(customVal);
            return;
        } else {
            $('#logo-custom-container').addClass('d-none');
        }

        let iconHtml = '';
        if (mode === 'upload') {
            $('#logo-upload-container').removeClass('d-none');
            if (activeLogoImageSrc) {
                iconHtml = `<img src="${activeLogoImageSrc}" alt="${appName}" style="max-height: 28px; width: auto; object-fit: contain;">`;
            } else {
                iconHtml = DEFAULT_SNEAT_SVG;
            }
        } else if (mode === 'business') {
            $('#logo-upload-container').addClass('d-none');
            const bizLogo = $('#business-logo-source-url').val();
            if (bizLogo) {
                iconHtml = `<img src="${bizLogo}" alt="${appName}" style="max-height: 28px; width: auto; object-fit: contain;">`;
            } else {
                iconHtml = `<span class="badge bg-label-warning py-1 px-2">No Business Logo uploaded in Tab 1</span>`;
            }
        } else { // default
            $('#logo-upload-container').addClass('d-none');
            iconHtml = DEFAULT_SNEAT_SVG;
        }

        const fullHtml = `<span class="app-brand-logo demo">${iconHtml}</span>${textHtml}`;
        $('#brand-logo-preview').html(fullHtml);
        $('#constructed_brand_logo').val(fullHtml);
        $('#business_brand_logo_on_top_left').val(fullHtml);
    }

    function previewSidebarLogoFile(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                activeLogoImageSrc = e.target.result;
                $('#logoModeUpload').prop('checked', true);
                updateSidebarLogoPreview();
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function selectAssetAsSidebarLogo(url) {
        activeLogoImageSrc = url;
        $('#logoModeUpload').prop('checked', true);
        updateSidebarLogoPreview();
        if (typeof toastr !== 'undefined') {
            toastr.success("Applied to Sidebar Logo preview!");
        }
    }

    $(function() {
        $('.select2').select2();

        // Detect initial logo mode from current business_brand_logo_on_top_left
        const currentLogo = $('#business_brand_logo_on_top_left').val() || '';
        const imgMatch = currentLogo.match(/<img[^>]+src=["']([^"']+)["']/i);
        if (imgMatch) {
            activeLogoImageSrc = imgMatch[1];
            const bizLogo = $('#business-logo-source-url').val();
            if (bizLogo && activeLogoImageSrc === bizLogo) {
                $('#logoModeBusiness').prop('checked', true);
            } else {
                $('#logoModeUpload').prop('checked', true);
            }
        } else if (currentLogo.indexOf('<svg') !== -1 && currentLogo.indexOf('M0.00172773') !== -1) {
            $('#logoModeDefault').prop('checked', true);
        } else if (currentLogo.trim() !== '') {
            $('#logoModeCustom').prop('checked', true);
        }

        // Check if text is currently shown
        if (currentLogo.indexOf('menu-text') === -1 && currentLogo.trim() !== '') {
            $('#brand_logo_show_text').prop('checked', false);
        }

        // Event listeners for logo
        $('input[name="brand_logo_mode"]').on('change', updateSidebarLogoPreview);
        $('#brand_logo_show_text').on('change', updateSidebarLogoPreview);
        $('#formtabs-brand-app-name').on('input', function() {
            updateSidebarLogoPreview();
        });
        $('#business_brand_logo_on_top_left').on('input', function() {
            $('#brand-logo-preview').html($(this).val());
            $('#constructed_brand_logo').val($(this).val());
        });

        // Brand assets copy link
        $(document).on('click', '.copy-asset-url', function(e) {
            e.preventDefault();
            const url = $(this).data('url');
            if (url) {
                copyToClipboard(url);
            }
        });

        // Initial preview rendering
        updateSidebarLogoPreview();

        // Initialize tooltips
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.forEach(function (tooltipTriggerEl) {
                new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
    });
</script>
@endsection

@section('styles')
@parent
<link rel="stylesheet" href="{{asset('assets/vendor/libs/quill/typography.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/quill/katex.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/quill/editor.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/dropzone/dropzone.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/flatpickr/flatpickr.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/tagify/tagify.css')}}" />

@endsection

@section('content')
<!-- Content -->
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="app-ecommerce">
        @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
        <form action="{{ route('businessinfo.update', $businessinfo->id) }}" method="POST"  enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @if ($errors->any())
                    <div class="alert alert-danger mt-2">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            <div
                class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-6 row-gap-4">
                <div class="d-flex flex-column justify-content-center">
                    <h4 class="mb-1">Business Info Details</h4>
                </div>
                <div class="d-flex align-content-center flex-wrap gap-4">
                    <div class="d-flex gap-4">
                        <button type="submit" class="btn btn-label-success">UPDATE</button>
                        <!-- <button class="btn btn-label-secondary">Discard</button> -->
                    </div>

                </div>
            </div>

            <div class="row">
                <div class="col-12 col-lg-12">
                    <div class="card mb-6">
                        <div class="card-header">
                            <!-- <h5 class="card-title mb-0">New Business Info</h5> -->
                        </div>
                        <div class="card-body">
                            <div class="card mb-6">
                                <div class="card-header px-0 pt-0">
                                    <div class="nav-align-top">
                                        <ul class="nav nav-tabs" role="tablist">
                                            <li class="nav-item">
                                                <button
                                                    type="button"
                                                    class="nav-link active"
                                                    data-bs-toggle="tab"
                                                    data-bs-target="#form-tabs-businessinfo"
                                                    aria-controls="form-tabs-businessinfo"
                                                    role="tab"
                                                    aria-selected="true">
                                                    <span class="icon-base ti tabler-user icon-lg d-sm-none"></span>
                                                    <span class="d-none d-sm-block">Business Info</span>
                                                </button>
                                            </li>
                                            <li class="nav-item">
                                                <button
                                                    type="button"
                                                    class="nav-link"
                                                    data-bs-toggle="tab"
                                                    data-bs-target="#form-tabs-billing-address"
                                                    aria-controls="form-tabs-billing-address"
                                                    role="tab"
                                                    aria-selected="false">
                                                    <span class="icon-base ti tabler-link icon-lg d-sm-none"></span>
                                                    <span class="d-none d-sm-block">Brand Info</span>
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="card-body">
                                    <div class="tab-content p-0">
                                        <!-- Compnay Info -->
                                        <div class="tab-pane active show" id="form-tabs-businessinfo" role="tabpanel">
                                        
                                        <div class="row g-6">
                                            <div class="col-md-12">
                                                <div class="row">
                                                    <label class="col-sm-2 col-form-label text-sm-end"
                                                        for="formtabs-businessname">Business Name</label>
                                                    <div class="col-sm-10">
                                                        <input type="text" id="formtabs-businessname" class="form-control"
                                                            placeholder="Business Name" aria-label="jdoe1 ltd" name="business_name"
                                                            required value="{{ $businessinfo->business_name }}" />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="row">
                                                    <label class="col-sm-2 col-form-label text-sm-end"
                                                        for="formtabs-contact-businessshortname">Business Short Name</label>
                                                    <div class="col-sm-5">
                                                        <input type="text" class="form-control"
                                                            id="formtabs-contact-businessshortname" placeholder="Business Short Name"
                                                            name="businessShortName" aria-label="Business Short Name" value="{{ $businessinfo->business_short_name }}" />
                                                    </div>
                                                    <div class="col-sm-5">
                                                        <input type="text" class="form-control"
                                                            id="formtabs-contact-businessdomain" placeholder="Business Domain"
                                                            name="businessDomain" aria-label="Business Domain" value="{{ $businessinfo->business_domain }}" />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="row">
                                                    <label class="col-sm-2 col-form-label text-sm-end"for="form-business-start-date">Business Start Date</label>
                                                    <div class="col-sm-5">
                                                        <input type="date" class="form-control flatpickr" id="form-business-start-date"
                                                        name="businessStartDate" placeholder="Business Start Date" aria-label="Business Start Date" value="{{ $businessinfo->business_start_date }}" />
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select id="businessStatus" class="form-select" name="businessStatus" required>
                                                                <option value="Active"
                                                                    {{ old('businessStatus', $businessinfo->business_status) == 'Active' ? 'selected' : '' }}>
                                                                    Active</option>
                                                                <option value="Inactive"
                                                                    {{ old('businessStatus', $businessinfo->business_status) == 'Inactive' ? 'selected' : '' }}>
                                                                    Inactive</option>
                                                            </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="row">
                                                    <label class="col-sm-3 col-form-label text-sm-end" for="businessLogo">Logo</label>
                                                    <div class="col-sm-9">
                                                        <div class="custom-file-wrapper position-relative">
                                                            <input class="d-none custom-native-file" type="file" name="businessLogo" id="businessLogo" accept=".jpg,.jpeg,.png" 
                                                                onchange="handleCustomFileChange(this, '#logoFileName', '#previewLogo')">
                                                            <div class="form-control custom-file-box d-flex align-items-center p-0 overflow-hidden" onclick="triggerCustomFileInput('businessLogo', event)">
                                                                <span class="custom-file-btn border-end d-inline-flex align-items-center gap-1 text-nowrap user-select-none">
                                                                    <span>Choose File</span>
                                                                    <i class="bx bx-help-circle custom-file-help-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="jpg, jpeg, png formats only" onclick="event.stopPropagation()"></i>
                                                                </span>
                                                                <span id="logoFileName" class="text-muted px-3 text-truncate small">No file chosen</span>
                                                            </div>
                                                        </div>
                                                        <img id="previewLogo" class="img-thumbnail mt-2 d-none1" style="max-height: 120px;" src="{{ $businessinfo->business_logo }}" />
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="row">
                                                    <label class="col-sm-3 col-form-label text-sm-end" for="businessLogoDark">Logo Dark</label>
                                                    <div class="col-sm-9">
                                                        <div class="custom-file-wrapper position-relative">
                                                            <input class="d-none custom-native-file" type="file" name="businessLogoDark" id="businessLogoDark" accept=".jpg,.jpeg,.png"  
                                                                onchange="handleCustomFileChange(this, '#logoDarkFileName', '#previewLogoDark')">
                                                            <div class="form-control custom-file-box d-flex align-items-center p-0 overflow-hidden" onclick="triggerCustomFileInput('businessLogoDark', event)">
                                                                <span class="custom-file-btn border-end d-inline-flex align-items-center gap-1 text-nowrap user-select-none">
                                                                    <span>Choose File</span>
                                                                    <i class="bx bx-help-circle custom-file-help-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="jpg, jpeg, png formats only" onclick="event.stopPropagation()"></i>
                                                                </span>
                                                                <span id="logoDarkFileName" class="text-muted px-3 text-truncate small">No file chosen</span>
                                                            </div>
                                                        </div>
                                                        <img id="previewLogoDark" class="img-thumbnail mt-2 d-none1" style="max-height: 120px;" src="{{ $businessinfo->business_logo_dark }}" />
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="row">
                                                    <label class="col-sm-3 col-form-label text-sm-end" for="businessFavicon">Favicon</label>
                                                    <div class="col-sm-9">
                                                        <div class="custom-file-wrapper position-relative">
                                                            <input class="d-none custom-native-file" type="file" name="businessFavicon" id="businessFavicon" accept=".jpg,.jpeg,.png,.ico"  
                                                                onchange="handleCustomFileChange(this, '#faviconFileName', '#previewFavicon')">
                                                            <div class="form-control custom-file-box d-flex align-items-center p-0 overflow-hidden" onclick="triggerCustomFileInput('businessFavicon', event)">
                                                                <span class="custom-file-btn border-end d-inline-flex align-items-center gap-1 text-nowrap user-select-none">
                                                                    <span>Choose File</span>
                                                                    <i class="bx bx-help-circle custom-file-help-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="jpg, jpeg, png formats only" onclick="event.stopPropagation()"></i>
                                                                </span>
                                                                <span id="faviconFileName" class="text-muted px-3 text-truncate small">No file chosen</span>
                                                            </div>
                                                        </div>
                                                        <img id="previewFavicon" class="img-thumbnail mt-2 d-none1" style="max-height: 120px;" src="{{ $businessinfo->business_favicon }}" />
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6 d-none d-md-block">
                                                <!-- Grid spacer to keep address fields cleanly aligned as a pair -->
                                            </div>

                                            <div class="col-md-6">
                                                <div class="row">
                                                    <label class="col-sm-3 col-form-label text-sm-end"
                                                        for="formtabs-address1">Address Line 1</label>
                                                    <div class="col-sm-9">
                                                        <input type="text" id="formtabs-address1" class="form-control"
                                                            placeholder="Address Line 1" aria-label="Address Line 1" name="businessAddress1" value="{{ $businessinfo->business_address }}"/>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="row">
                                                    <label class="col-sm-3 col-form-label text-sm-end"
                                                        for="formtabs-address2">Address Line 2</label>
                                                    <div class="col-sm-9">
                                                        <input type="text" id="formtabs-address1" class="form-control"
                                                            placeholder="Address Line 2" aria-label="Address Line 2" name="businessAddress2" value="{{ $businessinfo->business_address2 }}"/>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="row">
                                                    <label class="col-sm-3 col-form-label text-sm-end"
                                                        for="formtabs-city">City</label>
                                                    <div class="col-sm-9">
                                                        <input type="text" id="formtabs-city" class="form-control"
                                                            placeholder="City" aria-label="City" name="businessCity" value="{{ $businessinfo->business_city }}"/>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="row">
                                                    <label class="col-sm-3 col-form-label text-sm-end"
                                                        for="formtabs-state">State</label>
                                                    <div class="col-sm-9">
                                                        <input type="text" id="formtabs-state" class="form-control"
                                                            placeholder="State" aria-label="State" name="businessState" value="{{ $businessinfo->business_state }}"/>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="row">
                                                    <label class="col-sm-3 col-form-label text-sm-end"
                                                        for="formtabs-zip">Zip</label>
                                                    <div class="col-sm-9">
                                                        <input type="text" id="formtabs-zip" class="form-control"
                                                            placeholder="Zip" aria-label="Zip" name="businessZip" value="{{ $businessinfo->business_zip }}"/>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="row">
                                                    <label class="col-sm-3 col-form-label text-sm-end"
                                                        for="formtabs-country">Country</label>
                                                    <div class="col-sm-9">
                                                        <input type="text" id="formtabs-country" class="form-control"
                                                            placeholder="Country" aria-label="Country" name="businessCountry" value="{{ $businessinfo->business_country }}"/>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="row">
                                                    <label class="col-sm-3 col-form-label text-sm-end"
                                                        for="formtabs-contact-phone">Phone</label>
                                                    <div class="col-sm-9">
                                                        <input type="tel" id="formtabs-phone" name="businessPhone"
                                                            class="form-control" placeholder="{{ simple_format_phone('') }}" maxlength="20"
                                                            pattern="{{ simple_format_phone_pattern() }}" inputmode1="numeric"
                                                            oninput1="this.value = this.value.replace(/\D/g, '').slice(0, 11);" value="{{ $businessinfo->business_phone }}"/>
                                                            
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="row">
                                                    <label class="col-sm-3 col-form-label text-sm-end"
                                                        for="formtabs-business-email">Email</label>
                                                    <div class="col-sm-9">
                                                        <input type="email" id="formtabs-business-email" class="form-control"
                                                            placeholder="info@example.com" aria-label="info@example.com"
                                                            name="businessEmail" value="{{ $businessinfo->business_email }}"  />

                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="row">
                                                    <label class="col-sm-3 col-form-label text-sm-end"
                                                        for="formtabs-business-website">Website URL</label>
                                                    <div class="col-sm-9">
                                                        <input type="text" id="formtabs-business-website" name="businessWebsite" class="form-control" placeholder="http://example.com" value="{{ $businessinfo->business_website }}" />
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="row">
                                                    <label class="col-sm-3 col-form-label text-sm-end"
                                                        for="formtabs-business-registeraton-number">BIN</label>
                                                    <div class="col-sm-9">
                                                        <input type="text" id="formtabs-business-registeraton-number" class="form-control"
                                                            placeholder="Business Registration Number" aria-label="Business Registration Number" name="businessRegistrationNumber" value="{{ $businessinfo->business_registration_number }}"/>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="row">
                                                    <label class="col-sm-3 col-form-label text-sm-end"
                                                        for="formtabs-business-registeraton-number2">VAT</label>
                                                    <div class="col-sm-9">
                                                        <input type="text" id="formtabs-business-registeraton-number2" class="form-control"
                                                            placeholder="Business Registration Number2" aria-label="Business Registration Number2" name="businessRegistrationNumber2" value="{{ $businessinfo->business_registration_number2 }}"/>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12 mt-4 pt-2">
                                                <div class="d-flex justify-content-end gap-3">
                                                    <button type="submit" class="btn btn-primary d-flex align-items-center gap-2">
                                                        <i class="icon-base ti tabler-device-floppy"></i> Save Business Info
                                                    </button>
                                                </div>
                                            </div>
                                         </div>

                                        </div>

                                        <!-- Brand Info Tab -->
                                        <div class="tab-pane" id="form-tabs-billing-address" role="tabpanel">
                                            <input type="hidden" id="business-logo-source-url" value="{{ $businessinfo->business_logo ?? '' }}">
                                            <input type="hidden" id="constructed_brand_logo" name="constructed_brand_logo" value="">

                                            <div class="row g-6">
                                                <div class="col-md-8 col-lg-8">
                                                    <div class="row g-4">
                                                        
                                                        <!-- Brand App Name -->
                                                        <div class="col-12">
                                                            <div class="card border shadow-none mb-0">
                                                                <div class="card-header border-bottom py-3">
                                                                    <h6 class="mb-0 text-primary fw-semibold d-flex align-items-center gap-2">
                                                                        <i class="icon-base ti tabler-id-badge-2"></i> Application Name
                                                                    </h6>
                                                                </div>
                                                                <div class="card-body pt-4">
                                                                    <div class="row align-items-center">
                                                                        <label class="col-sm-3 col-form-label text-sm-end" for="formtabs-brand-app-name">App Name</label>
                                                                        <div class="col-sm-9">
                                                                            <input type="text" id="formtabs-brand-app-name" class="form-control"
                                                                                placeholder="Brand App Name (e.g. NEOERP)" name="business_brand_app_name" value="{{ $businessinfo->business_brand_app_name }}" required />
                                                                            <div class="form-text">Displayed on the browser page titles, login screen, and sidebar navigation header.</div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Brand Logo On Top (Sidebar) -->
                                                        <div class="col-12">
                                                            <div class="card border shadow-none mb-0">
                                                                <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-center">
                                                                    <h6 class="mb-0 text-primary fw-semibold d-flex align-items-center gap-2">
                                                                        <i class="icon-base ti tabler-layout-sidebar"></i> Sidebar Logo & Brand
                                                                    </h6>
                                                                    <span class="badge bg-label-primary">Navigation Header</span>
                                                                </div>
                                                                <div class="card-body pt-4">
                                                                    <!-- Live Preview Banner -->
                                                                    <div class="p-3 mb-4 rounded bg-dark border border-secondary d-flex align-items-center justify-content-between">
                                                                        <div class="d-flex align-items-center gap-3">
                                                                            <span class="text-white-50 small text-uppercase fw-semibold" style="letter-spacing: 0.5px;">Sidebar Preview:</span>
                                                                            <div id="brand-logo-preview" class="d-inline-flex align-items-center px-3 py-2 rounded bg-black bg-opacity-25 border border-white-10">
                                                                                {!! strip_tags($businessinfo->business_brand_logo_on_top_left ?? 'ERP', '<span><svg><path><img><b><strong><g><circle><rect>') !!}
                                                                            </div>
                                                                        </div>
                                                                        <span class="badge bg-label-info d-none d-sm-inline-block">Real-time Preview</span>
                                                                    </div>

                                                                    <!-- Logo Style Selector -->
                                                                    <div class="row mb-3">
                                                                        <label class="col-sm-3 col-form-label text-sm-end">Logo Style</label>
                                                                        <div class="col-sm-9">
                                                                            <div class="d-flex flex-wrap gap-2" role="group">
                                                                                <input type="radio" class="btn-check" name="brand_logo_mode" id="logoModeDefault" value="default" autocomplete="off" checked>
                                                                                <label class="btn btn-outline-primary btn-sm" for="logoModeDefault">
                                                                                    <i class="icon-base ti tabler-sparkles me-1"></i> Theme Icon
                                                                                </label>

                                                                                <input type="radio" class="btn-check" name="brand_logo_mode" id="logoModeUpload" value="upload" autocomplete="off">
                                                                                <label class="btn btn-outline-primary btn-sm" for="logoModeUpload">
                                                                                    <i class="icon-base ti tabler-upload me-1"></i> Upload Image
                                                                                </label>

                                                                                @if(!empty($businessinfo->business_logo))
                                                                                <input type="radio" class="btn-check" name="brand_logo_mode" id="logoModeBusiness" value="business" autocomplete="off">
                                                                                <label class="btn btn-outline-primary btn-sm" for="logoModeBusiness">
                                                                                    <i class="icon-base ti tabler-building me-1"></i> Use Main Business Logo
                                                                                </label>
                                                                                @endif

                                                                                <input type="radio" class="btn-check" name="brand_logo_mode" id="logoModeCustom" value="custom" autocomplete="off">
                                                                                <label class="btn btn-outline-secondary btn-sm" for="logoModeCustom">
                                                                                    <i class="icon-base ti tabler-code me-1"></i> Custom Code
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <!-- Upload Logo File (Shown in upload mode) -->
                                                                    <div id="logo-upload-container" class="row mb-3 d-none">
                                                                        <label class="col-sm-3 col-form-label text-sm-end" for="brandSidebarLogo">Sidebar Logo File</label>
                                                                        <div class="col-sm-9">
                                                                            <input type="file" class="form-control" id="brandSidebarLogo" name="brandSidebarLogo" accept=".png,.jpg,.jpeg,.svg,.webp" onchange="previewSidebarLogoFile(this)">
                                                                            <div class="form-text">Upload PNG, JPG, SVG, or WebP. Optimal height: 28px - 34px with transparent background.</div>
                                                                        </div>
                                                                    </div>

                                                                    <!-- Show App Name Text Beside Logo -->
                                                                    <div class="row mb-3 align-items-center">
                                                                        <label class="col-sm-3 col-form-label text-sm-end">Text Beside Logo</label>
                                                                        <div class="col-sm-9">
                                                                            <div class="form-check form-switch">
                                                                                <input class="form-check-input" type="checkbox" id="brand_logo_show_text" name="brand_logo_show_text" value="1" checked>
                                                                                <label class="form-check-label" for="brand_logo_show_text">Show App Name text beside the logo icon</label>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <!-- Advanced Raw SVG Textarea (Hidden unless custom mode is picked) -->
                                                                    <div id="logo-custom-container" class="row mb-3 d-none">
                                                                        <label class="col-sm-3 col-form-label text-sm-end" for="business_brand_logo_on_top_left">SVG / HTML Code</label>
                                                                        <div class="col-sm-9">
                                                                            <textarea name="business_brand_logo_on_top_left" id="business_brand_logo_on_top_left" rows="5" class="form-control font-monospace small" placeholder="<svg ...>...</svg>">{{ $businessinfo->business_brand_logo_on_top_left }}</textarea>
                                                                            <div class="form-text">Custom HTML or SVG vector markup for advanced developers.</div>
                                                                        </div>
                                                                    </div>

                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Save Button in Brand Info Tab -->
                                                        <div class="col-12 mt-2">
                                                            <div class="d-flex align-items-center gap-3">
                                                                <button type="submit" class="btn btn-primary d-flex align-items-center gap-2">
                                                                    <i class="icon-base ti tabler-device-floppy"></i> Save Brand Info
                                                                </button>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>

                                                <!-- Right Side: Brand Media Assets Card -->
                                                <div class="col-md-4 col-lg-4">
                                                    <div class="card border shadow-none">
                                                        <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-center">
                                                            <h6 class="mb-0 text-primary fw-semibold d-flex align-items-center gap-2">
                                                                <i class="icon-base ti tabler-photo"></i> Brand Assets
                                                            </h6>
                                                            <button type="button" class="btn btn-xs btn-primary waves-effect waves-light" data-bs-target="#addbrandfile" data-bs-toggle="modal">
                                                                <i class="icon-base ti tabler-plus icon-xs me-1"></i> Upload
                                                            </button>
                                                        </div>
                                                        <div class="card-body pt-3">
                                                            <p class="text-muted small mb-3">Click <strong>Use as Logo</strong> on any uploaded image to set it as your sidebar logo, or copy its URL.</p>
                                                            <div id="brandfiles" class="d-flex flex-wrap gap-2">
                                                                @forelse ($branlogoinfo as $item)
                                                                    <div class="border rounded p-2 text-center bg-label-secondary brand-asset-card" data-url="{{ $item }}" style="width: 95px;">
                                                                        <img src="{{ $item }}" style="max-width: 75px; max-height: 55px; object-fit: contain;" class="rounded mb-2 d-block mx-auto" />
                                                                        <button type="button" class="btn btn-xs btn-primary w-100 py-0 mb-1" style="font-size: 10px;" onclick="selectAssetAsSidebarLogo('{{ $item }}')">Use Logo</button>
                                                                        <a href="javascript:void(0);" class="small text-muted copy-asset-url d-block" data-url="{{ $item }}" style="font-size: 10px;">Copy URL</a>
                                                                    </div>
                                                                @empty
                                                                    <div class="text-center py-4 w-100 text-muted">
                                                                        <i class="icon-base ti tabler-photo-off fs-2 mb-2 d-block opacity-50"></i>
                                                                        <span class="small">No brand files uploaded yet.</span>
                                                                    </div>
                                                                @endforelse
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- / Content -->


<!-- Modal -->
<div class="modal fade" id="addbrandfile" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-simple modal-edit-user">
        <div class="modal-content">
            <div class="modal-body">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="text-center mb-6">
                    <h4 class="mb-2">Brand File</h4>
                </div>
                <form id="addbrandfileform" class="row g-6" onsubmit="return true" action="{{ route('businessinfo.uploadbrandfiles', $businessinfo->id) }}" enctype="multipart/form-data" method="POST" >
                    @csrf
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="brandFile"></label>
                        <input class="form-control" type="file" name="brandFile" id="brandFile"  accept=".jpg,.jpeg,.png" onchange="previewFile(this, '#previewBrandFile')">
                        <img id="previewBrandFile" class="img-thumbnail mt-2 d-none1" style="max-height: 120px;" src="" />
                    </div>

                    <div class="col-12 text-center">
                        <button type="submit" class="btn btn-primary me-3">Submit</button>
                        <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal"
                            aria-label="Close">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    </div>

@endsection