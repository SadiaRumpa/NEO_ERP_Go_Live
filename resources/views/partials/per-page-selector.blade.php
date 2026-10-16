<div class="d-flex align-items-center gap-2">
    <label class="text-muted small fw-medium mb-0 text-nowrap d-flex align-items-center">
        <i class="bx bx-list-ol me-1"></i> Show
    </label>
    <select class="form-select form-select-sm per-page-selector w-auto" style="min-width: 75px;" aria-label="Select entries per page">
        @php
            $selectedPerPage = (int) request('per_page', isset($perPage) ? $perPage : (isset($paginator) ? $paginator->perPage() : 10));
            $options = [5, 10, 25, 50, 100];
            if (!in_array($selectedPerPage, $options) && $selectedPerPage > 0) {
                $options[] = $selectedPerPage;
                sort($options);
            }
        @endphp
        @foreach($options as $val)
            <option value="{{ $val }}" {{ $selectedPerPage === $val ? 'selected' : '' }}>{{ $val }}</option>
        @endforeach
    </select>
    <label class="text-muted small fw-medium mb-0 text-nowrap">entries</label>
</div>

<div class="table-slider-controls d-flex align-items-center gap-2 d-none">
    <span class="text-muted small d-none d-md-inline-flex align-items-center">
        <i class="bx bx-move-horizontal me-1"></i> Scroll Table:
    </span>
    <div class="btn-group btn-group-sm shadow-sm" role="group" aria-label="Table horizontal slider">
        <button type="button" class="btn btn-outline-primary d-inline-flex align-items-center gap-1 btn-slide-table-left" title="Slide Left">
            <i class="bx bx-chevron-left"></i> <span>Slide Left</span>
        </button>
        <button type="button" class="btn btn-outline-primary d-inline-flex align-items-center gap-1 btn-slide-table-right" title="Slide Right">
            <span>Slide Right</span> <i class="bx bx-chevron-right"></i>
        </button>
    </div>
</div>
