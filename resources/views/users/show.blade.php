@extends('layouts.app')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">User Details</h5>
        <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning btn-rounded btn-sm">Edit</a>
    </div>
    <div class="card-body">
        <div class="row mb-2"><div class="col-md-3 fw-bold">ID</div><div class="col-md-9">{{ $user->id }}</div></div>
        <div class="row mb-2"><div class="col-md-3 fw-bold">Name</div><div class="col-md-9">{{ $user->name }}</div></div>
        <div class="row mb-2"><div class="col-md-3 fw-bold">Email</div><div class="col-md-9">{{ $user->email }}</div></div>
        <a href="{{ route('users.index') }}" class="btn btn-secondary btn-rounded">Back</a>
    </div>
</div>
@endsection


