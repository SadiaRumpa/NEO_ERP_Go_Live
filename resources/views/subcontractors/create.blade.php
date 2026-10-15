@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="mb-4">
        <h4 class="fw-bold py-1 mb-1"><span class="text-muted fw-light">Subcontractors /</span> Register Subcontractor</h4>
        <p class="text-muted mb-0">Record trade subcontractor profile, HMRC UTR number, and CIS deduction status.</p>
    </div>

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible" role="alert">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('subcontractors.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label required">Company / Subcontractor Name</label>
                        <input type="text" name="company_name" class="form-control" value="{{ old('company_name') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Trading Name (if different)</label>
                        <input type="text" name="trading_name" class="form-control" value="{{ old('trading_name') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Contact Person</label>
                        <input type="text" name="contact_name" class="form-control" value="{{ old('contact_name') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                    </div>

                    <div class="col-md-12">
                        <div class="card border p-3">
                            <h6 class="fw-bold mb-3 text-primary"><i class="bx bx-shield-check me-1"></i> HMRC Construction Industry Scheme (CIS) Compliance</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label required">HMRC Unique Taxpayer Reference (UTR)</label>
                                    <input type="text" name="utr_number" class="form-control fw-bold" maxlength="10" placeholder="10-digit UTR" value="{{ old('utr_number') }}" required>
                                    <small class="text-muted">Mandatory 10-digit HMRC tax identifier</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label required">CIS Tax Status</label>
                                    <select name="cis_tax_status" class="form-select" required>
                                        <option value="standard" {{ old('cis_tax_status', 'standard') == 'standard' ? 'selected' : '' }}>Standard (20% Net Deduction)</option>
                                        <option value="gross" {{ old('cis_tax_status') == 'gross' ? 'selected' : '' }}>Gross (0% - Fully Exempt)</option>
                                        <option value="higher" {{ old('cis_tax_status') == 'higher' ? 'selected' : '' }}>Higher (30% - Unregistered/Unmatched)</option>
                                    </select>
                                    <small class="text-muted">Determines statutory withholding from labor invoices</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">HMRC Verification Reference #</label>
                                    <input type="text" name="cis_verification_number" class="form-control" placeholder="e.g. V000123456" value="{{ old('cis_verification_number') }}">
                                    <small class="text-muted">Provided upon online HMRC verification</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">HMRC Verification Date</label>
                                    <input type="date" name="verified_at" class="form-control" value="{{ old('verified_at') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Company Registration Number</label>
                                    <input type="text" name="company_registration_number" class="form-control" placeholder="e.g. 09876543" value="{{ old('company_registration_number') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">National Insurance # (Sole Traders)</label>
                                    <input type="text" name="national_insurance_number" class="form-control" placeholder="e.g. QQ 12 34 56 A" value="{{ old('national_insurance_number') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Trade Specialism</label>
                        <input type="text" name="trade_specialism" class="form-control" placeholder="e.g. Scaffolding, Leadwork, Carpentry" value="{{ old('trade_specialism') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label required">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Registered Address</label>
                        <textarea name="address" rows="3" class="form-control">{{ old('address') }}</textarea>
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-end gap-2">
                    <a href="{{ route('subcontractors.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-check me-1"></i> Register Subcontractor</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
