@extends('oxalis::layouts.oxalis')
@section('title','Sign in with password')
@section('content')
<h5 class="fw-bold text-center mb-4">Sign in with password</h5>
@if(session('lockout_seconds'))
<div class="alert border-0 rounded-3 mb-3 small" style="background:rgba(220,53,69,.1);color:#dc3545">
  <i class="bi bi-lock me-1"></i>Too many attempts. Try again in <strong id="cd">{{ session('lockout_seconds') }}</strong>s.
</div>
@endif
<form action="{{ route('oxalis.password.login') }}" method="POST">
  @csrf
  <div class="form-floating mb-3">
    <input type="email" name="email" class="form-control rounded-3 @error('email') is-invalid @enderror" placeholder="e" value="{{ old('email') }}" autofocus>
    <label>Email address</label>
    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <div class="mb-3">
    <div class="d-flex justify-content-between align-items-center mb-1">
      <small class="text-secondary">Password</small>
      <a href="{{ route('oxalis.password.forgot') }}" class="small">Forgot?</a>
    </div>
    <input type="password" name="password" class="form-control rounded-3 @error('password') is-invalid @enderror" placeholder="Password">
    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <div class="form-check mb-4">
    <input class="form-check-input" type="checkbox" name="remember" id="rem">
    <label class="form-check-label small text-secondary" for="rem">Remember me for 30 days</label>
  </div>
  <button class="btn btn-ox w-100">Sign in</button>
</form>
<hr class="my-3">
<p class="text-center small mb-0"><a href="{{ route('oxalis.login') }}">← Other sign-in options</a></p>
@endsection
@push('scripts')
@if(session('lockout_seconds'))
<script>let s={{ session('lockout_seconds') }};const el=document.getElementById('cd');const t=setInterval(()=>{el.textContent=--s;if(s<=0)clearInterval(t);},1000);</script>
@endif
@endpush