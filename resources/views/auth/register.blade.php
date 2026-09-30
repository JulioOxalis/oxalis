@extends('oxalis::layouts.oxalis')
@section('title','Create account')
@section('content')

@php $withPassword = config('oxalis.methods.password', true); @endphp
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
  @if($withPassword)
  <div style="width:40px;height:2px;background:var(--bs-border-color);margin-bottom:1.1rem"></div>
  <div class="d-flex flex-column align-items-center">
    <div style="width:28px;height:28px;border-radius:50%;background:var(--bs-tertiary-bg,#f0f0f0);color:var(--bs-secondary-color);display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:700">3</div>
    <div style="font-size:.65rem;color:var(--bs-secondary-color);margin-top:.3rem">Password</div>
  </div>
  @endif
</div>

<h5 class="fw-bold text-center mb-1">Create your account</h5>
<p class="text-secondary small text-center mb-4">We'll send a verification code to confirm your email.</p>

@php $allowedDomains = array_filter(array_map('trim', explode(',', config('oxalis.allowed_domains', '')))); @endphp
@if(!empty($allowedDomains))
<div class="alert border-0 rounded-3 small mb-3 d-flex align-items-center gap-2" style="background:var(--ox-sf);color:var(--ox)">
  <i class="bi bi-envelope-check"></i>
  <span>Registration is restricted to: <strong>{{ implode(', ', $allowedDomains) }}</strong></span>
</div>
@endif

@if(config('oxalis.invites.required', false))
<div class="alert border-0 rounded-3 small mb-3 d-flex align-items-center gap-2" style="background:rgba(255,193,7,.1);color:#997404">
  <i class="bi bi-ticket-perforated"></i>
  <span>An invite code is required to register.</span>
</div>
@endif

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
  @if(config('oxalis.invites.required', false))
  <div class="input-group mb-3">
    <span class="input-group-text rounded-start-3" style="background:var(--ox-sf);border-color:var(--bs-border-color);color:var(--ox)">
      <i class="bi bi-ticket-perforated"></i>
    </span>
    <input type="text" name="invite_code"
      class="form-control rounded-end-3 @error('invite_code') is-invalid @enderror"
      placeholder="Invite code — e.g. A3BF72KQ"
      value="{{ old('invite_code') }}" autocomplete="off"
      style="text-transform:uppercase;letter-spacing:.1em">
    @error('invite_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  @endif
  <button class="btn btn-ox w-100 d-flex align-items-center justify-content-center gap-2">
    Continue <i class="bi bi-arrow-right"></i>
  </button>
</form>

<hr class="my-3">
<p class="text-center small mb-0 text-secondary">Already have an account? <a href="{{ route('oxalis.login') }}" class="fw-semibold">Sign in</a></p>
@endsection
