@extends('oxalis::layouts.oxalis')
@section('title','Verify your email')
@section('content')
<div class="ox-icon" style="background:rgba(255,193,7,.1);color:#997404"><i class="bi bi-shield-exclamation"></i></div>
<h5 class="fw-bold text-center mb-2">Verify your email</h5>
<p class="text-secondary small text-center mb-3">Before continuing, please verify your email address by clicking the link we sent you.</p>
@isset($devLink)
<div class="alert border-0 rounded-3 mb-3 small" style="background:rgba(255,193,7,.1);color:#997404">
  <strong>Dev link:</strong><br><a href="{{ $devLink }}" class="text-break">{{ $devLink }}</a>
</div>
@endisset
<form action="{{ route('oxalis.email.send') }}" method="POST">
  @csrf
  <input type="hidden" name="email" value="{{ auth()->user()?->email }}">
  <button class="btn btn-ox w-100 mb-2">Resend verification email</button>
</form>
<p class="text-center small mb-0"><a href="{{ route('oxalis.login') }}">Sign out</a></p>
@endsection