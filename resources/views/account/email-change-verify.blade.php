@extends('oxalis::layouts.oxalis')
@section('title','Verify new email')
@section('content')
<div class="ox-icon"><i class="bi bi-envelope-open"></i></div>
<h5 class="fw-bold text-center mb-1">Verify your new email</h5>
<p class="text-secondary small text-center mb-4">
  We sent a 6-digit code to <strong>{{ $newEmail }}</strong>. It expires in 10 minutes.
</p>
@isset($hint)
<div class="alert border-0 rounded-3 mb-3 text-center" style="background:rgba(255,193,7,.1);color:#997404">
  <span class="small fw-semibold d-block mb-1">Dev mode — your code:</span>
  <span class="fw-bold" style="font-size:1.8rem;letter-spacing:.2em;font-family:monospace">{{ $hint }}</span>
</div>
@endisset
<form action="{{ route('oxalis.account.email.verify') }}" method="POST">
  @csrf
  <input type="text" name="code" inputmode="numeric" pattern="\d{6}" maxlength="6"
    class="form-control form-control-lg text-center rounded-3 @error('code') is-invalid @enderror mb-3"
    placeholder="000000" autofocus autocomplete="one-time-code"
    style="font-size:1.8rem;letter-spacing:.2em;font-family:monospace">
  @error('code')<div class="invalid-feedback text-center mb-2">{{ $message }}</div>@enderror
  <button class="btn btn-ox w-100">Confirm new email</button>
</form>
<hr class="my-3">
<p class="text-center small mb-0">
  Wrong address? <a href="{{ route('oxalis.account.email.show') }}">Try again</a>
</p>
@endsection
