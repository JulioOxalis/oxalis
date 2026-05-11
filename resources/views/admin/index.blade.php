@extends('oxalis::layouts.account')
@section('title','Oxalis Admin')
@section('content')

<div class="d-flex align-items-center gap-3 mb-4">
    <div style="width:44px;height:44px;border-radius:12px;background:var(--ox-sf);display:flex;align-items:center;justify-content:center;color:var(--ox);font-size:1.2rem">
        <i class="bi bi-shield-fill-check"></i>
    </div>
    <div>
        <h4 class="fw-bold mb-0">Oxalis Admin</h4>
        <div class="text-secondary" style="font-size:.8rem">Authentication overview for {{ config('app.name') }}</div>
    </div>
</div>

{{-- KPI row --}}
<div class="row g-3 mb-5">
    @php
    $kpis = [
        ['icon'=>'bi-people-fill',        'label'=>'Total users',    'value'=>number_format($users->total()), 'color'=>'var(--ox)',  'bg'=>'var(--ox-sf)'],
        ['icon'=>'bi-box-arrow-in-right',  'label'=>'Total sign-ins', 'value'=>number_format($totalLogins),   'color'=>'#10b981',   'bg'=>'rgba(16,185,129,.12)'],
        ['icon'=>'bi-x-circle-fill',       'label'=>'Failed attempts','value'=>number_format($totalFailed),   'color'=>'#ef4444',   'bg'=>'rgba(239,68,68,.1)'],
        ['icon'=>'bi-ban',                 'label'=>'Currently locked','value'=>number_format($lockedNow),    'color'=>'#f59e0b',   'bg'=>'rgba(245,158,11,.12)'],
    ];
    @endphp
    @foreach($kpis as $k)
    <div class="col-6 col-md-3">
        <div class="ox-card h-100">
            <div class="d-flex align-items-center gap-3">
                <div style="width:40px;height:40px;border-radius:10px;background:{{ $k['bg'] }};display:flex;align-items:center;justify-content:center;color:{{ $k['color'] }};font-size:1.05rem;flex-shrink:0">
                    <i class="{{ $k['icon'] }}"></i>
                </div>
                <div>
                    <div class="fw-bold" style="font-size:1.35rem;letter-spacing:-.02em;color:{{ $k['color'] }}">{{ $k['value'] }}</div>
                    <div class="text-secondary" style="font-size:.72rem">{{ $k['label'] }}</div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Users table --}}
<div class="ox-section-label">Users</div>
<div class="ox-card mb-5" style="padding:0">
    <div style="overflow-x:auto;border-radius:var(--ox-r)">
    <table class="table table-sm table-hover mb-0" style="font-size:.78rem">
        <thead>
            <tr style="border-bottom:1px solid var(--bs-border-color)">
                <th class="ps-4 py-3 text-secondary fw-normal">Name</th>
                <th class="py-3 text-secondary fw-normal">Email</th>
                <th class="py-3 text-secondary fw-normal text-center">Passkeys</th>
                <th class="py-3 text-secondary fw-normal text-center">TOTP</th>
                <th class="py-3 text-secondary fw-normal d-none d-md-table-cell">Last sign-in</th>
                <th class="pe-4 py-3 text-secondary fw-normal">Registered</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $u)
            @php $uid = (string) $u->getAuthIdentifier(); @endphp
            <tr>
                <td class="ps-4 py-3 align-middle fw-medium">{{ $u->name ?? '—' }}</td>
                <td class="py-3 align-middle" style="font-size:.75rem">{{ $u->email }}</td>
                <td class="py-3 align-middle text-center">
                    @if($passkeyCounts[$uid] ?? 0)
                    <span style="color:var(--ox)">{{ $passkeyCounts[$uid] }}</span>
                    @else
                    <span class="text-secondary">—</span>
                    @endif
                </td>
                <td class="py-3 align-middle text-center">
                    @if(isset($totpEnabled[$uid]))
                    <i class="bi bi-check-circle-fill text-success"></i>
                    @else
                    <span class="text-secondary" style="font-size:.7rem">off</span>
                    @endif
                </td>
                <td class="py-3 align-middle text-secondary d-none d-md-table-cell">
                    {{ isset($lastLogin[$uid]) ? \Carbon\Carbon::parse($lastLogin[$uid])->diffForHumans() : 'Never' }}
                </td>
                <td class="pe-4 py-3 align-middle text-secondary">{{ $u->created_at?->diffForHumans() ?? '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center py-4 text-secondary">No users yet.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
@if($users->hasPages())
<div class="d-flex justify-content-center mb-5">{{ $users->links() }}</div>
@endif

{{-- Recent events --}}
<div class="row g-4">
<div class="col-12 col-md-6">
    <div class="ox-section-label">Recent sign-ins</div>
    <div class="ox-card" style="padding:0">
        <div style="overflow-x:auto;border-radius:var(--ox-r)">
        <table class="table table-sm mb-0" style="font-size:.75rem">
            <thead><tr style="border-bottom:1px solid var(--bs-border-color)">
                <th class="ps-3 py-3 text-secondary fw-normal">When</th>
                <th class="py-3 text-secondary fw-normal">Method</th>
                <th class="pe-3 py-3 text-secondary fw-normal text-end">Status</th>
            </tr></thead>
            <tbody>
                @foreach($recentEvents as $e)
                <tr>
                    <td class="ps-3 py-2 align-middle text-secondary">{{ $e->created_at->diffForHumans() }}</td>
                    <td class="py-2 align-middle">
                        <span class="badge rounded-pill px-2 fw-normal" style="background:var(--ox-sf);color:var(--ox)">{{ $e->method }}</span>
                    </td>
                    <td class="pe-3 py-2 align-middle text-end">
                        @if($e->status==='success')
                        <i class="bi bi-check-circle-fill text-success"></i>
                        @else
                        <i class="bi bi-x-circle-fill text-danger"></i>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>
</div>
<div class="col-12 col-md-6">
    <div class="ox-section-label">Recent lockouts</div>
    <div class="ox-card" style="padding:0">
        <div style="overflow-x:auto;border-radius:var(--ox-r)">
        <table class="table table-sm mb-0" style="font-size:.75rem">
            <thead><tr style="border-bottom:1px solid var(--bs-border-color)">
                <th class="ps-3 py-3 text-secondary fw-normal">Key</th>
                <th class="py-3 text-secondary fw-normal text-center">Attempts</th>
                <th class="pe-3 py-3 text-secondary fw-normal text-end">Locked until</th>
            </tr></thead>
            <tbody>
                @forelse($recentLockouts as $l)
                <tr>
                    <td class="ps-3 py-2 align-middle font-monospace text-secondary" style="font-size:.68rem;max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $l->key }}</td>
                    <td class="py-2 align-middle text-center">
                        <span style="color:{{ $l->attempts >= 5 ? '#ef4444' : 'var(--bs-body-color)' }}">{{ $l->attempts }}</span>
                    </td>
                    <td class="pe-3 py-2 align-middle text-end text-secondary">
                        {{ $l->locked_until ? $l->locked_until->diffForHumans() : '—' }}
                    </td>
                </tr>
                @empty
                <tr><td colspan="3" class="text-center py-3 text-secondary">No lockouts.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>
</div>

@endsection
