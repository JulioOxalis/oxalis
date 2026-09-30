@extends('oxalis::layouts.oxalis')
@section('title','Authenticator app')
@section('content')
<h5 class="fw-bold mb-4">Authenticator app</h5>
@if($enabled)
<div class="d-flex align-items-center gap-2 mb-3">
  <span class="badge rounded-pill px-3 py-2" style="background:rgba(25,135,84,.12);color:#198754"><i class="bi bi-shield-check me-1"></i>Active</span>
</div>
<p class="text-secondary small mb-3">To remove, confirm with a current 6-digit code:</p>
<form action="{{ route('oxalis.totp.disable') }}" method="POST">
  @csrf
  <div class="input-group mb-0">
    <input type="text" name="code" inputmode="numeric" pattern="\d{6}" maxlength="6"
      class="form-control rounded-start-3 @error('code') is-invalid @enderror"
      placeholder="6-digit code" autocomplete="one-time-code">
    <button class="btn rounded-end-3" style="border:1px solid #dc3545;color:#dc3545;border-left:none">Remove</button>
    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
</form>
@else
<p class="text-secondary small mb-3">No authenticator app set up yet.</p>
<a href="{{ route('oxalis.totp.setup') }}" class="btn btn-ox w-100">Set up now</a>
@endif
@endsection