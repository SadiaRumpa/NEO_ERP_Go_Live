@extends('layouts.master')

@section('title', 'Stock Ledger Valuation')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">Stock Ledger Valuation</h4>
            <p class="text-muted mb-0">Live inventory capitalization, warehouse locations, and category unit breakdown</p>
        </div>
        <div>
            <a href="{{ route('reports.export-stock') }}" class="btn btn-primary d-inline-flex align-items-center gap-2 shadow-sm waves-effect">
                <i class="icon-base ti tabler-file-spreadsheet fs-5"></i>
                <span class="fw-medium">Export Inventory CSV</span>
            </a>
        </div>
    </div>

    <!-- Summary Metrics -->
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading fw-semibold">Total Inventory Value</span>
                            <div class="d-flex align-items-center my-1">
                                <h3 class="mb-0 me-2 text-primary">£{{ number_format($metrics['total_valuation'], 2) }}</h3>
                            </div>
                            <small class="text-muted">Weighted average unit cost valuation</small>
                        </div>
                        <div class="avatar avatar-md">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="icon-base ti tabler-currency-pound fs-3"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading fw-semibold">Total Units On Hand</span>
                            <div class="d-flex align-items-center my-1">
                                <h3 class="mb-0 me-2 text-success">{{ number_format($metrics['total_quantity']) }}</h3>
                                <span class="text-muted">units</span>
                            </div>
                            <small class="text-muted">Across {{ $variants->count() }} active inventory variants</small>
                        </div>
                        <div class="avatar avatar-md">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="icon-base ti tabler-packages fs-3"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading fw-semibold">Low Stock Alerts</span>
                            <div class="d-flex align-items-center my-1">
                                <h3 class="mb-0 me-2 {{ $metrics['low_stock_count'] > 0 ? 'text-danger' : 'text-success' }}">
                                    {{ $metrics['low_stock_count'] }}
                                </h3>
                                <span class="text-muted">variants</span>
                            </div>
                            <small class="text-muted">At or below reorder threshold (≤ 10 units)</small>
                        </div>
                        <div class="avatar avatar-md">
                            <span class="avatar-initial rounded {{ $metrics['low_stock_count'] > 0 ? 'bg-label-danger' : 'bg-label-secondary' }}">
                                <i class="icon-base ti tabler-alert-triangle fs-3"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Breakdown Table -->
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Valuation by Category</h5>
            <span class="badge bg-label-primary">{{ count($metrics['category_breakdown']) }} Active Categories</span>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Category</th>
                        <th class="text-center">Variants Count</th>
                        <th class="text-end">Units in Stock</th>
                        <th class="text-end">Total Valuation (GBP)</th>
                        <th class="text-center" style="width: 180px;">Share of Portfolio</th>
                    </tr>
                </thead>
                <tbody>
                    @php $grandTotal = $metrics['total_valuation'] > 0 ? $metrics['total_valuation'] : 1; @endphp
                    @forelse($metrics['category_breakdown'] as $cat)
                        @php
                            $percentage = round(($cat['total_value'] / $grandTotal) * 100, 1);
                        @endphp
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-label-secondary p-2 rounded me-2">
                                        <i class="icon-base ti tabler-category fs-5 text-primary"></i>
                                    </span>
                                    <div>
                                        <div class="fw-semibold text-heading">{{ $cat['category'] }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-label-info">{{ $cat['item_count'] }} variants</span>
                            </td>
                            <td class="text-end fw-medium">{{ number_format($cat['total_stock']) }}</td>
                            <td class="text-end fw-bold text-heading">£{{ number_format($cat['total_value'], 2) }}</td>
                            <td class="text-center">
                                <div class="d-flex align-items-center gap-2 justify-content-center">
                                    <div class="progress w-100" style="height: 6px;">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $percentage }}%" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <small class="fw-medium text-muted" style="min-width: 42px;">{{ $percentage }}%</small>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No stock data available.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Live Stock Variant Ledger Table -->
    @if(isset($variants) && $variants->count() > 0)
    <div class="card shadow-sm border-0">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="card-title mb-0">Live Stock Variant Ledger</h5>
                <small class="text-muted">Detailed item-level capitalization and warehouse depot balance</small>
            </div>
            <span class="badge bg-label-info">{{ $variants->count() }} Managed SKUs</span>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>SKU & Variant</th>
                        <th>Item & Category</th>
                        <th>Warehouse Location</th>
                        <th class="text-end">Current Stock</th>
                        <th class="text-end">Unit Cost (GBP)</th>
                        <th class="text-end">Line Valuation (GBP)</th>
                        <th class="text-center">Stock Health</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @foreach($variants as $v)
                        @php
                            $qty = (float) ($v->quantity ?? 0);
                            $cost = (float) ($v->avg_price ?? 0);
                            $lineVal = $qty * $cost;
                            $isLow = $qty <= 10;
                            $isAdequate = $qty > 10 && $qty <= 25;
                        @endphp
                        <tr>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold font-monospace text-primary">{{ $v->sku }}</span>
                                    <span class="fw-medium text-heading">{{ $v->name }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="text-heading text-truncate" style="max-width: 250px;" title="{{ $v->stockItem?->name }}">
                                        {{ $v->stockItem?->name ?? 'N/A' }}
                                    </span>
                                    <small class="text-muted">{{ $v->stockItem?->category?->name ?? 'Uncategorized' }}</small>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="icon-base ti tabler-building-warehouse text-muted me-1"></i>
                                    <span class="small">{{ $v->warehouse?->name ?? 'Central London Depot' }}</span>
                                </div>
                            </td>
                            <td class="text-end">
                                <span class="fw-bold {{ $isLow ? 'text-danger' : 'text-heading' }}">
                                    {{ number_format($qty) }}
                                </span>
                                <small class="text-muted">{{ $v->unit ?? 'units' }}</small>
                            </td>
                            <td class="text-end font-monospace">£{{ number_format($cost, 2) }}</td>
                            <td class="text-end fw-bold font-monospace text-heading">£{{ number_format($lineVal, 2) }}</td>
                            <td class="text-center">
                                @if($isLow)
                                    <span class="badge bg-label-danger d-inline-flex align-items-center gap-1">
                                        <i class="icon-base ti tabler-alert-triangle fs-6"></i> Low Stock
                                    </span>
                                @elseif($isAdequate)
                                    <span class="badge bg-label-warning">Moderate</span>
                                @else
                                    <span class="badge bg-label-success">In Stock</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
