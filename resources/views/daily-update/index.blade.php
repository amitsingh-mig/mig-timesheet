@extends('layouts.app')

@section('content')
<div class="container py-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1 fw-bold">📝 Daily Update</h2>
                    <p class="text-muted mb-0">Record your daily work summary and accomplishments</p>
                </div>
                <div class="badge bg-primary-subtle text-primary fs-6 px-3 py-2">
                    {{ date('l, F j, Y') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Daily Update Form -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-edit me-2"></i>
                        {{ $todaySummary ? 'Update Today\'s Summary' : 'Add Today\'s Summary' }}
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('daily-update.store') }}">
                        @csrf
                        <input type="hidden" name="date" value="{{ $date }}">
                        
                        <div class="mb-3">
                            <label for="summary" class="form-label fw-semibold">
                                Work Summary <span class="text-danger">*</span>
                            </label>
                            <textarea 
                                name="summary" 
                                id="summary" 
                                class="form-control @error('summary') is-invalid @enderror" 
                                rows="6" 
                                placeholder="Describe what you accomplished today, tasks completed, meetings attended, challenges faced, etc..."
                                required>{{ old('summary', $todaySummary ? $todaySummary->summary : '') }}</textarea>
                            @error('summary')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Maximum 1000 characters. Be specific about your accomplishments and progress.
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted">
                                <i class="fas fa-user me-1"></i>
                                Updating as: <strong>{{ Auth::user()->name }}</strong>
                            </div>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-save me-2"></i>
                                {{ $todaySummary ? 'Update Summary' : 'Save Summary' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Daily Updates -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>
                        Recent Daily Updates
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($summaries->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="120">Date</th>
                                        <th>Summary</th>
                                        <th width="100">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($summaries as $summary)
                                    <tr>
                                        <td class="fw-semibold">
                                            <div class="d-flex flex-column">
                                                <span>{{ $summary->date->format('M j, Y') }}</span>
                                                <small class="text-muted">{{ $summary->date->format('l') }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="summary-text">
                                                {{ Str::limit($summary->summary, 200, '...') }}
                                            </div>
                                            @if(strlen($summary->summary) > 200)
                                                <button class="btn btn-link btn-sm p-0 mt-1" type="button" data-bs-toggle="collapse" data-bs-target="#summary-{{ $summary->id }}">
                                                    <small>Read more</small>
                                                </button>
                                                <div class="collapse mt-2" id="summary-{{ $summary->id }}">
                                                    <div class="text-muted">{{ $summary->summary }}</div>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
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
                            <div class="card-footer bg-light">
                                {{ $summaries->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <i class="fas fa-clipboard-list fa-3x text-muted"></i>
                            </div>
                            <h5 class="text-muted">No Daily Updates Yet</h5>
                            <p class="text-muted mb-4">Start by adding your first daily work summary above.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.summary-text {
    line-height: 1.6;
    white-space: pre-line;
}

.card {
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
}

.btn-primary {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    border: none;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #1d4ed8, #1e40af);
    transform: translateY(-1px);
}

.table th {
    border-top: none;
    font-weight: 600;
}

.badge {
    font-size: 0.75rem;
}
</style>
@endsection
