@extends('oxalis::layouts.oxalis')
@section('title','Two-factor authentication')
@section('content')
<div class="ox-icon"><i class="bi bi-shield-lock"></i></div>
<h5 class="fw-bold text-center mb-1">Two-factor authentication</h5>
<p class="text-secondary small text-center mb-4">Open your authenticator app and enter the current 6-digit code.</p>
<form action="{{ route('oxalis.totp.verify') }}" method="POST">
  @csrf
  <input type="text" name="code" inputmode="numeric" pattern="\d{6}" maxlength="6"
    class="form-control form-control-lg text-center rounded-3 @error('code') is-invalid @enderror mb-3"
    placeholder="000000" autofocus autocomplete="one-time-code"
    style="font-size:1.8rem;letter-spacing:.2em;font-family:monospace">
  @error('code')<div class="invalid-feedback text-center mb-2">{{ $message }}</div>@enderror
  <button class="btn btn-ox w-100">Verify</button>
</form>
<hr class="my-3">
<p class="text-center small mb-0 text-secondary">Lost your phone? <a href="{{ route('oxalis.recovery.show') }}">Use a recovery code</a></p>
@endsection