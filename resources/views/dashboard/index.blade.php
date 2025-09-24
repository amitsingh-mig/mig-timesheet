@extends('layouts.app')

@section('content')
<div class="row g-3">
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-header">Social Stream</div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="fw-bold">Greetings</div>
                    <div class="text-muted small">Happy Women's Day to all Aspiring Women.</div>
                </div>
                <div class="mb-3">
                    <div class="fw-bold">Anniversary</div>
                    <div class="text-muted small">Congratulations to Marcus on completing one successful year 🎉</div>
                </div>
                <button class="btn btn-primary btn-rounded">+ Add Comment</button>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="card mb-3">
            <div class="card-header">Personal Note</div>
            <div class="card-body">
                <div class="small text-muted">Welcome back!</div>
            </div>
        </div>
        <div class="card">
            <div class="card-header">Discussions</div>
            <div class="card-body">
                <div class="small">Daniel: Hello Team, I am not able to login</div>
                <div class="small text-muted">1 hour ago</div>
            </div>
        </div>
    </div>
</div>

<!-- Add Location Modal Trigger -->
<button type="button" class="btn btn-info btn-rounded mt-3" data-bs-toggle="modal" data-bs-target="#addLocationModal">
    + Add Location
  </button>

<!-- Add Location Modal -->
<div class="modal fade" id="addLocationModal" tabindex="-1" aria-labelledby="addLocationModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="addLocationModalLabel">Add Location</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form method="POST" action="#">
          <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" class="form-control rounded-input" placeholder="Please enter location name">
          </div>
          <div class="mb-3">
            <label class="form-label">Address</label>
            <input type="text" class="form-control rounded-input" placeholder="Please enter location address">
          </div>
          <div class="mb-3">
            <label class="form-label">Members</label>
            <input type="number" class="form-control rounded-input" placeholder="Please enter members">
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-rounded" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary btn-rounded">Save</button>
      </div>
    </div>
  </div>
</div>
@endsection


