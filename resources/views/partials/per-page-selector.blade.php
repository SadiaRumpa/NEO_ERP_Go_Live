<div class="d-flex align-items-center gap-2">
    <label class="text-muted small fw-medium mb-0 text-nowrap d-flex align-items-center">
        <i class="icon-base ti tabler-list-numbers me-1"></i> Show
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
