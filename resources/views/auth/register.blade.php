@extends('oxalis::layouts.oxalis')
@section('title','Create account')
@section('content')

{{-- Step indicator --}}
<div class="d-flex align-items-center justify-content-center gap-0 mb-4">
  <div class="d-flex flex-column align-items-center">
    <div style="width:28px;height:28px;border-radius:50%;background:var(--ox);color:#fff;display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:700">1</div>
    <div style="font-size:.65rem;color:var(--ox);margin-top:.3rem;font-weight:600">Details</div>
  </div>
  <div style="width:40px;height:2px;background:var(--bs-border-color);margin-bottom:1.1rem"></div>
  <div class="d-flex flex-column align-items-center">
    <div style="width:28px;height:28px;border-radius:50%;background:var(--bs-tertiary-bg,#f0f0f0);color:var(--bs-secondary-color);display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:700">2</div>
    <div style="font-size:.65rem;color:var(--bs-secondary-color);margin-top:.3rem">Verify</div>
  </div>
  <div style="width:40px;height:2px;background:var(--bs-border-color);margin-bottom:1.1rem"></div>
  <div class="d-flex flex-column align-items-center">
    <div style="width:28px;height:28px;border-radius:50%;background:var(--bs-tertiary-bg,#f0f0f0);color:var(--bs-secondary-color);display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:700">3</div>
    <div style="font-size:.65rem;color:var(--bs-secondary-color);margin-top:.3rem">Password</div>
  </div>
</div>

<h5 class="fw-bold text-center mb-1">Create your account</h5>
<p class="text-secondary small text-center mb-4">We'll send a verification code to confirm your email.</p>

<form action="{{ route('oxalis.register.submit') }}" method="POST">
  @csrf
  <div class="form-floating mb-3">
    <input type="text" name="name" id="name"
      class="form-control rounded-3 @error('name') is-invalid @enderror"
      placeholder="n" value="{{ old('name') }}" autofocus autocomplete="name">
    <label for="name">Full name</label>
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <div class="form-floating mb-4">
    <input type="email" name="email" id="email"
      class="form-control rounded-3 @error('email') is-invalid @enderror"
      placeholder="e" value="{{ old('email') }}" autocomplete="email">
    <label for="email">Email address</label>
    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <button class="btn btn-ox w-100 d-flex align-items-center justify-content-center gap-2">
    Continue <i class="bi bi-arrow-right"></i>
  </button>
</form>

<hr class="my-3">
<p class="text-center small mb-0 text-secondary">Already have an account? <a href="{{ route('oxalis.login') }}" class="fw-semibold">Sign in</a></p>
@endsection
