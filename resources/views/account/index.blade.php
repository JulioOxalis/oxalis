@extends('oxalis::layouts.account')
@section('title','Account settings')
@section('content')
@php $m = config('oxalis.methods', []); @endphp

{{-- Credential breach warning (shown once after password login, if HIBP match) --}}
@if(session()->pull('oxalis_breach_detected', false))
<div class="alert border-0 rounded-3 mb-4 d-flex align-items-start gap-3" style="background:rgba(220,53,69,.08);color:#dc3545">
    <i class="bi bi-exclamation-triangle-fill fs-5 mt-1 flex-shrink-0"></i>
    <div>
        <div class="fw-semibold mb-1">Your password has appeared in a data breach</div>
        <div style="font-size:.83rem">This password was found in a public breach database. It is strongly recommended that you change it immediately or switch to a passkey.</div>
        <div class="d-flex gap-2 mt-2 flex-wrap">
            @if(config('oxalis.methods.passkey',true))
            <a href="{{ route('oxalis.passkeys.enroll') }}" class="btn btn-sm rounded-pill px-3 fw-semibold" style="background:#dc3545;color:#fff;border:none">Set up passkey →</a>
            @endif
            <a href="{{ route('oxalis.password.forgot') }}" class="btn btn-sm rounded-pill px-3" style="border:1.5px solid #dc3545;color:#dc3545">Change password</a>
        </div>
    </div>
</div>
@endif

{{-- Passkey nudge banner (shown when user has no passkeys, dismissible) --}}
@if(($m['passkey'] ?? true) && $passkeys->count() === 0)
<div id="ox-nudge" class="d-none alert border-0 rounded-3 mb-4 d-flex align-items-start gap-3" style="background:var(--ox-sf);color:var(--bs-body-color)">
    <i class="bi bi-fingerprint fs-4 mt-1 flex-shrink-0" style="color:var(--ox)"></i>
    <div class="flex-grow-1">
        <div class="fw-semibold mb-1">Go passwordless with a passkey</div>
        <div class="text-secondary" style="font-size:.82rem">Sign in with your fingerprint, face, or device PIN — no password needed. Phishing-resistant and faster.</div>
        <a href="{{ route('oxalis.passkeys.enroll') }}" class="btn btn-sm btn-ox mt-2 px-3">Set up passkey →</a>
    </div>
    <button id="ox-nudge-dismiss" class="btn-close btn-close-sm ms-auto" title="Dismiss" style="filter:none;opacity:.5"></button>
</div>
<script>
(function(){
  const max = {{ config('oxalis.passkey_nudge_max', 3) }};
  if (max === 0) return;
  const count = parseInt(localStorage.getItem('ox-nudge-dismissed') || '0');
  if (count < max) {
    const nudge = document.getElementById('ox-nudge');
    if (nudge) nudge.classList.remove('d-none');
    document.getElementById('ox-nudge-dismiss')?.addEventListener('click', function(){
      localStorage.setItem('ox-nudge-dismissed', count + 1);
      nudge.style.transition='opacity .3s';nudge.style.opacity='0';
      setTimeout(()=>nudge.classList.add('d-none'), 300);
    });
  }
})();
</script>
@endif

{{-- Passkey health warning (1 passkey = no backup) --}}
@if(($m['passkey'] ?? true) && $passkeys->count() === 1)
<div class="alert border-0 rounded-3 mb-4 d-flex align-items-center gap-3" style="background:rgba(255,193,7,.1);color:#997404">
    <i class="bi bi-exclamation-triangle-fill fs-5 flex-shrink-0"></i>
    <div style="font-size:.84rem">
        <strong>Only one passkey enrolled.</strong> If you lose this device you'll be locked out.
        <a href="{{ route('oxalis.passkeys.enroll') }}" class="fw-semibold ms-1" style="color:#997404">Add a backup →</a>
    </div>
</div>
@endif

{{-- Profile header --}}
<div class="d-flex align-items-center gap-4 mb-5 pb-4" style="border-bottom:1px solid var(--bs-border-color)">
    <div class="ox-avatar">{{ strtoupper(substr(auth()->user()->name ?? auth()->user()->email ?? '?', 0, 1)) }}</div>
    <div>
        <h4 class="fw-bold mb-0" style="letter-spacing:-.02em">{{ auth()->user()->name ?? 'User' }}</h4>
        <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
            <span class="text-secondary" style="font-size:.875rem">{{ auth()->user()->email }}</span>
            <a href="{{ route('oxalis.account.email.show') }}" class="badge rounded-pill text-decoration-none" style="background:var(--ox-sf);color:var(--ox);font-size:.68rem;font-weight:500;padding:.25rem .65rem">
                <i class="bi bi-pencil me-1"></i>Change
            </a>
        </div>
    </div>
</div>

{{-- Sign-in methods --}}
<div class="ox-section-label">Sign-in methods</div>
<div class="row g-3 mb-5">

    @if($m['passkey'] ?? true)
    <div class="col-12 col-sm-6 col-lg-4">
        <div class="ox-method-card">
            <div class="ox-method-icon"><i class="bi bi-fingerprint"></i></div>
            <div class="fw-semibold mb-1">Passkeys</div>
            <div class="mb-3">
                @if($passkeys->count())
                <span class="ox-status-pill" style="background:var(--ox-sf);color:var(--ox)">
                    <i class="bi bi-check-circle-fill"></i>{{ $passkeys->count() }} key{{ $passkeys->count()!=1?'s':'' }}
                </span>
                @else
                <span class="ox-status-pill" style="background:var(--bs-tertiary-bg,#f8f9fa);color:var(--bs-secondary-color)">
                    <i class="bi bi-dash-circle"></i>None yet
                </span>
                @endif
            </div>
            <a href="{{ route('oxalis.passkeys.enroll') }}" class="btn btn-sm btn-ox-out">Manage →</a>
        </div>
    </div>
    @endif

    @if($m['totp'] ?? true)
    <div class="col-12 col-sm-6 col-lg-4">
        <div class="ox-method-card">
            <div class="ox-method-icon"><i class="bi bi-phone"></i></div>
            <div class="fw-semibold mb-1">Authenticator app</div>
            <div class="mb-3">
                @if($totpEnabled)
                <span class="ox-status-pill" style="background:rgba(25,135,84,.1);color:#198754">
                    <i class="bi bi-shield-fill-check"></i>Enabled
                </span>
                @else
                <span class="ox-status-pill" style="background:var(--bs-tertiary-bg,#f8f9fa);color:var(--bs-secondary-color)">
                    <i class="bi bi-shield-slash"></i>Not set up
                </span>
                @endif
            </div>
            @if($totpEnabled)
            <a href="{{ route('oxalis.totp.manage') }}" class="btn btn-sm btn-ox-out">Manage →</a>
            @else
            <a href="{{ route('oxalis.totp.setup') }}" class="btn btn-sm btn-ox">Set up →</a>
            @endif
        </div>
    </div>
    @endif

    @if($m['password'] ?? true)
    <div class="col-12 col-sm-6 col-lg-4">
        <div class="ox-method-card">
            <div class="ox-method-icon"><i class="bi bi-lock-fill"></i></div>
            <div class="fw-semibold mb-1">Password</div>
            <div class="mb-3">
                <span class="ox-status-pill" style="background:var(--ox-sf);color:var(--ox)">
                    <i class="bi bi-check-circle-fill"></i>Active
                </span>
            </div>
            <a href="{{ route('oxalis.password.forgot') }}" class="btn btn-sm btn-ox-out">Change →</a>
        </div>
    </div>
    @endif

    @if($m['magic_link'] ?? true)
    <div class="col-12 col-sm-6 col-lg-4">
        <div class="ox-method-card">
            <div class="ox-method-icon"><i class="bi bi-envelope-paper-fill"></i></div>
            <div class="fw-semibold mb-1">Magic link</div>
            <div class="mb-3">
                <span class="ox-status-pill" style="background:var(--ox-sf);color:var(--ox)">
                    <i class="bi bi-check-circle-fill"></i>Active
                </span>
            </div>
            <span class="text-secondary" style="font-size:.78rem">No setup needed</span>
        </div>
    </div>
    @endif

    @if($m['email_otp'] ?? true)
    <div class="col-12 col-sm-6 col-lg-4">
        <div class="ox-method-card">
            <div class="ox-method-icon"><i class="bi bi-123"></i></div>
            <div class="fw-semibold mb-1">One-time code</div>
            <div class="mb-3">
                <span class="ox-status-pill" style="background:var(--ox-sf);color:var(--ox)">
                    <i class="bi bi-check-circle-fill"></i>Active
                </span>
            </div>
            <span class="text-secondary" style="font-size:.78rem">No setup needed</span>
        </div>
    </div>
    @endif

    @foreach($socialLogins as $s)
    <div class="col-12 col-sm-6 col-lg-4">
        <div class="ox-method-card">
            <div class="ox-method-icon"><i class="bi bi-{{ $s->provider==='github'?'github':'google' }}"></i></div>
            <div class="fw-semibold mb-1">{{ ucfirst($s->provider) }}</div>
            <div class="mb-3">
                <span class="ox-status-pill" style="background:rgba(25,135,84,.1);color:#198754">
                    <i class="bi bi-check-circle-fill"></i>Connected
                </span>
            </div>
            <span class="text-secondary" style="font-size:.78rem">Linked account</span>
        </div>
    </div>
    @endforeach

</div>

{{-- Inline passkey management --}}
@if(($m['passkey'] ?? true) && $passkeys->count())
<div class="ox-section-label">Your passkeys</div>
<div class="ox-card mb-5">
    @foreach($passkeys as $pk)
    <div class="ox-passkey-row">
        <div class="d-flex align-items-center justify-content-center rounded-3" style="width:38px;height:38px;background:var(--ox-sf);color:var(--ox);flex-shrink:0;font-size:1rem">
            <i class="bi bi-key-fill"></i>
        </div>
        <div class="flex-grow-1 min-width-0">
            <form action="{{ route('oxalis.passkeys.rename') }}" method="POST" class="d-flex gap-2 align-items-center">
                @csrf
                <input type="hidden" name="id" value="{{ $pk->id }}">
                <input type="text" name="label" class="form-control form-control-sm rounded-pill" value="{{ $pk->label }}" style="max-width:200px">
                <button type="submit" class="btn btn-sm btn-ox-out rounded-pill px-3">Save</button>
            </form>
            <div class="text-secondary mt-1" style="font-size:.72rem">
                Added {{ $pk->created_at->diffForHumans() }}@if($pk->last_used_at) &nbsp;·&nbsp; Used {{ $pk->last_used_at->diffForHumans() }}@endif
            </div>
        </div>
        <form action="{{ route('oxalis.passkeys.delete') }}" method="POST" class="ms-auto">
            @csrf
            <input type="hidden" name="id" value="{{ $pk->id }}">
            <button type="submit" class="btn btn-sm rounded-pill px-3" style="border:1.5px solid #dc3545;color:#dc3545" onclick="return confirm('Remove this passkey?')">
                <i class="bi bi-trash3"></i>
            </button>
        </form>
    </div>
    @endforeach
</div>
@endif

{{-- Recovery codes --}}
@if(($m['passkey'] ?? true) && config('oxalis.passkey_recovery.enabled', true))
<div class="ox-section-label">Passkey recovery</div>
<div class="ox-card mb-5 d-flex align-items-center gap-3 flex-wrap" style="gap:1rem!important">
    <div class="d-flex align-items-center justify-content-center rounded-3" style="width:42px;height:42px;background:rgba(255,193,7,.1);color:#997404;font-size:1.1rem;flex-shrink:0">
        <i class="bi bi-life-preserver"></i>
    </div>
    <div class="flex-grow-1">
        <div class="fw-semibold">Passkey recovery codes</div>
        <div class="text-secondary" style="font-size:.78rem">{{ $passkeyRecoveryCount ?? 0 }} active one-time code{{ ($passkeyRecoveryCount ?? 0) === 1 ? '' : 's' }} if you lose every passkey</div>
    </div>
    <a href="{{ route('oxalis.passkeys.recovery') }}" class="btn btn-sm btn-ox-out text-nowrap">Manage codes →</a>
</div>
@endif

@if(($m['totp'] ?? true) && $totpEnabled)
<div class="ox-section-label">Recovery</div>
<div class="ox-card mb-5 d-flex align-items-center gap-3 flex-wrap" style="gap:1rem!important">
    <div class="d-flex align-items-center justify-content-center rounded-3" style="width:42px;height:42px;background:rgba(255,193,7,.1);color:#997404;font-size:1.1rem;flex-shrink:0">
        <i class="bi bi-shield-lock-fill"></i>
    </div>
    <div class="flex-grow-1">
        <div class="fw-semibold">Recovery codes</div>
        <div class="text-secondary" style="font-size:.78rem">Backup access if you lose your authenticator app</div>
    </div>
    <a href="{{ route('oxalis.recovery.show') }}" class="btn btn-sm btn-ox-out text-nowrap">View codes →</a>
</div>
@endif

{{-- Recent sign-ins --}}
@if($recentEvents->count())
<div class="ox-section-label">Recent sign-ins</div>
<div class="ox-card" style="padding:0">
    <div style="overflow-x:auto;border-radius:var(--ox-r)">
    <table class="table table-sm table-hover mb-0" style="font-size:.78rem">
        <thead>
            <tr style="border-bottom:1px solid var(--bs-border-color)">
                <th class="text-secondary fw-normal ps-4 py-3" style="width:130px">When</th>
                <th class="text-secondary fw-normal py-3">Method</th>
                <th class="text-secondary fw-normal py-3 d-none d-sm-table-cell">IP address</th>
                <th class="text-secondary fw-normal pe-4 py-3 text-end">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($recentEvents as $e)
            <tr>
                <td class="text-secondary ps-4 py-3 align-middle">{{ $e->created_at->diffForHumans() }}</td>
                <td class="py-3 align-middle">
                    <span class="badge rounded-pill px-3 py-2 fw-normal" style="background:var(--ox-sf);color:var(--ox)">{{ $e->method }}</span>
                </td>
                <td class="text-secondary font-monospace py-3 align-middle d-none d-sm-table-cell" style="font-size:.72rem">{{ $e->ip_address ?? '—' }}</td>
                <td class="pe-4 py-3 align-middle text-end">
                    @if($e->status === 'success')
                    <span class="text-success"><i class="bi bi-check-circle-fill"></i></span>
                    @else
                    <span class="text-danger"><i class="bi bi-x-circle-fill"></i></span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
</div>
@endif

{{-- Active sessions --}}
<div class="ox-section-label">Security</div>
<div class="ox-card mb-5 d-flex align-items-center gap-3 flex-wrap">
    <div class="d-flex align-items-center justify-content-center rounded-3" style="width:42px;height:42px;background:var(--ox-sf);color:var(--ox);font-size:1.1rem;flex-shrink:0">
        <i class="bi bi-phone-laptop"></i>
    </div>
    <div class="flex-grow-1">
        <div class="fw-semibold">Active sessions</div>
        <div class="text-secondary" style="font-size:.78rem">See all devices currently signed in and revoke any you don't recognise</div>
    </div>
    <a href="{{ route('oxalis.sessions') }}" class="btn btn-sm btn-ox-out text-nowrap">Manage →</a>
</div>

{{-- Danger zone --}}
@if(config('oxalis.account_deletion.enabled', true))
<div class="ox-section-label mt-5" style="color:#dc3545">Danger zone</div>
<div class="ox-card" style="border-color:rgba(220,53,69,.3)">
    <div class="d-flex align-items-start gap-3 flex-wrap">
        <div class="flex-grow-1">
            <div class="fw-semibold" style="color:#dc3545">Delete account</div>
            <div class="text-secondary" style="font-size:.8rem;margin-top:.25rem">
                Permanently deletes your account and all associated data. This cannot be undone.
            </div>
        </div>
        <button class="btn btn-sm rounded-pill text-nowrap" style="border:1.5px solid #dc3545;color:#dc3545"
            data-bs-toggle="collapse" data-bs-target="#delete-form">
            Delete my account
        </button>
    </div>
    <div class="collapse mt-4" id="delete-form">
        <form action="{{ route('oxalis.account.delete') }}" method="POST"
            onsubmit="return confirm('This is permanent. Your account and all data will be deleted.')">
            @csrf
            <p class="text-secondary small mb-2">Type your email address to confirm:</p>
            <div class="d-flex gap-2 align-items-center flex-wrap">
                <input type="email" name="confirm_email"
                    class="form-control rounded-3 @error('confirm_email') is-invalid @enderror"
                    placeholder="{{ auth()->user()->email }}" style="max-width:260px">
                @error('confirm_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <button type="submit" class="btn btn-sm rounded-pill px-4"
                    style="background:#dc3545;color:#fff;border:none;font-weight:500">
                    Confirm deletion
                </button>
            </div>
        </form>
    </div>
</div>
@endif

@endsection
