@extends('layouts.app')

@section('content')
<div class="card shadow-sm mb-3">
    <div class="card-header bg-dark text-white">Admin Timesheets</div>
    <div class="card-body">
        <form class="row g-2 mb-3" method="GET" action="{{ route('timesheet.admin.index') }}">
            <div class="col-sm-2">
                <input type="number" name="user_id" value="{{ request('user_id') }}" class="form-control rounded-input" placeholder="User ID">
            </div>
            <div class="col-sm-2">
                <select name="department" class="form-control rounded-input">
                    <option value="">All Departments</option>
                    <option value="Web" {{ request('department') == 'Web' ? 'selected' : '' }}>Web Development</option>
                    <option value="Graphic" {{ request('department') == 'Graphic' ? 'selected' : '' }}>Graphic Design</option>
                    <option value="Editorial" {{ request('department') == 'Editorial' ? 'selected' : '' }}>Editorial</option>
                    <option value="Multimedia" {{ request('department') == 'Multimedia' ? 'selected' : '' }}>Multimedia</option>
                    <option value="Sales" {{ request('department') == 'Sales' ? 'selected' : '' }}>Sales</option>
                    <option value="Marketing" {{ request('department') == 'Marketing' ? 'selected' : '' }}>Marketing</option>
                    <option value="Intern" {{ request('department') == 'Intern' ? 'selected' : '' }}>Internship</option>
                    <option value="General" {{ request('department') == 'General' ? 'selected' : '' }}>General</option>
                </select>
            </div>
            <div class="col-sm-2">
                <input type="text" name="task" value="{{ request('task') }}" class="form-control rounded-input" placeholder="Task">
            </div>
            <div class="col-sm-2">
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control rounded-input">
            </div>
            <div class="col-sm-2">
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control rounded-input">
            </div>
            <div class="col-sm-2 d-grid">
                <button class="btn btn-info btn-rounded" type="submit">Filter</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Employee</th>
                        <th>Date</th>
                        <th>Task</th>
                        <th class="text-end">Hours</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($entries as $e)
                        <tr>
                            <td>{{ $e->user?->name }} (ID: {{ $e->user_id }})</td>
                            <td>{{ $e->date }}</td>
                            <td>{{ $e->task }}</td>
                            <td class="text-end">{{ $e->hours }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center">
            <div>{{ $entries->links() }}</div>
            <a href="{{ route('timesheet.admin.index', array_merge(request()->all(), ['export' => 'csv'])) }}" class="btn btn-success btn-rounded">Export CSV</a>
        </div>
    </div>
</div>
@endsection


