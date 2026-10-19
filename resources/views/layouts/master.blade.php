<!doctype html>

<html lang="en" class="layout-navbar-fixed layout-menu-fixed layout-compact" dir="ltr" data-skin="default"
    data-template="vertical-menu-template" data-bs-theme="light">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') | {{ $appbrand->business_brand_app_name??'ERP' }}</title>

    <meta name="description" content="" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ $appbrand->business_favicon ?? asset('assets/img/favicon/favicon.ico') }}" />
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/img/favicon/favicon-32x32.png') }}" />
    <link rel="apple-touch-icon" sizes="192x192" href="{{ asset('assets/img/favicon/favicon-192.png') }}" />

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
                } else {
                    document.documentElement.setAttribute('data-bs-theme', 'light');
                    document.documentElement.style.backgroundColor = '#ffffff';
                    document.documentElement.style.colorScheme = 'light';
                }
            } catch (e) {}
        })();
    </script>

    <!-- PWA Manifest & Mobile Configuration (FR-022) -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#ffffff" id="meta-theme-color">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="NeoField">

    <!-- Fonts: Standard Roboto & Roboto Mono for High-Precision Enterprise UI -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500&family=Roboto+Mono:wght@400;500;600&display=swap"
        rel="stylesheet" />

    <!-- Icons -->
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

    @section('styles')
        <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/iconify-icons.css') }}" />
        <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/fontawesome.css') }}" />

        <!-- Core CSS -->
        <!-- build:css assets/vendor/css/theme.css  -->
        <link rel="stylesheet" href="{{ asset('assets/vendor/libs/node-waves/node-waves.css') }}" />
        <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}?v={{ file_exists(public_path('assets/vendor/css/core.css')) ? filemtime(public_path('assets/vendor/css/core.css')) : time() }}" />
        <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />
        <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}?v={{ file_exists(public_path('assets/css/custom.css')) ? filemtime(public_path('assets/css/custom.css')) : time() }}" />

        <!-- Vendors CSS -->
        <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
        <!-- endbuild -->
        <link rel="stylesheet" href="{{ asset('assets/vendor/libs/swiper/swiper.css') }}" />
        <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">


    @show

    <!-- Modern DataTables & UI Polish Overrides (Ensures global precedence over all page-level vendor stylesheets) -->
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}?v={{ file_exists(public_path('assets/css/custom.css')) ? filemtime(public_path('assets/css/custom.css')) : time() }}" />
    <!-- Helpers -->
    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->

    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="{{ asset('assets/js/config.js') }}"></script>
</head>

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <!-- Menu -->

            @include('layouts.sidemenu')

            <div class="menu-mobile-toggler d-xl-none rounded-1">
                <a href="javascript:void(0);"
                    class="layout-menu-toggle menu-link text-large text-bg-secondary p-2 rounded-1">
                    <i class="ti tabler-menu icon-base"></i>
                    <i class="ti tabler-chevron-right icon-base"></i>
                </a>
            </div>
            <!-- / Menu -->

            <!-- Layout container -->
            <div class="layout-page">
                <!-- Navbar -->
                <nav class="layout-navbar container-xxl navbar-detached navbar navbar-expand-xl align-items-center bg-navbar-theme"
                    id="layout-navbar">
                    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
                        <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
                            <i class="icon-base ti tabler-menu-2 icon-md"></i>
                        </a>
                    </div>
                    
                    <div class="navbar-nav-right d-flex align-items-center justify-content-end" id="navbar-collapse">
                        <!-- Search -->
                        <div class="navbar-nav align-items-center">
                            <div class="nav-item navbar-search-wrapper px-md-0 px-2 mb-0 d-flex align-items-center gap-2">
                                <img src="{{ asset('assets/img/branding/neoteric-icon.png') }}" alt="Neoteric Digital" class="d-none d-sm-inline-block" style="width: 22px; height: 22px; object-fit: contain;" />
                                <span class="fw-bold text-heading fs-6" style="letter-spacing: -0.01em;">{{ $appbrand->business_name ?? 'Neoteric Digital' }}</span>
                                <span class="badge bg-label-primary font-monospace ms-1 d-none d-md-inline-block" style="font-size: 0.65rem; padding: 0.25rem 0.5rem;">NEO ERP</span>
                            </div>
                        </div>

                        <!-- /Search -->

                        <ul class="navbar-nav flex-row align-items-center ms-md-auto">
                            <!-- Notification -->
                            @php
                                $currentUser = auth()->user();
                                $unreadCount = $currentUser ? $currentUser->unreadNotifications()->count() : 0;
                                $recentNotifications = $currentUser ? $currentUser->notifications()->latest()->take(5)->get() : collect();
                            @endphp
                            <li class="nav-item dropdown-notifications navbar-dropdown dropdown">
                                <a class="nav-link dropdown-toggle hide-arrow btn btn-icon btn-text-secondary rounded-pill"
                                    href="javascript:void(0);" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                                    aria-expanded="false">
                                    <span class="position-relative">
                                        <i class="icon-base ti tabler-bell icon-22px text-heading"></i>
                                        @if($unreadCount > 0)
                                            <span class="badge rounded-pill bg-danger badge-dot badge-notifications border"></span>
                                        @endif
                                    </span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end p-0" style="min-width: 320px;">
                                    <li class="dropdown-menu-header border-bottom">
                                        <div class="dropdown-header d-flex align-items-center py-3">
                                            <h6 class="mb-0 me-auto">Notifications</h6>
                                            <div class="d-flex align-items-center h6 mb-0">
                                                <span class="badge bg-label-primary me-2">{{ $unreadCount }} New</span>
                                                <a href="{{ route('notifications.mark-all-read') }}"
                                                    onclick="event.preventDefault(); fetch(this.href, {method: 'POST', headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json'}}).then(() => window.location.reload());"
                                                    class="dropdown-notifications-all p-2 btn btn-icon"
                                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                                    title="Mark all as read"><i
                                                        class="icon-base ti tabler-mail-opened text-heading"></i></a>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="dropdown-notifications-list scrollable-container">
                                        <ul class="list-group list-group-flush">
                                            @forelse($recentNotifications as $notif)
                                                <li class="list-group-item list-group-item-action dropdown-notifications-item {{ $notif->read_at ? 'opacity-75' : '' }}">
                                                    <div class="d-flex">
                                                        <div class="flex-shrink-0 me-3">
                                                            <div class="avatar">
                                                                <span class="avatar-initial rounded-circle bg-label-primary">
                                                                    <i class="icon-base ti tabler-bell"></i>
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <h6 class="small mb-1">{{ $notif->data['title'] ?? 'Notification' }}</h6>
                                                            <small class="mb-1 d-block text-body">{{ Str::limit($notif->data['message'] ?? '', 50) }}</small>
                                                            <small class="text-muted">{{ $notif->created_at->diffForHumans() }}</small>
                                                        </div>
                                                        @if(!empty($notif->data['action_url']))
                                                            <div class="flex-shrink-0 dropdown-notifications-actions">
                                                                <a href="{{ $notif->data['action_url'] }}" class="dropdown-notifications-read"><span class="badge badge-dot"></span></a>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </li>
                                            @empty
                                                <li class="list-group-item text-center py-3 text-muted">
                                                    <small>No unread notifications</small>
                                                </li>
                                            @endforelse
                                        </ul>
                                    </li>
                                    <li class="border-top">
                                        <div class="d-grid p-3">
                                            <a class="btn btn-primary btn-sm d-flex justify-content-center" href="{{ route('notifications.index') }}">
                                                <small class="align-middle">View all notifications</small>
                                            </a>
                                        </div>
                                    </li>
                                </ul>
                            </li>
                            <!--/ Notification -->
                        </ul>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </div>
                </nav>
                <!-- / Navbar -->

                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <!-- Content -->
                    @yield('content')
                    <!-- / Content -->

                    <!-- Footer -->
                    <footer class="content-footer footer bg-footer-theme">
                        <div class="container-xxl">
                            <div
                                class="footer-container d-flex align-items-center justify-content-between py-4 flex-md-row flex-column">
                                <div class="text-body">
                                    {!! strip_tags(isset($appbrand) && $appbrand->business_brand_credit_on_footer_enable==1 ? ($appbrand->business_brand_credit_on_footer ?: '&copy; ' . date('Y') . ', created by <a href="https://neotericdigital.co.uk/" target="_blank" class="footer-link">Neoteric Digital</a>') : '', '<a><span><strong><em><b><i>') !!}
                                </div>

                            </div>
                        </div>
                    </footer>
                    <!-- / Footer -->

                    <div class="content-backdrop fade"></div>
                </div>
                <!-- Content wrapper -->
            </div>
            <!-- / Layout page -->
        </div>



        <!-- Drag Target Area To SlideIn Menu On Small Screens -->
        <div class="drag-target"></div>
    </div>
    <!-- / Layout wrapper -->

    @section('scripts')
        <!-- Core JS -->
        <!-- build:js assets/vendor/js/theme.js -->
        <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
        <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
        <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
        <script src="{{ asset('assets/vendor/libs/node-waves/node-waves.js') }}"></script>
        <script src="{{ asset('assets/vendor/libs/@algolia/autocomplete-js.js') }}"></script>
        <script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
        <script src="{{ asset('assets/vendor/libs/hammer/hammer.js') }}"></script>
        <script src="{{ asset('assets/vendor/libs/i18n/i18n.js') }}"></script>
        <script src="{{ asset('assets/vendor/js/menu.js') }}"></script>
        <!-- endbuild -->

        <!-- Vendors JS -->
        <script src="{{ asset('assets/vendor/libs/swiper/swiper.js') }}"></script>

        <!-- Main JS -->
        <script src="{{ asset('assets/js/main.js') }}?v={{ file_exists(public_path('assets/js/main.js')) ? filemtime(public_path('assets/js/main.js')) : time() }}"></script>
    @show

    <!-- PWA Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js').then(function(reg) {
                    console.log('NeoERP PWA ServiceWorker active:', reg.scope);
                }).catch(function(err) {
                    console.log('PWA ServiceWorker error:', err);
                });
            });
        }
    </script>
    <!-- Zero-Flash Instant Tab Switching Enforcement -->
    <script>
        (function() {
            // 1. Force Bootstrap 5 Tab switcher to run 100% synchronously without transition delay
            function patchBootstrapTab() {
                if (window.bootstrap && window.bootstrap.Tab) {
                    window.bootstrap.Tab.prototype._queueCallback = function(callback) {
                        if (typeof callback === 'function') {
                            callback();
                        }
                    };
                }
            }
            patchBootstrapTab();

            // 2. Strip all .fade classes so panes switch with zero opacity latency
            function cleanTabFades() {
                patchBootstrapTab();
                document.querySelectorAll('.tab-pane.fade').forEach(function(pane) {
                    pane.classList.remove('fade');
                });
            }

            // 3. Pre-emptively sanitize tabs on click
            document.addEventListener('click', function(e) {
                var tabBtn = e.target.closest('[data-bs-toggle="tab"], [data-bs-toggle="pill"]');
                if (tabBtn) {
                    patchBootstrapTab();
                    var targetSelector = tabBtn.getAttribute('data-bs-target') || tabBtn.getAttribute('href');
                    if (targetSelector && targetSelector.startsWith('#')) {
                        var targetPane = document.querySelector(targetSelector);
                        if (targetPane) {
                            targetPane.classList.remove('fade');
                            if (targetPane.parentElement) {
                                targetPane.parentElement.querySelectorAll('.tab-pane').forEach(function(p) {
                                    p.classList.remove('fade');
                                });
                            }
                        }
                    }
                }
            }, true);

            // 4. Instantaneous activation on show.bs.tab to prevent visual blank frame
            document.addEventListener('show.bs.tab', function(e) {
                if (e.target) {
                    var targetSel = e.target.getAttribute('data-bs-target') || e.target.getAttribute('href');
                    if (targetSel && targetSel.startsWith('#')) {
                        var targetEl = document.querySelector(targetSel);
                        if (targetEl) {
                            targetEl.classList.remove('fade');
                            targetEl.classList.add('show', 'active');
                            targetEl.style.opacity = '1';
                            targetEl.style.display = 'block';
                            targetEl.style.transform = 'none';
                            targetEl.style.transition = 'none';
                        }
                    }
                }
            });

            // 5. Synchronize theme-color meta and html canvas background on theme toggle
            document.querySelectorAll('[data-bs-theme-value]').forEach(function(toggle) {
                toggle.addEventListener('click', function() {
                    setTimeout(function() {
                        var isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
                        var bg = isDark ? '#0b0f19' : '#ffffff';
                        document.documentElement.style.backgroundColor = bg;
                        document.documentElement.style.colorScheme = isDark ? 'dark' : 'light';
                        var meta = document.getElementById('meta-theme-color');
                        if (meta) meta.setAttribute('content', bg);
                    }, 10);
                });
            });

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', cleanTabFades);
            } else {
                cleanTabFades();
            }
            window.addEventListener('load', cleanTabFades);
        })();
    </script>

    <!-- Interactive Per-Page Selector & DataTables Page Length Defaults -->
    <script>
        document.addEventListener('change', function(e) {
            if (e.target && e.target.classList.contains('per-page-selector')) {
                var perPage = e.target.value;
                var url = new URL(window.location.href);
                url.searchParams.set('per_page', perPage);
                url.searchParams.set('page', '1');
                window.location.href = url.toString();
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            if (window.jQuery && window.jQuery.fn && window.jQuery.fn.dataTable) {
                window.jQuery.extend(true, window.jQuery.fn.dataTable.defaults, {
                    lengthMenu: [5, 10, 25, 50, 100],
                    pageLength: 10
                });
            }
        });

        // Universal Horizontal Table Slider & Synchronized Top Scrollbar
        function initUniversalTableSliders() {
            document.querySelectorAll('.table-responsive').forEach(function(container) {
                if (container.id === 'portfolioTableContainer') return;

                var table = container.querySelector('table');
                if (!table) return;

                var card = container.closest('.card') || container.parentElement;
                var controls = card ? card.querySelector('.table-slider-controls') : null;
                if (!controls && container.previousElementSibling) {
                    controls = container.previousElementSibling.querySelector('.table-slider-controls');
                }
                var btnLeft = controls ? controls.querySelector('.btn-slide-table-left') : null;
                var btnRight = controls ? controls.querySelector('.btn-slide-table-right') : null;

                // Ensure top synchronized scrollbar exists
                var topScroll = container.previousElementSibling;
                var isTopScroll = topScroll && topScroll.classList.contains('universal-top-scrollbar');
                if (!isTopScroll) {
                    topScroll = document.createElement('div');
                    topScroll.className = 'universal-top-scrollbar';
                    topScroll.style.cssText = 'overflow-x: auto; overflow-y: hidden; height: 10px; display: none; background: rgba(128,128,128,0.04); border-bottom: 1px solid rgba(128,128,128,0.1);';
                    var dummy = document.createElement('div');
                    dummy.className = 'universal-top-scrollbar-dummy';
                    dummy.style.height = '10px';
                    topScroll.appendChild(dummy);
                    container.parentNode.insertBefore(topScroll, container);
                }
                var dummy = topScroll.querySelector('.universal-top-scrollbar-dummy');

                function update() {
                    var scrollWidth = table.scrollWidth;
                    var clientWidth = container.clientWidth;
                    var isOverflowing = scrollWidth > clientWidth + 5;

                    if (isOverflowing) {
                        topScroll.style.display = 'block';
                        if (dummy) dummy.style.width = scrollWidth + 'px';
                        if (controls) {
                            controls.classList.remove('d-none');
                            controls.style.setProperty('display', 'flex', 'important');
                        }

                        var scrollLeft = container.scrollLeft;
                        var maxScroll = scrollWidth - clientWidth;
                        if (btnLeft) btnLeft.disabled = scrollLeft <= 2;
                        if (btnRight) btnRight.disabled = scrollLeft >= maxScroll - 5;
                    } else {
                        topScroll.style.display = 'none';
                        if (controls) {
                            controls.classList.add('d-none');
                            controls.style.setProperty('display', 'none', 'important');
                        }
                    }
                }

                if (!container.dataset.sliderInitialized) {
                    container.dataset.sliderInitialized = 'true';

                    topScroll.addEventListener('scroll', function() {
                        container.scrollLeft = topScroll.scrollLeft;
                        update();
                    });

                    container.addEventListener('scroll', function() {
                        topScroll.scrollLeft = container.scrollLeft;
                        update();
                    });

                    if (btnLeft) {
                        btnLeft.addEventListener('click', function(e) {
                            e.preventDefault();
                            container.scrollBy({ left: -320, behavior: 'smooth' });
                        });
                    }

                    if (btnRight) {
                        btnRight.addEventListener('click', function(e) {
                            e.preventDefault();
                            container.scrollBy({ left: 320, behavior: 'smooth' });
                        });
                    }

                    if (window.ResizeObserver) {
                        var ro = new ResizeObserver(function() {
                            update();
                        });
                        ro.observe(container);
                        ro.observe(table);
                    }
                    window.addEventListener('resize', update);
                }

                update();
            });
        }

        function updateSidebarThemeIcon(theme) {
            var icon = document.getElementById('sidebar-theme-icon');
            var btn = document.getElementById('sidebar-theme-toggle');
            if (!icon) return;
            if (theme === 'dark') {
                icon.className = 'bx bx-sun';
                if (btn) btn.setAttribute('title', 'Switch to Light Mode');
            } else {
                icon.className = 'bx bx-moon';
                if (btn) btn.setAttribute('title', 'Switch to Dark Mode');
            }
        }

        window.toggleAppTheme = function() {
            var current = document.documentElement.getAttribute('data-bs-theme') || 'light';
            var next = current === 'dark' ? 'light' : 'dark';
            
            document.documentElement.setAttribute('data-bs-theme', next);
            try {
                localStorage.setItem('templateCustomizer-vertical-menu-template--Theme', next);
            } catch(e) {}
            
            var bg = next === 'dark' ? '#0b0f19' : '#ffffff';
            document.documentElement.style.backgroundColor = bg;
            document.documentElement.style.colorScheme = next;
            var meta = document.getElementById('meta-theme-color');
            if (meta) meta.setAttribute('content', bg);
            
            updateSidebarThemeIcon(next);
        };

        // Global Bootstrap Tooltips Initialization
        function initBootstrapTooltips() {
            if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.forEach(function (tooltipTriggerEl) {
                    if (!tooltipTriggerEl._tooltip) {
                        tooltipTriggerEl._tooltip = new bootstrap.Tooltip(tooltipTriggerEl);
                    }
                });
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            var currentTheme = document.documentElement.getAttribute('data-bs-theme') || 'light';
            updateSidebarThemeIcon(currentTheme);
            initBootstrapTooltips();
            setTimeout(initUniversalTableSliders, 100);
            setTimeout(initUniversalTableSliders, 400);
        });
        window.addEventListener('load', function() {
            var currentTheme = document.documentElement.getAttribute('data-bs-theme') || 'light';
            updateSidebarThemeIcon(currentTheme);
            initBootstrapTooltips();
            setTimeout(initUniversalTableSliders, 50);
            setTimeout(initUniversalTableSliders, 300);
        });
    </script>
</body>

</html>
