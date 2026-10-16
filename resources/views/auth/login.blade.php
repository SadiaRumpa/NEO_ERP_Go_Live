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
            <h4 class="mb-1 fw-bold text-heading">{{ $appbrand->business_brand_app_name ?? 'NEO ERP' }}</h4>
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

        <!-- Login Form -->
        <form id="formAuthentication" class="mb-0 text-start" action="{{ route('login') }}" method="POST">
            @csrf

            <!-- Email -->
            <div class="mb-4">
                <label for="email" class="form-label small fw-semibold text-heading">Email Address</label>
                <div class="input-group input-group-merge auth-input-group">
                    <span class="input-group-text"><i class="icon-base ti tabler-mail text-muted"></i></span>
                    <input
                        type="email"
                        class="form-control"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="name@company.com"
                        required
                        autofocus />
                </div>
            </div>

            <!-- Password -->
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label small fw-semibold text-heading mb-0" for="password">Password</label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="small text-primary text-decoration-none">
                            Forgot Password?
                        </a>
                    @endif
                </div>
                <div class="input-group input-group-merge auth-input-group form-password-toggle">
                    <span class="input-group-text"><i class="icon-base ti tabler-lock text-muted"></i></span>
                    <input
                        type="password"
                        id="password"
                        class="form-control"
                        name="password"
                        placeholder="••••••••••••"
                        aria-describedby="password"
                        required 
                        autocomplete="current-password" />
                    <span class="input-group-text cursor-pointer"><i class="icon-base ti tabler-eye-off"></i></span>
                </div>
            </div>

            <!-- Remember Me -->
            <div class="mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }} />
                    <label class="form-check-label small" for="remember">
                        Keep me signed in on this device
                    </label>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn auth-submit-btn text-white w-100 d-flex align-items-center justify-content-center gap-2 mb-4">
                <span>Sign In to Dashboard</span>
                <i class="icon-base ti tabler-arrow-right"></i>
            </button>
        </form>

        <!-- One-Click Showcase Demo Login Helper -->
        <div class="auth-demo-badge d-flex align-items-center justify-content-between mt-2">
            <div class="overflow-hidden me-2 text-start">
                <div class="fw-semibold text-primary d-flex align-items-center gap-1 small">
                    <i class="icon-base ti tabler-sparkles"></i>
                    <span>Showcase Evaluator Login</span>
                </div>
                <small class="text-muted text-truncate d-block" style="font-size: 0.75rem;">admin@neoerp.local</small>
            </div>
            <button type="button" 
                    class="auth-demo-btn flex-shrink-0" 
                    onclick="fillDemoAccount('admin@neoerp.local', 'NeoErp!Admin2026#Secure')"
                    title="Auto-fill Super Admin credentials">
                Auto-Fill
            </button>
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
    function fillDemoAccount(email, password) {
        var emailInput = document.getElementById('email');
        var passwordInput = document.getElementById('password');
        if (emailInput && passwordInput) {
            emailInput.value = email;
            passwordInput.value = password;
            emailInput.focus();
        }
    }
</script>
@endsection