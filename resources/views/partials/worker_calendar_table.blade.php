<table class="table table-bordered calendar-table">
    <thead class="table-light">
        <tr>
            <th class="sticky-col bg-white" rowspan="2">Worker Name</th>
            @foreach ($datesByMonth as $month => $dates)
                <th colspan="{{ count($dates) }}" class="text-center">{{ $month }}</th>
            @endforeach
        </tr>
        <tr>
            @foreach ($datesByMonth as $month => $dates)
                @foreach ($dates as $date)
                    <th>{{ \Carbon\Carbon::parse($date)->format('d') }}</th>
                @endforeach
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach ($workers as $worker)
            <tr>
                <td><strong>{{ $worker->name }}</strong></td>
                @foreach ($datesByMonth as $month => $dates)
                    @foreach ($dates as $date)
                        @php
                            $assigned = optional($assignments[$worker->id] ?? collect())->firstWhere(
                                'assigned_date',
                                $date,
                            );
                        @endphp
                        <td>
                            @if ($assigned)
                                <span class="badge bg-info">
                                    {{ $assigned->jobSchedule->job_name ?? 'Job #' . $assigned->job_schedule_id }}
                                </span>
                            @else
                                -
                            @endif
                        </td>
                    @endforeach
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>
