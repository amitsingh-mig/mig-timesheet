@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <!-- Modern Employee Header -->
    <div class="employee-header">
        <div class="employee-header-content">
            <div class="employee-title-section">
                <h1 class="employee-title">📝 Daily Update</h1>
                <p class="employee-subtitle">Record your daily work summary and accomplishments</p>
            </div>
            <div class="employee-time-display">
                <div class="current-time">{{ date('H:i') }}</div>
                <div class="current-date">{{ date('l, F j, Y') }}</div>
            </div>
        </div>
    </div>

<div class="container py-5">
    <div class="row g-5">

        <!-- Today's Daily Update Form -->
        <div class="col-12">
            <div class="employee-card">
                <div class="employee-card-header">
                    <div class="employee-card-title">
                        <i class="bi bi-pencil-square"></i>
                        {{ $todaySummary ? 'Update Today\'s Summary' : 'Add Today\'s Summary' }}
                    </div>
                </div>
                <div class="employee-card-body p-5">
                    <form method="POST" action="{{ route('daily-update.store') }}">
                        @csrf
                        <input type="hidden" name="date" value="{{ $date }}">
                        
                        <div class="form-group-modern mb-4">
                            <label for="summary" class="form-label-modern mb-3">
                                <i class="bi bi-journal-text me-1"></i>
                                Work Summary <span class="text-danger">*</span>
                            </label>
                            <textarea 
                                name="summary" 
                                id="summary" 
                                class="form-input-modern @error('summary') is-invalid @enderror" 
                                rows="8" 
                                placeholder="Describe what you accomplished today, tasks completed, meetings attended, challenges faced, etc..."
                                required>{{ old('summary', $todaySummary ? $todaySummary->summary : '') }}</textarea>
                            @error('summary')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text-modern mt-2">
                                <i class="bi bi-info-circle me-1"></i>
                                Maximum 1000 characters. Be specific about your accomplishments and progress.
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div class="user-info-modern">
                                <i class="bi bi-person me-1"></i>
                                Updating as: <strong>{{ Auth::user()->name }}</strong>
                            </div>
                            <button type="submit" class="btn-action-modern">
                                <i class="bi bi-check-circle me-2"></i>
                                {{ $todaySummary ? 'Update Summary' : 'Save Summary' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

        <!-- Recent Daily Updates -->
        <div class="col-12">
            <div class="employee-card">
                <div class="employee-card-header">
                    <div class="employee-card-title">
                        <i class="bi bi-clock-history"></i>
                        Recent Daily Updates
                    </div>
                </div>
                <div class="employee-card-body p-4">
                    @if($summaries->count() > 0)
                        <div class="table-responsive">
                            <table class="table employee-table">
                                <thead>
                                    <tr>
                                        <th class="text-start" width="120">Date</th>
                                        <th class="text-start">Summary</th>
                                        <th class="text-center" width="100">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($summaries as $summary)
                                    <tr>
                                        <td class="fw-medium py-3">
                                            <div class="d-flex flex-column">
                                                <span>{{ $summary->date->format('M j, Y') }}</span>
                                                <small class="text-muted">{{ $summary->date->format('l') }}</small>
                                            </div>
                                        </td>
                                        <td class="py-3">
                                            <div class="summary-text-modern">
                                                {{ Str::limit($summary->summary, 200, '...') }}
                                            </div>
                                            @if(strlen($summary->summary) > 200)
                                                <button class="btn btn-link btn-sm p-0 mt-2" type="button" data-bs-toggle="collapse" data-bs-target="#summary-{{ $summary->id }}">
                                                    <small>Read more</small>
                                                </button>
                                                <div class="collapse mt-2" id="summary-{{ $summary->id }}">
                                                    <div class="text-muted">{{ $summary->summary }}</div>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="text-center py-3">
                                            @if($summary->date->isToday())
                                                <span class="badge bg-success">Today</span>
                                            @elseif($summary->date->isYesterday())
                                                <span class="badge bg-warning">Yesterday</span>
                                            @else
                                                <span class="badge bg-secondary">
                                                    {{ $summary->date->diffForHumans() }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        @if($summaries->hasPages())
                            <div class="card-footer-modern">
                                {{ $summaries->links() }}
                            </div>
                        @endif
                    @else
                        <div class="employee-loading py-5">
                            <div class="empty-icon">
                                <i class="bi bi-journal-text"></i>
                            </div>
                            <h5>No Daily Updates Yet</h5>
                            <p>Start by adding your first daily work summary above.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
