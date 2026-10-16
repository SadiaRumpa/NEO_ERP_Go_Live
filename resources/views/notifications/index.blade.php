@extends('layouts.master')

@section('title', 'Notifications')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header header-elements">
            <h5 class="card-title mb-0">System Notifications</h5>
            <div class="card-header-elements ms-auto py-0">
                <button id="btnMarkAllRead" class="btn btn-outline-secondary btn-sm waves-effect">
                    <i class="ti ti-checks me-1"></i> Mark All as Read
                </button>
            </div>
        </div>

        <div class="px-4 py-3 d-flex justify-content-between align-items-center flex-wrap gap-2 border-bottom">
            @include('partials.per-page-selector', ['paginator' => $notifications])
        </div>

        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th style="width: 50px;">Status</th>
                        <th>Notification</th>
                        <th>Type</th>
                        <th>Received</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($notifications as $notification)
                        @php
                            $isRead = $notification->read_at !== null;
                            $data = $notification->data;
                        @endphp
                        <tr class="{{ $isRead ? 'opacity-75' : 'table-light fw-medium' }}" id="notif-row-{{ $notification->id }}">
                            <td>
                                @if(!$isRead)
                                    <span class="badge badge-dot bg-primary"></span>
                                @else
                                    <span class="badge badge-dot bg-secondary"></span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-semibold text-heading">{{ $data['title'] ?? 'Notification' }}</span>
                                    <small class="text-muted">{{ $data['message'] ?? '' }}</small>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-label-info">{{ ucfirst(str_replace('_', ' ', $data['type'] ?? 'General')) }}</span>
                            </td>
                            <td>
                                <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                            </td>
                            <td class="text-end">
                                @if(!empty($data['action_url']))
                                    <a href="{{ $data['action_url'] }}" class="btn btn-sm btn-label-primary waves-effect me-1">View</a>
                                @endif
                                @if(!$isRead)
                                    <button class="btn btn-sm btn-icon btn-outline-secondary waves-effect btn-mark-read" data-id="{{ $notification->id }}" title="Mark as read">
                                        <i class="ti ti-check"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                <i class="ti ti-bell-off fs-2 mb-2 d-block"></i>
                                No notifications yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="text-muted small">
                Showing {{ $notifications->firstItem() ?? 0 }} to {{ $notifications->lastItem() ?? 0 }} of {{ $notifications->total() }} results
            </div>
            <div>
                {{ $notifications->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@parent
<script>
    document.querySelectorAll('.btn-mark-read').forEach(button => {
        button.addEventListener('click', function() {
            const notifId = this.getAttribute('data-id');
            fetch(`{{ url('notifications') }}/${notifId}/read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            }).then(r => r.json()).then(data => {
                if (data.success) {
                    const row = document.getElementById(`notif-row-${notifId}`);
                    if (row) {
                        row.classList.remove('table-light', 'fw-medium');
                        row.classList.add('opacity-75');
                        row.querySelector('.badge-dot')?.classList.replace('bg-primary', 'bg-secondary');
                        button.remove();
                    }
                }
            });
        });
    });

    document.getElementById('btnMarkAllRead')?.addEventListener('click', function() {
        fetch(`{{ route('notifications.mark-all-read') }}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        }).then(r => r.json()).then(data => {
            if (data.success) {
                window.location.reload();
            }
        });
    });
</script>
@endsection
