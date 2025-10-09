@props(['messages'])

@if ($messages)
    <div {{ $attributes->merge(['class' => 'mt-2']) }}>
        @foreach ((array) $messages as $message)
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <span class="small">{{ $message }}</span>
                <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endforeach
    </div>
@endif
