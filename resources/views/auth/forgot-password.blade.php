@extends('oxalis::layouts.oxalis')
@section('title','Reset password')
@section('content')
<div class="ox-icon"><i class="bi bi-key"></i></div>
<h5 class="fw-bold text-center mb-1">Reset your password</h5>
<p class="text-secondary small text-center mb-4">We'll email you a reset link valid for 60 minutes.</p>
<form action="{{ route('oxalis.password.forgot.send') }}" method="POST">
  @csrf
  <div class="form-floating mb-3">
    <input type="email" name="email" class="form-control rounded-3 @error('email') is-invalid @enderror" placeholder="e" value="{{ old('email') }}" autofocus>
    <label>Email address</label>
    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <button class="btn btn-ox w-100">Send reset link</button>
</form>
<hr class="my-3">
<p class="text-center small mb-0"><a href="{{ route('oxalis.login') }}">← Back to sign in</a></p>
@endsection