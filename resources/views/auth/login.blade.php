@extends('oxalis::layouts.oxalis')
@section('title','Sign in')
@section('content')
@php
  $m           = config('oxalis.methods', []);
  $passkeyOnly = config('oxalis.passkey_only', false);
  $hasGoogle   = ($m['social'] ?? false) && config('oxalis.social.google.enabled');
  $hasGithub   = ($m['social'] ?? false) && config('oxalis.social.github.enabled');
  $hasSocial   = $hasGoogle || $hasGithub;

  $methods = [];
  if ($m['passkey']    ?? true)  $methods[] = 'passkey';
  if (!$passkeyOnly) {
      if ($m['password']   ?? true)  $methods[] = 'password';
      if ($m['magic_link'] ?? true)  $methods[] = 'magic_link';
      if ($m['email_otp']  ?? true)  $methods[] = 'email_otp';
      if ($hasSocial)                $methods[] = 'social';
  }
  $primaryMethod = $methods[0] ?? 'passkey';
  $showRail      = count($methods) > 1;
  $emailMethods  = ['passkey', 'password', 'magic_link', 'email_otp'];
  $needsEmail    = count(array_intersect($methods, $emailMethods)) > 0;
@endphp

<style>
/* ── Login-page styles ──────────────────────────────────────────────────── */
.ox-method-rail{display:flex;justify-content:center;gap:.45rem;flex-wrap:wrap;margin-top:.25rem}
.ox-rail-btn{
  width:46px;height:46px;border-radius:var(--ox-r,50rem);
  border:1.5px solid var(--bs-border-color);background:transparent;
  color:var(--bs-secondary-color);display:inline-flex;align-items:center;
  justify-content:center;font-size:1.05rem;cursor:pointer;
  transition:border-color .18s,color .18s,background .18s,transform .15s;
  position:relative;
}
.ox-rail-btn:hover,.ox-rail-btn.active{
  border-color:var(--ox);color:var(--ox);background:var(--ox-sf);transform:translateY(-2px);
}
.ox-rail-btn:active{transform:translateY(0);}
[data-ox-theme=neon] .ox-rail-btn.active{box-shadow:0 0 14px rgba(0,245,255,.3);}
[data-ox-theme=obsidian] .ox-rail-btn{border-radius:0;}

/* panel fade-in */
.ox-panel{animation:ox-in .22s ease both}
.ox-panel.ox-instant{animation:none}
@keyframes ox-in{from{opacity:0;transform:translateY(5px)}to{opacity:1;transform:translateY(0)}}

/* security tier badges */
.ox-tier{
  display:inline-flex;align-items:center;gap:.28rem;
  font-size:.67rem;font-weight:700;letter-spacing:.04em;
  padding:.18rem .55rem;border-radius:50rem;white-space:nowrap;
}
.ox-tier-best{background:rgba(25,135,84,.1);color:#198754;}
.ox-tier-good{background:rgba(13,110,253,.1);color:#0d6efd;}
.ox-tier-basic{background:rgba(255,193,7,.14);color:#997404;}
.ox-hint{font-size:.73rem;color:var(--bs-secondary-color);}

/* skeleton loader */
.ox-skel{display:flex;flex-direction:column;gap:.4rem;}
.ox-skel-bar{
  height:44px;border-radius:var(--ox-btn-radius,50rem);
  background:linear-gradient(90deg,
    var(--bs-tertiary-bg,rgba(0,0,0,.06)) 25%,
    var(--ox-sf) 50%,
    var(--bs-tertiary-bg,rgba(0,0,0,.06)) 75%);
  background-size:200% 100%;animation:ox-shimmer 1.3s infinite;
}
.ox-skel-bar.sm{height:16px;width:55%;margin:0 auto;border-radius:50rem;}
@keyframes ox-shimmer{0%{background-position:200% center}100%{background-position:-200% center}}

/* footer meta row */
.ox-meta-row{display:flex;align-items:center;justify-content:space-between;gap:.5rem;margin-top:.4rem;}
</style>

<h5 class="fw-bold mb-1 text-center">Welcome back</h5>
<p class="text-secondary small text-center mb-4">Sign in to {{ config('app.name') }}</p>

{{-- RP-ID mismatch warning --}}
@if($m['passkey'] ?? true)
<div id="rpid-warn" class="alert border-0 rounded-3 small mb-3 d-none" style="background:rgba(255,193,7,.1);color:#997404">
  <i class="bi bi-exclamation-triangle me-1"></i>
  Origin mismatch — server expects <code>{{ config('oxalis.rp_id') }}</code> but you are on <code id="actual-host"></code>.
  <br>Visit <a href="{{ (config('oxalis.origins')[0]??'http://localhost').'/'.config('oxalis.routes.prefix','oxalis').'/login' }}" class="fw-semibold">the correct URL</a> or set <code>OXALIS_ORIGINS</code>.
</div>
@endif

{{-- Shared email field --}}
@if($needsEmail)
<div class="form-floating mb-3" id="ox-email-zone">
  <input type="email" id="main-email" class="form-control rounded-3"
         placeholder="e" autocomplete="username webauthn"
         value="{{ old('email') }}">
  <label>Email address</label>
</div>
@endif

{{-- ─── Method panels ─────────────────────────────────────────────────── --}}
<div id="ox-panels">

  @if($m['passkey'] ?? true)
  {{-- Passkey panel --}}
  <div class="ox-panel ox-instant" id="panel-passkey" data-needs-email="1">
    <div id="pk-skel" class="ox-skel d-none mb-2">
      <div class="ox-skel-bar"></div><div class="ox-skel-bar sm"></div>
    </div>
    <button id="btn-pk" class="btn btn-ox w-100 d-flex align-items-center justify-content-center gap-2" style="min-height:44px">
      <i id="pk-icon" class="bi bi-fingerprint fs-5"></i>
      <span>Sign in with Passkey</span>
    </button>
    <div id="pk-err" class="alert border-0 rounded-3 small d-none mt-2 py-2 mb-0" style="background:rgba(220,53,69,.1);color:#dc3545"></div>
    <div class="ox-meta-row">
      <span class="ox-hint" id="pk-hint">Use your biometric or security key</span>
      <span class="ox-tier ox-tier-best"><i class="bi bi-shield-fill-check"></i>Most secure</span>
    </div>
  </div>
  @endif

  @if(!$passkeyOnly && ($m['password'] ?? true))
  {{-- Password panel (inline) --}}
  <div class="ox-panel d-none" id="panel-password" data-needs-email="1">
    <form action="{{ route('oxalis.password.login') }}" method="POST" id="pwd-form">
      @csrf
      <input type="hidden" name="email" id="pwd-email">
      <div class="form-floating mb-2">
        <input type="password" name="password" class="form-control rounded-3 @error('password') is-invalid @enderror"
               placeholder="p" id="pwd-input" autocomplete="current-password">
        <label>Password</label>
        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="form-check mb-0">
          <input class="form-check-input" type="checkbox" name="remember" id="pwd-rem">
          <label class="form-check-label small text-secondary" for="pwd-rem">Remember me</label>
        </div>
        <a href="{{ route('oxalis.password.forgot') }}" class="small">Forgot password?</a>
      </div>
      <button type="submit" class="btn btn-ox w-100">Sign in</button>
    </form>
    <div class="ox-meta-row">
      <span class="ox-hint">Classic email &amp; password</span>
      <span class="ox-tier ox-tier-basic"><i class="bi bi-lock"></i>Basic</span>
    </div>
  </div>
  @endif

  @if(!$passkeyOnly && ($m['magic_link'] ?? true))
  {{-- Magic link panel --}}
  <div class="ox-panel d-none" id="panel-magic_link" data-needs-email="1">
    <form action="{{ route('oxalis.magic-link.send') }}" method="POST" id="magic-form">
      @csrf
      <input type="hidden" name="email" id="magic-email">
      <button type="submit" class="btn btn-ox w-100">Send magic link</button>
    </form>
    <div class="ox-meta-row">
      <span class="ox-hint">One-click link emailed to you, valid 15 min</span>
      <span class="ox-tier ox-tier-good"><i class="bi bi-envelope-fill"></i>Secure</span>
    </div>
  </div>
  @endif

  @if(!$passkeyOnly && ($m['email_otp'] ?? true))
  {{-- OTP panel --}}
  <div class="ox-panel d-none" id="panel-email_otp" data-needs-email="1">
    <form action="{{ route('oxalis.otp.send') }}" method="POST" id="otp-form">
      @csrf
      <input type="hidden" name="email" id="otp-email">
      <button type="submit" class="btn btn-ox w-100">Send one-time code</button>
    </form>
    <div class="ox-meta-row">
      <span class="ox-hint">6-digit code emailed, valid 5 min</span>
      <span class="ox-tier ox-tier-good"><i class="bi bi-grid-3x3-gap-fill"></i>Secure</span>
    </div>
  </div>
  @endif

  @if(!$passkeyOnly && $hasSocial)
  {{-- Social panel --}}
  <div class="ox-panel d-none" id="panel-social">
    @if($hasGoogle)
    <a href="{{ route('oxalis.social.redirect','google') }}" class="btn btn-ox-out w-100 mb-2 d-flex align-items-center justify-content-center gap-2">
      <svg width="16" height="16" viewBox="0 0 48 48" aria-hidden="true">
        <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
        <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
        <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
        <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
      </svg>
      Continue with Google
    </a>
    @endif
    @if($hasGithub)
    <a href="{{ route('oxalis.social.redirect','github') }}" class="btn btn-dark w-100 d-flex align-items-center justify-content-center gap-2" style="border-radius:var(--ox-btn-radius,50rem)">
      <i class="bi bi-github"></i> Continue with GitHub
    </a>
    @endif
    <div class="ox-meta-row justify-content-end">
      <span class="ox-tier ox-tier-good"><i class="bi bi-shield-fill-check"></i>Secure</span>
    </div>
  </div>
  @endif

</div>
{{-- ──────────────────────────────────────────────────────────────────────── --}}

{{-- Icon rail — only when 2+ methods --}}
@if($showRail)
<div class="ox-div">or continue with</div>
<div class="ox-method-rail" id="ox-rail" role="group" aria-label="Sign-in methods">
  @if($m['passkey'] ?? true)
  <button class="ox-rail-btn" data-method="passkey" title="Passkey" aria-label="Sign in with Passkey">
    <i class="bi bi-fingerprint"></i>
  </button>
  @endif
  @if(!$passkeyOnly)
    @if($m['password'] ?? true)
    <button class="ox-rail-btn" data-method="password" title="Password" aria-label="Password">
      <i class="bi bi-lock-fill"></i>
    </button>
    @endif
    @if($m['magic_link'] ?? true)
    <button class="ox-rail-btn" data-method="magic_link" title="Magic link" aria-label="Magic link">
      <i class="bi bi-send-fill"></i>
    </button>
    @endif
    @if($m['email_otp'] ?? true)
    <button class="ox-rail-btn" data-method="email_otp" title="One-time code" aria-label="One-time code">
      <i class="bi bi-grid-3x3-gap-fill"></i>
    </button>
    @endif
    @if($hasSocial)
    <button class="ox-rail-btn" data-method="social"
      title="{{ $hasGoogle && $hasGithub ? 'Google / GitHub' : ($hasGoogle ? 'Google' : 'GitHub') }}"
      aria-label="Social login">
      <i class="bi bi-{{ $hasGoogle ? 'google' : 'github' }}"></i>
    </button>
    @endif
  @endif
</div>
@endif

@if(($m['totp'] ?? true) && session('oxalis_totp_pending_user_id'))
<p class="text-center small text-secondary mt-3 mb-0">
  Have an authenticator code? <a href="{{ route('oxalis.totp.verify.show') }}">Enter it →</a>
</p>
@endif

@if(config('oxalis.smart_dispatch'))
<p class="text-center small mt-2 mb-0"><a href="{{ route('oxalis.dispatch') }}">✦ Smart sign-in</a></p>
@endif

<hr class="my-3">
<p class="text-center small mb-0 text-secondary">No account? <a href="{{ route('oxalis.register') }}" class="fw-semibold">Create one</a></p>

@endsection

@push('scripts')
<script>
(function(){
'use strict';
const LAST_METHOD_KEY = 'ox-last-method';

// ── Platform-aware passkey icon + hint ─────────────────────────────────────
const ua = navigator.userAgent;
let pkIcon = 'bi-fingerprint', pkHint = 'Use your biometric or security key';
if (/iPhone|iPad/.test(ua))    { pkIcon = 'bi-phone-fill';  pkHint = 'Use Face ID or Touch ID'; }
else if (/Macintosh/.test(ua)) { pkIcon = 'bi-fingerprint'; pkHint = 'Use Touch ID or your password manager'; }
else if (/Windows/.test(ua))   { pkIcon = 'bi-windows';     pkHint = 'Use Windows Hello'; }
else if (/Android/.test(ua))   { pkIcon = 'bi-phone-fill';  pkHint = 'Use your screen lock or fingerprint'; }
const pkIconEl = document.getElementById('pk-icon');
const pkHintEl = document.getElementById('pk-hint');
if (pkIconEl) pkIconEl.className = 'bi ' + pkIcon + ' fs-5';
if (pkHintEl) pkHintEl.textContent = pkHint;

// ── RP-ID origin check ─────────────────────────────────────────────────────
const rp = '{!! config('oxalis.rp_id','localhost') !!}', h = window.location.hostname;
if (h !== rp) {
  const w = document.getElementById('rpid-warn');
  const ah = document.getElementById('actual-host');
  if (ah) ah.textContent = h;
  if (w) w.classList.remove('d-none');
  const pkBtn = document.getElementById('btn-pk');
  if (pkBtn) pkBtn.disabled = true;
}

// ── Method switching ───────────────────────────────────────────────────────
function activateMethod(method, animate) {
  document.querySelectorAll('#ox-panels .ox-panel').forEach(p => p.classList.add('d-none'));
  document.querySelectorAll('.ox-rail-btn').forEach(b => b.classList.remove('active'));

  const panel = document.getElementById('panel-' + method);
  if (panel) {
    panel.classList.remove('d-none');
    panel.classList.toggle('ox-instant', !animate);
  }

  const emailZone = document.getElementById('ox-email-zone');
  if (emailZone) emailZone.classList.toggle('d-none', !(panel && panel.dataset.needsEmail === '1'));

  const railBtn = document.querySelector('.ox-rail-btn[data-method="' + method + '"]');
  if (railBtn) railBtn.classList.add('active');

  syncEmail();
  localStorage.setItem(LAST_METHOD_KEY, method);
}

function syncEmail() {
  const val = (document.getElementById('main-email') || {}).value || '';
  ['pwd-email','magic-email','otp-email'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.value = val;
  });
}

document.querySelectorAll('.ox-rail-btn').forEach(b => {
  b.addEventListener('click', () => activateMethod(b.dataset.method, true));
});
document.getElementById('main-email')?.addEventListener('input', syncEmail);
document.getElementById('pwd-form')?.addEventListener('submit', syncEmail);

// ── Restore last-used method or fall back to default ───────────────────────
const remembered = localStorage.getItem(LAST_METHOD_KEY);
const validPanel  = remembered && !!document.getElementById('panel-' + remembered);
@if($errors->has('password'))
activateMethod('password', false);
@else
activateMethod(validPanel ? remembered : '{{ $primaryMethod }}', false);
@endif

// ── Passkey sign-in ────────────────────────────────────────────────────────
const toB = s => Uint8Array.from(atob(s.replace(/-/g,'+').replace(/_/g,'/')), c => c.charCodeAt(0));
const toS = b => btoa(String.fromCharCode(...new Uint8Array(b))).replace(/\+/g,'-').replace(/\//g,'_').replace(/=/g,'');

function pkSkeleton(on) {
  const btn = document.getElementById('btn-pk');
  const skel = document.getElementById('pk-skel');
  if (on) { btn?.classList.add('d-none'); skel?.classList.remove('d-none'); }
  else { btn?.classList.remove('d-none'); if (btn) btn.disabled = false; skel?.classList.add('d-none'); }
}
function showErr(id, msg) { const e=document.getElementById(id); if(e){e.textContent=msg;e.classList.remove('d-none');} }
function hideErr(id) { document.getElementById(id)?.classList.add('d-none'); }

function pkFriendlyErr(e) {
  const m = (e.message||'').toLowerCase();
  if (m.includes('timed out')||m.includes('not allowed')||m.includes('operation either'))
    return 'Cancelled or timed out — complete the browser prompt quickly and try again.';
  if (m.includes('security')||m.includes('invalid domain'))
    return 'Security error — make sure you are on the correct URL: ' + window.location.hostname;
  return 'Passkey unavailable. Try another method, or: ' + e.name;
}

document.getElementById('btn-pk')?.addEventListener('click', async () => {
  const email = (document.getElementById('main-email')||{}).value?.trim();
  if (!email) { showErr('pk-err','Enter your email first.'); return; }
  pkSkeleton(true); hideErr('pk-err');
  try {
    const r1 = await fetch('{{ route('oxalis.passkeys.login.begin') }}',{
      method:'POST',
      headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
      body:JSON.stringify({email})
    });
    const o = await r1.json();
    if (o.error) { showErr('pk-err',o.error); pkSkeleton(false); return; }
    o.challenge = toB(o.challenge);
    if (o.allowCredentials) o.allowCredentials = o.allowCredentials.map(c=>({...c,id:toB(c.id)}));

    const cred = await navigator.credentials.get({publicKey:o});

    const r2 = await fetch('{{ route('oxalis.passkeys.login.finish') }}',{
      method:'POST',
      headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
      body:JSON.stringify({
        id:cred.id, rawId:toS(cred.rawId), type:cred.type,
        response:{
          clientDataJSON:toS(cred.response.clientDataJSON),
          authenticatorData:toS(cred.response.authenticatorData),
          signature:toS(cred.response.signature),
          userHandle:cred.response.userHandle?toS(cred.response.userHandle):null
        }
      })
    });
    const d = await r2.json();
    if (d.redirect) { localStorage.setItem(LAST_METHOD_KEY,'passkey'); window.location.href=d.redirect; }
    else { showErr('pk-err', d.error||'Authentication failed.'); pkSkeleton(false); }
  } catch(e) { showErr('pk-err', pkFriendlyErr(e)); pkSkeleton(false); }
});

// ── Conditional UI (passkey autofill dropdown) ─────────────────────────────
(async () => {
  try {
    if (!window.PublicKeyCredential?.isConditionalMediationAvailable) return;
    if (!await PublicKeyCredential.isConditionalMediationAvailable()) return;
    const r = await fetch('{{ route('oxalis.passkeys.login.autofill') }}',{
      method:'POST',
      headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
      body:'{}'
    });
    const o = await r.json(); if (o.error) return;
    o.challenge = toB(o.challenge);
    if (o.allowCredentials) o.allowCredentials = o.allowCredentials.map(c=>({...c,id:toB(c.id)}));
    const cred = await navigator.credentials.get({publicKey:o, mediation:'conditional'});
    if (!cred) return;
    const r2 = await fetch('{{ route('oxalis.passkeys.login.finish') }}',{
      method:'POST',
      headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
      body:JSON.stringify({
        id:cred.id, rawId:toS(cred.rawId), type:cred.type,
        response:{
          clientDataJSON:toS(cred.response.clientDataJSON),
          authenticatorData:toS(cred.response.authenticatorData),
          signature:toS(cred.response.signature),
          userHandle:cred.response.userHandle?toS(cred.response.userHandle):null
        }
      })
    });
    const d = await r2.json();
    if (d.redirect) window.location.href = d.redirect;
  } catch(e) { /* conditional UI silently ignored */ }
})();

})();
</script>
@endpush
