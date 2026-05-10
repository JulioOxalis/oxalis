@extends('oxalis::layouts.oxalis')
@section('title','Enter your code')
@section('content')
<div class="ox-icon"><i class="bi bi-envelope-open"></i></div>
<h5 class="fw-bold text-center mb-1">Check your email</h5>
<p class="text-secondary small text-center mb-4">We sent a 6-digit code. It expires in {{ config('oxalis.otp.expires_in',5) }} minutes.</p>
@isset($hint)
<div class="alert border-0 rounded-3 mb-3 text-center" style="background:rgba(255,193,7,.1);color:#997404">
  <span class="small fw-semibold">Dev mode — your code:</span>
  <span class="d-block fw-bold" style="font-size:1.6rem;letter-spacing:.15em;font-family:monospace">{{ $hint }}</span>
</div>
@endisset
<form action="{{ route('oxalis.otp.verify') }}" method="POST">
  @csrf
  <input type="text" name="code" inputmode="numeric" pattern="\d{6}" maxlength="6"
    class="form-control form-control-lg text-center rounded-3 @error('code') is-invalid @enderror mb-3"
    placeholder="000000" autofocus autocomplete="one-time-code"
    style="font-size:1.8rem;letter-spacing:.2em;font-family:monospace">
  @error('code')<div class="invalid-feedback text-center mb-2">{{ $message }}</div>@enderror
  <div class="form-check mb-3 d-flex justify-content-center">
    <input class="form-check-input me-2" type="checkbox" name="remember" id="rem">
    <label class="form-check-label small text-secondary" for="rem">Remember me</label>
  </div>
  <button class="btn btn-ox w-100">Verify code</button>
</form>
<hr class="my-3">
<p class="text-center small mb-0"><a href="{{ route('oxalis.login') }}">← Try another method</a></p>
@endsection