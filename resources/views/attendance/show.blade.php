@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Attendance: {{ $user?->name }}</h4>
    <form action="{{ route('attendance.show', $user->id) }}" method="GET" class="d-flex gap-2">
        <input type="date" name="start_date" value="{{ $start }}" class="form-control form-control-sm rounded-input" />
        <input type="date" name="end_date" value="{{ $end }}" class="form-control form-control-sm rounded-input" />
        <button class="btn btn-info btn-sm btn-rounded" type="submit">Filter</button>
    </form>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Date</th>
                        <th>Clock In</th>
                        <th>Clock Out</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendance as $a)
                        <tr>
                            <td>{{ $a->date }}</td>
                            <td>{{ $a->clock_in }}</td>
                            <td>{{ $a->clock_out }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center">No records.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-2">{{ $attendance->links() }}</div>
    </div>
</div>
@endsection


