@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="auth-centered-wrapper">
    <div class="auth-card p-sm-8 p-6">
        <!-- Logo & Brand Header -->
        <div class="text-center mb-6">
            @if(isset($appbrand) && $appbrand->business_logo)
                <img src="{{ asset($appbrand->business_logo) }}" 
                     alt="{{ $appbrand->business_brand_app_name ?? 'NEO ERP' }}" 
                     class="img-fluid mb-3" 
                     style="max-height: 52px; max-width: 200px; object-fit: contain;" />
            @else
                <div class="auth-logo-badge mx-auto">
                    <i class="icon-base ti tabler-building-skyscraper"></i>
                </div>
            @endif
            <h4 class="mb-1 fw-bold text-heading" style="letter-spacing: -0.01em;">{{ $appbrand->business_brand_app_name ?? 'NEO ERP' }}</h4>
            <p class="text-muted small mb-0">Commercial Contracting & Costing Portal</p>
        </div>

        @if (isset($errors) && $errors->any())
        <div class="alert alert-danger py-2 px-3 mb-4 rounded-3 text-start small">
            @foreach ($errors->all() as $error)
                <div class="d-flex align-items-center gap-1">
                    <i class="icon-base ti tabler-alert-circle flex-shrink-0"></i>
                    <span>{{ $error }}</span>
                </div>
            @endforeach
        </div>
        @endif

        <!-- Login Form (Bypasses third-party page-auth validator that intercepted and blocked submit) -->
        <form id="loginForm" class="mb-0 text-start" action="{{ route('login') }}" method="POST">
            @csrf

            <!-- Email (No left icon) -->
            <div class="mb-4">
                <label for="email" class="form-label">Email Address</label>
                <div class="auth-input-wrapper">
                    <input
                        type="email"
                        class="form-control auth-input"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="name@company.com"
                        required
                        autofocus />
                </div>
            </div>

            <!-- Password (No left icon; show password icon placed inside input on the right with 50% opacity) -->
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label mb-0" for="password">Password</label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="small auth-link">
                            Forgot Password?
                        </a>
                    @endif
                </div>
                <div class="auth-input-wrapper">
                    <input
                        type="password"
                        id="password"
                        class="form-control auth-input auth-input-password"
                        name="password"
                        placeholder="••••••••••••"
                        aria-describedby="password"
                        required 
                        autocomplete="current-password" />
                    <button type="button" 
                            id="passwordToggleBtn" 
                            class="auth-password-toggle"
                            onclick="togglePasswordVisibility()"
                            title="Show password"
                            aria-label="Toggle password visibility">
                        <i id="passwordToggleIcon" class="icon-base ti tabler-eye-off"></i>
                    </button>
                </div>
            </div>

            <!-- Remember Me -->
            <div class="mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }} />
                    <label class="form-check-label small text-muted" for="remember">
                        Keep me signed in on this device
                    </label>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" id="loginSubmitBtn" class="btn auth-submit-btn w-100 d-flex align-items-center justify-content-center gap-2 mb-4">
                <span id="loginSubmitText">Sign In to Dashboard</span>
                <i id="loginSubmitIcon" class="icon-base ti tabler-arrow-right"></i>
            </button>
        </form>

        <!-- One-Click Showcase Demo Login Helpers -->
        <div class="auth-demo-box mt-3 p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="small fw-semibold text-heading"><i class="icon-base ti tabler-users me-1 text-primary"></i>Showcase Role Quick-Fill</span>
                <span class="badge bg-label-primary font-monospace" style="font-size: 0.65rem;">7 Roles</span>
            </div>
            <select id="roleSelector" class="form-select form-select-sm mb-2" onchange="onRoleSelectChange(this)">
                <option value="" disabled selected>-- Select Role to Auto-Fill --</option>
                <option value="admin@neoerp.local|NeoErp!Admin2026#Secure">👑 Super Admin (Full Control)</option>
                <option value="marcus.vance@neoerp.local|NeoErp!Staff2026#">📊 Commercial Director (WIP &amp; Contracts)</option>
                <option value="priya.patel@neoerp.local|NeoErp!Staff2026#">📐 Lead Quantity Surveyor (HMRC CIS &amp; Valuations)</option>
                <option value="sarah.engineer@neoerp.local|NeoErp!Staff2026#">🏗️ Project Engineer (Jobs &amp; Field Ops)</option>
                <option value="michael.chang@neoerp.local|NeoErp!Staff2026#">📦 Procurement Officer (POs &amp; 3-Way Match)</option>
                <option value="james.oconnor@neoerp.local|NeoErp!Staff2026#">👷 Senior Site Manager (Timesheets &amp; Labor)</option>
                <option value="chloe.bennett@neoerp.local|NeoErp!Staff2026#">🛡️ H&amp;S Compliance Auditor (Audits &amp; RAMS)</option>
            </select>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-outline-primary flex-fill py-1" onclick="fillDemoAccount('admin@neoerp.local', 'NeoErp!Admin2026#Secure')">
                    <i class="icon-base ti tabler-shield-check me-1"></i>Super Admin
                </button>
                <button type="button" class="btn btn-sm btn-outline-info flex-fill py-1" onclick="fillDemoAccount('marcus.vance@neoerp.local', 'NeoErp!Staff2026#')">
                    <i class="icon-base ti tabler-chart-pie me-1"></i>Commercial
                </button>
            </div>
        </div>

        <!-- Security Disclaimer -->
        <div class="text-center mt-5 pt-3 border-top">
            <small class="text-muted d-inline-flex align-items-center gap-1" style="font-size: 0.75rem;">
                <i class="icon-base ti tabler-shield-check text-success"></i>
                <span>256-bit TLS Encrypted Enterprise Portal</span>
            </small>
        </div>
    </div>
</div>

<script>
    function togglePasswordVisibility() {
        var passwordInput = document.getElementById('password');
        var toggleIcon = document.getElementById('passwordToggleIcon');
        var toggleBtn = document.getElementById('passwordToggleBtn');
        if (!passwordInput || !toggleIcon) return;

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.className = 'icon-base ti tabler-eye';
            if (toggleBtn) toggleBtn.setAttribute('title', 'Hide password');
        } else {
            passwordInput.type = 'password';
            toggleIcon.className = 'icon-base ti tabler-eye-off';
            if (toggleBtn) toggleBtn.setAttribute('title', 'Show password');
        }
    }

    function fillDemoAccount(email, password) {
        var emailInput = document.getElementById('email');
        var passwordInput = document.getElementById('password');
        if (emailInput && passwordInput) {
            emailInput.value = email;
            passwordInput.value = password;
            emailInput.dispatchEvent(new Event('input', { bubbles: true }));
            emailInput.dispatchEvent(new Event('change', { bubbles: true }));
            passwordInput.dispatchEvent(new Event('input', { bubbles: true }));
            passwordInput.dispatchEvent(new Event('change', { bubbles: true }));
            passwordInput.focus();
        }
    }

    function onRoleSelectChange(sel) {
        if (!sel || !sel.value) return;
        var parts = sel.value.split('|');
        if (parts.length === 2) {
            fillDemoAccount(parts[0], parts[1]);
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        var form = document.getElementById('loginForm');
        var btn = document.getElementById('loginSubmitBtn');
        var text = document.getElementById('loginSubmitText');
        var icon = document.getElementById('loginSubmitIcon');

        if (form && btn) {
            form.addEventListener('submit', function () {
                btn.disabled = true;
                if (text) text.textContent = 'Signing in...';
                if (icon) {
                    icon.className = 'spinner-border spinner-border-sm';
                    icon.setAttribute('role', 'status');
                }
            });
        }
    });
</script>
@endsection