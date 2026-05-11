@extends('oxalis::layouts.oxalis')
@section('title','Change email')
@section('content')
<div class="ox-icon"><i class="bi bi-envelope-check"></i></div>
<h5 class="fw-bold text-center mb-1">Change email address</h5>
<p class="text-secondary small text-center mb-4">
  Current: <strong>{{ auth()->user()->email }}</strong><br>
  Enter your new address — we'll send a verification code to confirm it.
</p>
<form action="{{ route('oxalis.account.email.send') }}" method="POST">
  @csrf
  <div class="form-floating mb-3">
    <input type="email" name="email" id="email" class="form-control rounded-3 @error('email') is-invalid @enderror"
      placeholder="e" autofocus autocomplete="email">
    <label for="email">New email address</label>
    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <div class="form-floating mb-4">
    <input type="password" name="current_password" id="current_password"
      class="form-control rounded-3 @error('current_password') is-invalid @enderror"
      placeholder="p" autocomplete="current-password">
    <label for="current_password">Current password <span class="text-secondary fw-normal small">(to confirm it's you)</span></label>
    @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <button class="btn btn-ox w-100 d-flex align-items-center justify-content-center gap-2">
    Send verification code <i class="bi bi-arrow-right"></i>
  </button>
</form>
<hr class="my-3">
<p class="text-center small mb-0"><a href="{{ route('oxalis.account') }}">← Back to account</a></p>
@endsection
