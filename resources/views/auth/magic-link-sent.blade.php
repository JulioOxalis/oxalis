@extends('oxalis::layouts.oxalis')
@section('title','Check your email')
@section('content')
<div class="ox-icon" style="background:rgba(25,135,84,.1);color:#198754"><i class="bi bi-envelope-check"></i></div>
<h5 class="fw-bold text-center mb-2">Magic link sent</h5>
<p class="text-secondary small text-center mb-0">
  Check <strong>{{ $email }}</strong> for your sign-in link. It expires in {{ config('oxalis.magic_link.expires_in',15) }} minutes.
</p>
@isset($devLink)
<div class="alert border-0 rounded-3 mt-3 small" style="background:rgba(255,193,7,.1);color:#997404">
  <strong>Dev link:</strong><br><a href="{{ $devLink }}" class="text-break">{{ $devLink }}</a>
</div>
@endisset
<div class="text-center mt-4">
  <a href="{{ route('oxalis.login') }}" class="btn btn-ox-out">Back to sign in</a>
</div>
@endsection