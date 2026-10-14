@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="mb-4">
        <h4 class="fw-bold py-1 mb-1"><span class="text-muted fw-light">Procurement / Suppliers /</span> Add Supplier</h4>
        <p class="text-muted mb-0">Register an approved trade vendor or subcontractor merchant.</p>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('procurement.suppliers.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label required">Company / Merchant Name</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Vendor Code / Reference</label>
                        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" placeholder="e.g. SUP-TP-001">
                        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                    <div class="col-md-6">
                        <label class="form-label">VAT / Tax Identification Number</label>
                        <input type="text" name="tax_number" class="form-control" value="{{ old('tax_number') }}" placeholder="e.g. GB 123 4567 89">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Payment Terms</label>
                        <select name="payment_terms" class="form-select">
                            <option value="Immediate" {{ old('payment_terms') == 'Immediate' ? 'selected' : '' }}>Immediate</option>
                            <option value="14 Days Net" {{ old('payment_terms') == '14 Days Net' ? 'selected' : '' }}>14 Days Net</option>
                            <option value="30 Days Net" {{ old('payment_terms', '30 Days Net') == '30 Days Net' ? 'selected' : '' }}>30 Days Net</option>
                            <option value="60 Days Net" {{ old('payment_terms') == '60 Days Net' ? 'selected' : '' }}>60 Days Net</option>
                            <option value="End of Next Month" {{ old('payment_terms') == 'End of Next Month' ? 'selected' : '' }}>End of Next Month</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label required">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Billing & Yard Address</label>
                        <textarea name="address" rows="3" class="form-control">{{ old('address') }}</textarea>
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-end gap-2">
                    <a href="{{ route('procurement.suppliers.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-check me-1"></i> Save Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
