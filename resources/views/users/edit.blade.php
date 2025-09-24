@extends('layouts.app')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-dark text-white">
        <h5 class="mb-0">Edit User</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('users.update', $user->id) }}" method="POST" class="p-2">
            @csrf
            <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control rounded-input @error('name') is-invalid @enderror" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control rounded-input @error('email') is-invalid @enderror" required>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <button class="btn btn-success btn-rounded" type="submit">Save</button>
            <a href="{{ route('users.show', $user->id) }}" class="btn btn-secondary btn-rounded">Cancel</a>
        </form>
    </div>
</div>
@endsection


