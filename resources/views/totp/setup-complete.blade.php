@extends('oxalis::layouts.oxalis')
@section('title','Authenticator enabled')
@section('content')
<div class="ox-icon" style="background:rgba(25,135,84,.1);color:#198754"><i class="bi bi-shield-check"></i></div>
<h5 class="fw-bold text-center mb-2">Authenticator app enabled</h5>
<p class="text-secondary small text-center mb-3">Two-factor authentication is now active on your account.</p>
@isset($recoveryCodes)
<div class="alert border-0 rounded-3 mb-3 small" style="background:rgba(255,193,7,.1);color:#997404">
  <i class="bi bi-exclamation-triangle me-1"></i><strong>Save your recovery codes</strong> — store them somewhere safe.
</div>
<div class="row g-2 mb-3">
  @foreach($recoveryCodes as $code)
  <div class="col-6"><code class="d-block p-2 rounded-3 text-center fw-bold small" style="background:var(--bs-tertiary-bg,#f8f9fa);border:1px solid var(--bs-border-color);cursor:pointer" onclick="navigator.clipboard.writeText('{{ $code }}')">{{ $code }}</code></div>
  @endforeach
</div>
@endisset
<a href="{{ config('oxalis.routes.home','/dashboard') }}" class="btn btn-ox w-100">Continue</a>
@endsection