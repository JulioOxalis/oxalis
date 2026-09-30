@extends('oxalis::layouts.oxalis')
@section('title','Recover passkey access')
@section('content')
<div class="ox-icon"><i class="bi bi-life-preserver"></i></div>
<h5 class="fw-bold text-center mb-1">Recover access</h5>
<p class="text-secondary small text-center mb-4">Use one saved recovery code, then add a new passkey.</p>
<form action="{{ route('oxalis.passkeys.recover.verify') }}" method="POST">
  @csrf
  <div class="form-floating mb-3">
    <input type="email" name="email" class="form-control rounded-3 @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="name@example.com" autocomplete="username" autofocus>
    <label>Email address</label>
    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <div class="form-floating mb-3">
    <input type="text" name="recovery_code" class="form-control rounded-3 @error('recovery_code') is-invalid @enderror" placeholder="XXXX-XXXX-XXXX" autocomplete="one-time-code">
    <label>Recovery code</label>
    @error('recovery_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <button class="btn btn-ox w-100">Recover and add passkey</button>
</form>
<hr class="my-3">
<p class="text-center small mb-0"><a href="{{ route('oxalis.login') }}">Back to sign in</a></p>
@endsection
