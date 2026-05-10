@extends('oxalis::layouts.account')
@section('title','Auth Analytics')
@section('content')

<div class="d-flex align-items-center gap-3 mb-4">
    <div style="width:44px;height:44px;border-radius:12px;background:var(--ox-sf);display:flex;align-items:center;justify-content:center;color:var(--ox);font-size:1.2rem">
        <i class="bi bi-graph-up-arrow"></i>
    </div>
    <div>
        <h4 class="fw-bold mb-0" style="letter-spacing:-.02em">Auth Analytics</h4>
        <div class="text-secondary" style="font-size:.8rem">Powered by <code>oxalis_auth_events</code></div>
    </div>
</div>

{{-- KPI Cards --}}
<div class="row g-3 mb-5">
    @php
    $kpis = [
        ['icon'=>'bi-box-arrow-in-right','label'=>'Total sign-ins','value'=>number_format($totalLogins),'color'=>'var(--ox)','bg'=>'var(--ox-sf)'],
        ['icon'=>'bi-people-fill','label'=>'Unique users','value'=>number_format($totalUsers),'color'=>'#10b981','bg'=>'rgba(16,185,129,.12)'],
        ['icon'=>'bi-fingerprint','label'=>'Passkeys','value'=>number_format($totalPasskeys),'color'=>'#6366f1','bg'=>'rgba(99,102,241,.12)'],
        ['icon'=>'bi-phone-fill','label'=>'TOTP users','value'=>number_format($totpUsers),'color'=>'#f59e0b','bg'=>'rgba(245,158,11,.12)'],
        ['icon'=>'bi-check-circle-fill','label'=>'Success rate','value'=>$successRate.'%','color'=>'#10b981','bg'=>'rgba(16,185,129,.12)'],
        ['icon'=>'bi-x-circle','label'=>'Failed attempts','value'=>number_format($totalFailed),'color'=>'#ef4444','bg'=>'rgba(239,68,68,.1)'],
    ];
    @endphp
    @foreach($kpis as $k)
    <div class="col-6 col-md-4">
        <div class="ox-card h-100">
            <div class="d-flex align-items-center gap-3">
                <div style="width:40px;height:40px;border-radius:10px;background:{{ $k['bg'] }};display:flex;align-items:center;justify-content:center;color:{{ $k['color'] }};font-size:1.05rem;flex-shrink:0">
                    <i class="{{ $k['icon'] }}"></i>
                </div>
                <div>
                    <div class="fw-bold" style="font-size:1.4rem;letter-spacing:-.03em;color:{{ $k['color'] }}">{{ $k['value'] }}</div>
                    <div class="text-secondary" style="font-size:.75rem">{{ $k['label'] }}</div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="row g-4 mb-5">

    {{-- Method Breakdown --}}
    <div class="col-12 col-md-6">
        <div class="ox-section-label">By sign-in method</div>
        <div class="ox-card" style="padding:1.25rem">
            @forelse($methods as $m)
            @php
            $icons = ['passkey'=>'bi-fingerprint','email_otp'=>'bi-123','magic_link'=>'bi-envelope-paper','totp'=>'bi-phone','password'=>'bi-lock','password+totp'=>'bi-lock','google'=>'bi-google','github'=>'bi-github'];
            $icon = $icons[$m->method] ?? 'bi-person';
            $pct = round($m->total / $methodMax * 100);
            @endphp
            <div class="d-flex align-items-center gap-3 mb-3">
                <div style="width:32px;height:32px;border-radius:8px;background:var(--ox-sf);color:var(--ox);display:flex;align-items:center;justify-content:center;font-size:.85rem;flex-shrink:0">
                    <i class="{{ $icon }}"></i>
                </div>
                <div class="flex-grow-1" style="min-width:0">
                    <div class="d-flex justify-content-between mb-1">
                        <span style="font-size:.8rem;font-weight:500">{{ $m->method }}</span>
                        <span style="font-size:.78rem;color:var(--bs-secondary-color)">{{ number_format($m->total) }}</span>
                    </div>
                    <div style="height:6px;background:var(--bs-tertiary-bg,#f0f0f0);border-radius:50rem;overflow:hidden">
                        <div style="width:{{ $pct }}%;height:100%;background:var(--ox);border-radius:50rem;transition:width .6s ease"></div>
                    </div>
                </div>
            </div>
            @empty
            <p class="text-secondary text-center py-3 mb-0" style="font-size:.85rem">No auth events yet.</p>
            @endforelse
        </div>
    </div>

    {{-- 7-day chart --}}
    <div class="col-12 col-md-6">
        <div class="ox-section-label">Last 7 days</div>
        <div class="ox-card d-flex flex-column" style="padding:1.25rem;height:100%;min-height:180px">
            <div class="d-flex align-items-end gap-2 flex-grow-1" style="height:140px">
                @foreach($days as $date => $count)
                @php $h = $dayMax > 0 ? max(4, round($count / $dayMax * 100)) : 4; @endphp
                <div class="d-flex flex-column align-items-center flex-grow-1 h-100 justify-content-end gap-1">
                    <div style="font-size:.65rem;color:var(--bs-secondary-color)">{{ $count ?: '' }}</div>
                    <div style="width:100%;height:{{ $h }}%;background:var(--ox);border-radius:4px 4px 0 0;min-height:4px;opacity:{{ $count > 0 ? 1 : 0.2 }};transition:height .5s ease"></div>
                    <div style="font-size:.62rem;color:var(--bs-secondary-color);white-space:nowrap">{{ \Carbon\Carbon::parse($date)->format('D') }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- Recent events --}}
<div class="ox-section-label">Recent events</div>
<div class="ox-card" style="padding:0">
    <div style="overflow-x:auto;border-radius:var(--ox-r)">
    <table class="table table-sm table-hover mb-0" style="font-size:.78rem">
        <thead>
            <tr style="border-bottom:1px solid var(--bs-border-color)">
                <th class="ps-4 py-3 text-secondary fw-normal">When</th>
                <th class="py-3 text-secondary fw-normal">User</th>
                <th class="py-3 text-secondary fw-normal">Method</th>
                <th class="py-3 text-secondary fw-normal d-none d-sm-table-cell">IP</th>
                <th class="pe-4 py-3 text-secondary fw-normal text-end">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recent as $e)
            <tr>
                <td class="ps-4 py-3 align-middle text-secondary">{{ $e->created_at->diffForHumans() }}</td>
                <td class="py-3 align-middle font-monospace" style="font-size:.72rem">{{ substr((string)$e->user_id, 0, 8) }}…</td>
                <td class="py-3 align-middle">
                    <span class="badge rounded-pill px-3 py-2 fw-normal" style="background:var(--ox-sf);color:var(--ox)">{{ $e->method }}</span>
                </td>
                <td class="py-3 align-middle text-secondary font-monospace d-none d-sm-table-cell" style="font-size:.72rem">{{ $e->ip_address ?? '—' }}</td>
                <td class="pe-4 py-3 align-middle text-end">
                    @if($e->status === 'success')
                    <span class="text-success"><i class="bi bi-check-circle-fill"></i></span>
                    @else
                    <span class="text-danger"><i class="bi bi-x-circle-fill"></i></span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center py-4 text-secondary">No events yet.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>

@endsection
