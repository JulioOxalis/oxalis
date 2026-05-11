@extends('oxalis::layouts.admin-panel')
@section('title','Dashboard')
@section('content')

{{-- KPI row --}}
<div class="row g-3 mb-2">
  @php
  $kpis=[
    ['icon'=>'bi-people-fill',      'val'=>number_format($totalUsers),    'label'=>'Total users',     'c'=>'var(--ox)',   'bg'=>'var(--ox-sf)'],
    ['icon'=>'bi-fingerprint',      'val'=>number_format($totalPasskeys), 'label'=>'Passkeys',        'c'=>'#818cf8',   'bg'=>'rgba(129,140,248,.12)'],
    ['icon'=>'bi-check-circle-fill','val'=>number_format($totalLogins),   'label'=>'Successful logins','c'=>'#34d399',  'bg'=>'rgba(52,211,153,.1)'],
    ['icon'=>'bi-x-circle-fill',    'val'=>number_format($totalFailed),   'label'=>'Failed attempts', 'c'=>'#f87171',   'bg'=>'rgba(248,113,113,.1)'],
    ['icon'=>'bi-ban',              'val'=>number_format($lockedNow),     'label'=>'IPs locked now',  'c'=>'#fcd34d',   'bg'=>'rgba(252,211,77,.08)'],
  ];
  @endphp
  @foreach($kpis as $k)
  <div class="col-6 col-md-4 col-lg">
    <div class="adm-card">
      <div class="adm-kpi">
        <div class="adm-kpi-icon" style="background:{{ $k['bg'] }};color:{{ $k['c'] }}"><i class="{{ $k['icon'] }}"></i></div>
        <div>
          <div class="adm-kpi-val" style="color:{{ $k['c'] }}">{{ $k['val'] }}</div>
          <div class="adm-kpi-label">{{ $k['label'] }}</div>
        </div>
      </div>
    </div>
  </div>
  @endforeach
</div>

{{-- Users --}}
<div class="adm-section">Users</div>

<form method="GET" action="{{ route('oxalis.admin') }}" class="adm-toolbar">
  <input type="text" name="search" class="adm-search" placeholder="Search name or email…" value="{{ $search }}" autocomplete="off">
  <div class="d-flex gap-1 flex-wrap">
    <a href="{{ route('oxalis.admin', array_merge(request()->except('filter','page'), ['filter'=>'all'])) }}"
       class="adm-filter-btn {{ $filter==='all' ? 'active' : '' }}">All</a>
    <a href="{{ route('oxalis.admin', array_merge(request()->except('filter','page'), ['filter'=>'passkey'])) }}"
       class="adm-filter-btn {{ $filter==='passkey' ? 'active' : '' }}"><i class="bi bi-fingerprint me-1"></i>Has passkey</a>
    <a href="{{ route('oxalis.admin', array_merge(request()->except('filter','page'), ['filter'=>'totp'])) }}"
       class="adm-filter-btn {{ $filter==='totp' ? 'active' : '' }}"><i class="bi bi-phone me-1"></i>TOTP on</a>
  </div>
  @if($search)
  <a href="{{ route('oxalis.admin') }}" class="adm-filter-btn" style="color:#f87171;border-color:rgba(248,113,113,.3)">
    <i class="bi bi-x me-1"></i>Clear
  </a>
  @endif
</form>

<div class="adm-table-wrap mb-3">
  <div style="overflow-x:auto">
  <table class="adm-table">
    <thead>
      <tr>
        <th style="padding-left:1.25rem">User</th>
        <th>Email</th>
        <th class="text-center">Passkeys</th>
        <th class="text-center">TOTP</th>
        <th class="d-none d-lg-table-cell">Last sign-in</th>
        <th class="d-none d-md-table-cell" style="padding-right:1.25rem">Registered</th>
      </tr>
    </thead>
    <tbody>
      @forelse($users as $u)
      @php
        $uid=(string)$u->getAuthIdentifier();
        $pkCount=$passkeyCounts[$uid]??0;
        $hasTotpOn=isset($totpEnabled[$uid]);
        $last=$lastLogin[$uid]??null;
      @endphp
      @if($filter==='passkey' && !$pkCount) @continue @endif
      @if($filter==='totp' && !$hasTotpOn) @continue @endif
      <tr>
        <td style="padding-left:1.25rem">
          <div class="d-flex align-items-center gap-2">
            <div style="width:30px;height:30px;border-radius:50%;background:var(--ox-sf);color:var(--ox);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.72rem;flex-shrink:0">
              {{ strtoupper(substr($u->name??$u->email??'?',0,1)) }}
            </div>
            <span style="font-weight:500;color:#e8ecf8">{{ $u->name ?? '—' }}</span>
          </div>
        </td>
        <td style="font-size:.75rem;color:var(--adm-muted)">{{ $u->email }}</td>
        <td class="text-center">
          @if($pkCount)
          <span class="adm-badge adm-badge-ox"><i class="bi bi-fingerprint"></i>{{ $pkCount }}</span>
          @else
          <span style="color:var(--adm-muted);font-size:.72rem">—</span>
          @endif
        </td>
        <td class="text-center">
          @if($hasTotpOn)
          <span class="adm-badge adm-badge-green"><i class="bi bi-shield-check"></i>On</span>
          @else
          <span style="color:var(--adm-muted);font-size:.72rem">Off</span>
          @endif
        </td>
        <td class="d-none d-lg-table-cell" style="color:var(--adm-muted);font-size:.75rem">
          {{ $last ? \Carbon\Carbon::parse($last)->diffForHumans() : 'Never' }}
        </td>
        <td class="d-none d-md-table-cell" style="color:var(--adm-muted);font-size:.75rem;padding-right:1.25rem">
          {{ $u->created_at?->diffForHumans() ?? '—' }}
        </td>
      </tr>
      @empty
      <tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--adm-muted)">No users found.</td></tr>
      @endforelse
    </tbody>
  </table>
  </div>
</div>

{{-- Pagination --}}
@if($users->hasPages())
<div class="d-flex align-items-center justify-content-between mb-2" style="font-size:.75rem;color:var(--adm-muted)">
  <span>Showing {{ $users->firstItem() }}–{{ $users->lastItem() }} of {{ number_format($users->total()) }} users</span>
  <div class="d-flex gap-1">
    @if($users->onFirstPage())
    <span class="adm-filter-btn" style="opacity:.4;cursor:default">&lsaquo; Prev</span>
    @else
    <a href="{{ $users->previousPageUrl() }}" class="adm-filter-btn">&lsaquo; Prev</a>
    @endif
    @if($users->hasMorePages())
    <a href="{{ $users->nextPageUrl() }}" class="adm-filter-btn">Next &rsaquo;</a>
    @else
    <span class="adm-filter-btn" style="opacity:.4;cursor:default">Next &rsaquo;</span>
    @endif
  </div>
</div>
@endif

{{-- Auth Events + Lockouts side by side --}}
<div class="row g-3 mt-1">
  <div class="col-12 col-xl-7">
    <div class="adm-section" id="events">Recent auth events</div>
    <div class="adm-table-wrap">
      <div style="overflow-x:auto">
      <table class="adm-table">
        <thead><tr>
          <th style="padding-left:1.25rem">When</th>
          <th>Method</th>
          <th class="d-none d-sm-table-cell">IP</th>
          <th style="padding-right:1.25rem;text-align:right">Status</th>
        </tr></thead>
        <tbody>
          @forelse($recentEvents as $e)
          <tr>
            <td style="padding-left:1.25rem;color:var(--adm-muted);white-space:nowrap">{{ $e->created_at->diffForHumans() }}</td>
            <td><span class="adm-badge adm-badge-ox">{{ $e->method }}</span></td>
            <td class="d-none d-sm-table-cell" style="font-family:monospace;font-size:.7rem;color:var(--adm-muted)">{{ $e->ip_address??'—' }}</td>
            <td style="padding-right:1.25rem;text-align:right">
              @if($e->status==='success')
              <span class="adm-badge adm-badge-green"><i class="bi bi-check-circle-fill"></i></span>
              @else
              <span class="adm-badge adm-badge-red"><i class="bi bi-x-circle-fill"></i></span>
              @endif
            </td>
          </tr>
          @empty
          <tr><td colspan="4" style="text-align:center;padding:1.5rem;color:var(--adm-muted)">No events yet.</td></tr>
          @endforelse
        </tbody>
      </table>
      </div>
    </div>
  </div>
  <div class="col-12 col-xl-5">
    <div class="adm-section" id="lockouts">Active lockouts</div>
    <div class="adm-table-wrap">
      <div style="overflow-x:auto">
      <table class="adm-table">
        <thead><tr>
          <th style="padding-left:1.25rem">Key</th>
          <th class="text-center">Hits</th>
          <th style="padding-right:1.25rem;text-align:right">Locked until</th>
        </tr></thead>
        <tbody>
          @forelse($recentLockouts as $l)
          <tr>
            <td style="padding-left:1.25rem;font-family:monospace;font-size:.68rem;color:var(--adm-muted);max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $l->key }}</td>
            <td class="text-center"><span class="adm-badge {{ $l->attempts>=5?'adm-badge-red':'adm-badge-gray' }}">{{ $l->attempts }}</span></td>
            <td style="padding-right:1.25rem;text-align:right;font-size:.73rem;color:var(--adm-muted)">
              {{ $l->locked_until ? $l->locked_until->diffForHumans() : '—' }}
            </td>
          </tr>
          @empty
          <tr><td colspan="3" style="text-align:center;padding:1.5rem;color:var(--adm-muted)">No lockouts.</td></tr>
          @endforelse
        </tbody>
      </table>
      </div>
    </div>
  </div>
</div>

@endsection
