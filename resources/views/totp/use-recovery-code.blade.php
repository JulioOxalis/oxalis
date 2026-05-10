@extends('oxalis::layouts.oxalis')
@section('title','Recovery code')
@section('content')
<div class="ox-icon"><i class="bi bi-life-preserver"></i></div>
<h5 class="fw-bold text-center mb-1">Use a recovery code</h5>
<p class="text-secondary small text-center mb-4">Each recovery code can only be used once.</p>
<form action="{{ route('oxalis.recovery.verify') }}" method="POST">
  @csrf
  <div class="form-floating mb-3">
    <input type="text" name="recovery_code" class="form-control rounded-3 @error('recovery_code') is-invalid @enderror"
      placeholder="XXXX-XXXX" autofocus autocomplete="off">
    <label>Recovery code</label>
    @error('recovery_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <button class="btn btn-ox w-100">Verify</button>
</form>
<hr class="my-3">
<p class="text-center small mb-0"><a href="{{ route('oxalis.totp.verify.show') }}">← Use authenticator code instead</a></p>
@endsection