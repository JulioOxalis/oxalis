@extends('oxalis::layouts.account')
@section('title','Active sessions')
@section('content')

<div class="d-flex align-items-center gap-3 mb-4">
    <div style="width:44px;height:44px;border-radius:12px;background:var(--ox-sf);display:flex;align-items:center;justify-content:center;color:var(--ox);font-size:1.2rem">
        <i class="bi bi-phone-laptop"></i>
    </div>
    <div>
        <h4 class="fw-bold mb-0">Active sessions</h4>
        <div class="text-secondary" style="font-size:.8rem">All devices currently signed in to your account</div>
    </div>
</div>

<div class="ox-section-label">Signed-in devices</div>
<div class="ox-card mb-4" style="padding:0">
    @forelse($sessions as $s)
    <div class="d-flex align-items-center gap-3 px-4 py-3" style="border-bottom:1px solid var(--bs-border-color)">
        <div style="width:38px;height:38px;border-radius:10px;background:{{ $s['is_current'] ? 'var(--ox-sf)' : 'var(--bs-tertiary-bg,#f8f9fa)' }};color:{{ $s['is_current'] ? 'var(--ox)' : 'var(--bs-secondary-color)' }};display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0">
            <i class="bi bi-{{ str_contains($s['label'],'iPhone')||str_contains($s['label'],'Android') ? 'phone' : (str_contains($s['label'],'Mac')||str_contains($s['label'],'Windows') ? 'laptop' : 'display') }}"></i>
        </div>
        <div class="flex-grow-1">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="fw-medium" style="font-size:.875rem">{{ $s['label'] }}</span>
                @if($s['is_current'])
                <span class="badge rounded-pill px-2 py-1" style="background:rgba(16,185,129,.1);color:#198754;font-size:.65rem">Current session</span>
                @endif
            </div>
            <div class="text-secondary" style="font-size:.72rem;margin-top:.15rem">
                <span class="font-monospace">{{ $s['ip'] }}</span>
                &nbsp;·&nbsp; {{ ucfirst($s['method']) }}
                &nbsp;·&nbsp; Active {{ $s['last_active'] }}
            </div>
        </div>
        <form action="{{ route('oxalis.sessions.revoke') }}" method="POST">
            @csrf
            <input type="hidden" name="session_id" value="{{ $s['id'] }}">
            <button type="submit"
                class="btn btn-sm rounded-pill px-3"
                style="border:1.5px solid #dc3545;color:#dc3545;font-size:.75rem"
                onclick="return confirm('{{ $s['is_current'] ? 'This will sign you out. Continue?' : 'Revoke this session?' }}')">
                {{ $s['is_current'] ? 'Sign out' : 'Revoke' }}
            </button>
        </form>
    </div>
    @empty
    <div class="text-center text-secondary py-4" style="font-size:.85rem">No active sessions recorded.</div>
    @endforelse
</div>

@if($sessions->where('is_current', false)->count() > 0)
<div class="text-center">
    <form action="{{ route('oxalis.sessions.revoke-all') }}" method="POST"
        onsubmit="return confirm('This will sign out all devices including this one.')">
        @csrf
        <button type="submit" class="btn btn-sm rounded-pill px-4"
            style="border:1.5px solid #dc3545;color:#dc3545">
            <i class="bi bi-box-arrow-right me-1"></i>Sign out all devices
        </button>
    </form>
</div>
@endif

<hr class="my-4">
<p class="text-center small mb-0"><a href="{{ route('oxalis.account') }}">← Back to account</a></p>
@endsection
