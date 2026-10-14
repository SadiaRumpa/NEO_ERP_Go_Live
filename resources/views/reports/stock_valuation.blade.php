@extends('layouts.master')

@section('title', 'Stock Ledger Valuation')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Stock Ledger Valuation</h4>
            <p class="text-muted mb-0">Live inventory capitalization and category unit breakdown</p>
        </div>
        <div>
            <a href="{{ route('reports.export-stock') }}" class="btn btn-primary waves-effect">
                <i class="ti ti-file-spreadsheet me-1"></i> Export Inventory CSV
            </a>
        </div>
    </div>

    <!-- Summary Metrics -->
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">Total Inventory Value</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2 text-primary">£{{ number_format($metrics['total_valuation'], 2) }}</h4>
                            </div>
                            <small class="text-muted">Weighted unit cost valuation</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="ti ti-currency-pound ti-md"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">Total Units On Hand</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ number_format($metrics['total_quantity']) }}</h4>
                            </div>
                            <small class="text-muted">Across all managed variants</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="ti ti-packages ti-md"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">Low Stock Alerts</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2 {{ $metrics['low_stock_count'] > 0 ? 'text-danger' : 'text-success' }}">
                                    {{ $metrics['low_stock_count'] }}
                                </h4>
                            </div>
                            <small class="text-muted">Variants at or below reorder level</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded {{ $metrics['low_stock_count'] > 0 ? 'bg-label-danger' : 'bg-label-secondary' }}">
                                <i class="ti ti-alert-triangle ti-md"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Breakdown Table -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Valuation by Category</h5>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th class="text-center">Variants Count</th>
                        <th class="text-end">Units in Stock</th>
                        <th class="text-end">Total Valuation (GBP)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($metrics['category_breakdown'] as $cat)
                        <tr>
                            <td><strong>{{ $cat['category'] }}</strong></td>
                            <td class="text-center">{{ $cat['item_count'] }}</td>
                            <td class="text-end">{{ number_format($cat['total_stock']) }}</td>
                            <td class="text-end fw-semibold">£{{ number_format($cat['total_value'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">No stock data available.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
