@extends('layouts.app')

@section('content')
<!-- Modern Daily Update Page -->
<div class="container-fluid px-0">
    <!-- Modern Header -->
    <div class="attendance-header-modern">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="header-content-modern">
                        <h1 class="page-title-modern">📝 Daily Update</h1>
                        <p class="page-subtitle-modern">Record your daily work summary and accomplishments</p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="current-time-display-modern">
                        <div class="time-display">
                            <span class="time-text">{{ date('H:i') }}</span>
                        </div>
                        <div class="date-display">
                            <span class="date-text">{{ date('l, F j, Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    <div class="row g-5">

        <!-- Today's Daily Update Form -->
        <div class="col-12">
            <div class="card-modern">
                <div class="card-header-modern">
                    <h3 class="card-title-modern">
                        <i class="bi bi-pencil-square me-2"></i>
                        {{ $todaySummary ? 'Update Today\'s Summary' : 'Add Today\'s Summary' }}
                    </h3>
                </div>
                <div class="card-body-modern p-5">
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
            <div class="card-modern">
                <div class="card-header-modern">
                    <h3 class="card-title-modern">
                        <i class="bi bi-clock-history me-2"></i>
                        Recent Daily Updates
                    </h3>
                </div>
                <div class="card-body-modern p-4">
                    @if($summaries->count() > 0)
                        <div class="table-responsive-modern">
                            <table class="table-modern">
                                <thead class="table-header-modern">
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
                        <div class="empty-state-modern py-5">
                            <div class="empty-icon-modern">
                                <i class="bi bi-journal-text"></i>
                            </div>
                            <h5 class="empty-title-modern">No Daily Updates Yet</h5>
                            <p class="empty-text-modern">Start by adding your first daily work summary above.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
