@extends('oxalis::layouts.oxalis')
@section('title','Account security')
@section('content')
<h5 class="fw-bold mb-4">Account security</h5>

<h6 class="text-secondary small fw-semibold text-uppercase mb-2" style="letter-spacing:.06em">Sign-in methods</h6>
<div class="d-flex flex-wrap gap-2 mb-3">
  @php $count = $passkeys->count(); @endphp
  <span class="badge rounded-pill px-3 py-2 {{ $count > 0 ? '' : 'opacity-40' }}" style="background:var(--ox-sf,rgba(92,106,196,.12));color:var(--ox,#5c6ac4)">
    <i class="bi bi-fingerprint me-1"></i>{{ $count }} passkey{{ $count != 1 ? 's' : '' }}
  </span>
  <span class="badge rounded-pill px-3 py-2 {{ $totpEnabled ? '' : 'opacity-40' }}" style="background:{{ $totpEnabled ? 'rgba(25,135,84,.1)' : 'var(--bs-tertiary-bg,#f8f9fa)' }};color:{{ $totpEnabled ? '#198754' : 'var(--bs-secondary-color)' }}">
    <i class="bi bi-phone me-1"></i>TOTP {{ $totpEnabled ? 'active' : 'off' }}
  </span>
  @foreach($socialLogins as $s)
  <span class="badge rounded-pill px-3 py-2" style="background:var(--bs-tertiary-bg,#f8f9fa);color:var(--bs-body-color)"><i class="bi bi-{{ $s->provider == 'github' ? 'github' : 'google' }} me-1"></i>{{ ucfirst($s->provider) }}</span>
  @endforeach
</div>
<div class="d-flex gap-2 mb-4">
  <a href="{{ route('oxalis.passkeys.manage') }}" class="btn btn-sm btn-ox-out rounded-pill">Manage passkeys</a>
  <a href="{{ route('oxalis.totp.manage') }}" class="btn btn-sm btn-ox-out rounded-pill">Manage TOTP</a>
</div>

@if($recentEvents->count())
<h6 class="text-secondary small fw-semibold text-uppercase mb-2" style="letter-spacing:.06em">Recent activity</h6>
<div style="overflow-x:auto">
<table class="table table-sm" style="font-size:.78rem">
  <thead><tr><th class="text-secondary fw-normal">When</th><th class="text-secondary fw-normal">Method</th><th class="text-secondary fw-normal">IP</th><th class="text-secondary fw-normal">Status</th></tr></thead>
  <tbody>
    @foreach($recentEvents as $e)
    <tr>
      <td class="text-secondary">{{ $e->created_at->diffForHumans() }}</td>
      <td><span class="badge rounded-pill px-2" style="background:var(--ox-sf,rgba(92,106,196,.12));color:var(--ox,#5c6ac4)">{{ $e->method }}</span></td>
      <td class="text-secondary font-monospace">{{ $e->ip_address ?? '—' }}</td>
      <td><span class="{{ $e->status === 'success' ? 'text-success' : 'text-danger' }}">{{ $e->status }}</span></td>
    </tr>
    @endforeach
  </tbody>
</table>
</div>
@endif

@if($totpEnabled)
<h6 class="text-secondary small fw-semibold text-uppercase mt-3 mb-2" style="letter-spacing:.06em">Recovery</h6>
<p class="text-secondary small mb-2">Recovery codes let you access your account if you lose your authenticator app.</p>
<a href="{{ route('oxalis.recovery.show') }}" class="btn btn-sm btn-ox-out rounded-pill">Manage recovery codes</a>
@endif
@endsection