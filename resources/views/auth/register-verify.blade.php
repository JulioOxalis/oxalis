@extends('oxalis::layouts.oxalis')
@section('title','Verify your email')
@section('content')

@php $withPassword = config('oxalis.methods.password', true); @endphp
{{-- Step indicator --}}
<div class="d-flex align-items-center justify-content-center gap-0 mb-4">
  <div class="d-flex flex-column align-items-center">
    <div style="width:28px;height:28px;border-radius:50%;background:rgba(25,135,84,.15);color:#198754;display:flex;align-items:center;justify-content:center;font-size:.8rem">
      <i class="bi bi-check2" style="font-weight:800"></i>
    </div>
    <div style="font-size:.65rem;color:#198754;margin-top:.3rem;font-weight:600">Details</div>
  </div>
  <div style="width:40px;height:2px;background:var(--ox);margin-bottom:1.1rem"></div>
  <div class="d-flex flex-column align-items-center">
    <div style="width:28px;height:28px;border-radius:50%;background:var(--ox);color:#fff;display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:700">2</div>
    <div style="font-size:.65rem;color:var(--ox);margin-top:.3rem;font-weight:600">Verify</div>
  </div>
  @if($withPassword)
  <div style="width:40px;height:2px;background:var(--bs-border-color);margin-bottom:1.1rem"></div>
  <div class="d-flex flex-column align-items-center">
    <div style="width:28px;height:28px;border-radius:50%;background:var(--bs-tertiary-bg,#f0f0f0);color:var(--bs-secondary-color);display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:700">3</div>
    <div style="font-size:.65rem;color:var(--bs-secondary-color);margin-top:.3rem">Password</div>
  </div>
  @endif
</div>

<div class="ox-icon"><i class="bi bi-envelope-open"></i></div>
<h5 class="fw-bold text-center mb-1">Check your email</h5>
<p class="text-secondary small text-center mb-4">We sent a 6-digit code to confirm your email address. It expires in 10 minutes.</p>

@isset($hint)
<div class="alert border-0 rounded-3 mb-3 text-center" style="background:rgba(255,193,7,.1);color:#997404">
  <span class="small fw-semibold d-block mb-1">Dev mode — your code:</span>
  <span class="fw-bold" style="font-size:1.8rem;letter-spacing:.2em;font-family:monospace">{{ $hint }}</span>
</div>
@endisset

<form action="{{ route('oxalis.register.verify') }}" method="POST">
  @csrf
  <input type="text" name="code" inputmode="numeric" pattern="\d{6}" maxlength="6"
    class="form-control form-control-lg text-center rounded-3 @error('code') is-invalid @enderror mb-3"
    placeholder="000000" autofocus autocomplete="one-time-code"
    style="font-size:1.8rem;letter-spacing:.2em;font-family:monospace">
  @error('code')<div class="invalid-feedback text-center mb-2">{{ $message }}</div>@enderror
  <button class="btn btn-ox w-100 d-flex align-items-center justify-content-center gap-2">
    Verify email <i class="bi bi-arrow-right"></i>
  </button>
</form>

<p class="text-center small text-secondary mt-3 mb-0">
  Wrong email? <a href="{{ route('oxalis.register') }}">Start over</a>
</p>
@endsection
