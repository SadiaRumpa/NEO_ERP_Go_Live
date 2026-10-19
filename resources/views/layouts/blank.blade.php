<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="layout-wide" dir="ltr" data-skin="default"
    data-template="horizontal-menu-template-no-customizer" data-bs-theme="light">

<head>
    <meta charset="utf-8" />
    <!-- Prevent White Flash in Dark Mode before CSS or JS loads -->
    <script>
        (function() {
            try {
                var stored = localStorage.getItem('templateCustomizer-vertical-menu-template--Theme') || 'light';
                var isDark = stored === 'dark' || (stored === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
                if (isDark) {
                    document.documentElement.setAttribute('data-bs-theme', 'dark');
                    document.documentElement.style.backgroundColor = '#0b0f19';
                    document.documentElement.style.colorScheme = 'dark';
                }
            } catch (e) {}
        })();
    </script>
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <meta name="description" content="" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ $appbrand->business_favicon ?? asset('assets/img/favicon/favicon.ico') }}" />
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/img/favicon/favicon-32x32.png') }}" />
    <link rel="apple-touch-icon" sizes="192x192" href="{{ asset('assets/img/favicon/favicon-192.png') }}" />

    <!-- Fonts: Standard Roboto -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500&family=Roboto+Mono:wght@400;500;600&display=swap"
        rel="stylesheet" />
    @section('styles')
        <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/iconify-icons.css') }}" />

        <!-- Core CSS -->
        <!-- build:css assets/vendor/css/theme.css  -->
        <link rel="stylesheet" href="{{ asset('assets/vendor/libs/node-waves/node-waves.css') }}" />
        <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}?v={{ file_exists(public_path('assets/vendor/css/core.css')) ? filemtime(public_path('assets/vendor/css/core.css')) : time() }}" />
        <link rel="stylesheet" href="{{asset('assets/css/demo.css')}}" />
        <link rel="stylesheet" href="{{asset('assets/css/custom.css')}}?v={{ file_exists(public_path('assets/css/custom.css')) ? filemtime(public_path('assets/css/custom.css')) : time() }}" />
        <!-- Vendors CSS -->

        <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
        <link rel="stylesheet" href="{{ asset('assets/vendor/libs/swiper/swiper.css') }}" />
        <link rel="stylesheet" href="{{ asset('vendor/toastr/build/toastr.min.css') }}" />
        <!-- endbuild -->

        <!-- Page CSS -->
    @show
    <!-- Helpers -->
    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->

    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->

    <script src="{{ asset('assets/js/config.js') }}"></script>
</head>

<body>
    <!-- Content -->
    @yield('content')

    <!-- / Content -->

    <!-- Core JS -->
    <!-- build:js assets/vendor/js/theme.js -->
    @section('scripts')
        <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
        <script src="{{ asset('vendor/toastr/toastr.js') }}"></script>
        <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
        <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
        <script src="{{ asset('assets/vendor/libs/node-waves/node-waves.js') }}"></script>

        <script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>

        <script src="{{ asset('assets/vendor/libs/hammer/hammer.js') }}"></script>

        <script src="{{ asset('assets/vendor/libs/i18n/i18n.js') }}"></script>

        <script src="{{ asset('assets/vendor/js/menu.js') }}"></script>

        <!-- endbuild -->

        <!-- Vendors JS -->
        <script src="{{ asset('assets/vendor/libs/swiper/swiper.js') }}"></script>
    @show
    <!-- Page JS -->
</body>

</html>
