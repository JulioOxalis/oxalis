@extends('oxalis::layouts.oxalis')
@section('title','Sign in')
@section('content')
@php $m = config('oxalis.methods',[]); @endphp

<h5 class="fw-bold mb-1 text-center">Welcome back</h5>
<p class="text-secondary small text-center mb-4">Sign in to {{ config('app.name') }}</p>

@if(($m['social']??false))
  @if(config('oxalis.social.google.enabled'))
  <a href="{{ route('oxalis.social.redirect','google') }}" class="btn btn-ox-out w-100 mb-2 d-flex align-items-center justify-content-center gap-2">
    <svg width="17" height="17" viewBox="0 0 48 48"><path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/><path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/><path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/><path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/></svg>
    Continue with Google
  </a>
  @endif
  @if(config('oxalis.social.github.enabled'))
  <a href="{{ route('oxalis.social.redirect','github') }}" class="btn btn-dark w-100 mb-2 rounded-pill d-flex align-items-center justify-content-center gap-2">
    <i class="bi bi-github"></i> Continue with GitHub
  </a>
  @endif
  <div class="ox-div">or</div>
@endif

@if($m['passkey']??true)
<div id="rpid-warn" class="alert border-0 rounded-3 small mb-3 d-none" style="background:rgba(255,193,7,.12);color:#997404">
  <i class="bi bi-exclamation-triangle me-1"></i>
  Origin mismatch — server expects <code>{{ config('oxalis.rp_id') }}</code> but you are on <code id="actual-host"></code>.
  <br>Visit <a href="{{ (config('oxalis.origins')[0]??'http://localhost').'/oxalis/login' }}" class="fw-semibold">the correct URL</a> or update <code>OXALIS_ORIGINS</code>.
</div>
<div class="form-floating mb-2">
  <input type="email" id="pk-email" class="form-control rounded-3" placeholder="e" autocomplete="username webauthn">
  <label>Email address</label>
</div>
<button id="btn-pk" class="btn btn-ox w-100 mb-2 d-flex align-items-center justify-content-center gap-2">
  <i class="bi bi-fingerprint fs-5"></i> Sign in with Passkey
</button>
<div id="pk-err" class="alert border-0 rounded-3 small d-none py-2 mb-1" style="background:rgba(220,53,69,.1);color:#dc3545"></div>
@endif

@if(($m['passkey']??true) && (($m['magic_link']??true)||($m['email_otp']??true)||($m['password']??true)))
<div class="ox-div">or</div>
@endif

@if($m['magic_link']??true)
<form action="{{ route('oxalis.magic-link.send') }}" method="POST" class="mb-2">@csrf
  <div class="input-group">
    <div class="form-floating flex-grow-1">
      <input type="email" name="email" class="form-control" style="border-radius:var(--ox-r) 0 0 var(--ox-r)" placeholder="e" value="{{ old('email') }}">
      <label class="small">Send me a sign-in link</label>
    </div>
    <button class="btn btn-ox px-3" style="border-radius:0 var(--ox-r) var(--ox-r) 0"><i class="bi bi-send"></i></button>
  </div>
</form>
@endif

@if($m['email_otp']??true)
<form action="{{ route('oxalis.otp.send') }}" method="POST" class="mb-2">@csrf
  <div class="input-group">
    <div class="form-floating flex-grow-1">
      <input type="email" name="email" class="form-control" style="border-radius:var(--ox-r) 0 0 var(--ox-r)" placeholder="e" value="{{ old('email') }}">
      <label class="small">Email me a one-time code</label>
    </div>
    <button class="btn btn-ox-out px-3" style="border-radius:0 var(--ox-r) var(--ox-r) 0"><i class="bi bi-envelope"></i></button>
  </div>
</form>
@endif

@if($m['password']??true)
<a href="{{ route('oxalis.password.login.show') }}" class="btn btn-ox-out w-100 mb-1 mt-1">
  <i class="bi bi-lock me-2"></i>Sign in with password
</a>
@endif

@if(($m['totp']??true) && session('oxalis_totp_pending_user_id'))
<p class="text-center small text-secondary mt-2 mb-0">Authenticator app? <a href="{{ route('oxalis.totp.verify.show') }}">Enter code →</a></p>
@endif

@if(config('oxalis.smart_dispatch'))
<p class="text-center small mt-2 mb-0"><a href="{{ route('oxalis.dispatch') }}">✦ Smart sign-in</a></p>
@endif

<hr class="my-3">
<p class="text-center small mb-0 text-secondary">No account? <a href="{{ route('oxalis.register') }}" class="fw-semibold">Create one</a></p>
@endsection

@if($m['passkey']??true)
@push('scripts')
<script>
(function(){
  const rp='{!! config('oxalis.rp_id','localhost') !!}',h=window.location.hostname;
  if(h!==rp){const w=document.getElementById('rpid-warn');document.getElementById('actual-host').textContent=h;if(w)w.classList.remove('d-none');const b=document.getElementById('btn-pk');if(b)b.disabled=true;}
})();
document.getElementById('btn-pk')?.addEventListener('click',async()=>{
  const email=document.getElementById('pk-email').value.trim();
  if(!email){showErr('Enter your email first.');return;}
  const btn=document.getElementById('btn-pk');
  btn.disabled=true;btn.innerHTML='<span class="spinner-border spinner-border-sm"></span>';hideErr();
  const toB=s=>Uint8Array.from(atob(s.replace(/-/g,'+').replace(/_/g,'/')),c=>c.charCodeAt(0));
  const toS=b=>btoa(String.fromCharCode(...new Uint8Array(b))).replace(/\+/g,'-').replace(/\//g,'_').replace(/=/g,'');
  try{
    const r1=await fetch('{{ route('oxalis.passkeys.login.begin') }}',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify({email})});
    const o=await r1.json();if(o.error){showErr(o.error);reset();return;}
    o.challenge=toB(o.challenge);
    if(o.allowCredentials)o.allowCredentials=o.allowCredentials.map(c=>({...c,id:toB(c.id)}));
    const cred=await navigator.credentials.get({publicKey:o});
    const r2=await fetch('{{ route('oxalis.passkeys.login.finish') }}',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify({id:cred.id,rawId:toS(cred.rawId),type:cred.type,response:{clientDataJSON:toS(cred.response.clientDataJSON),authenticatorData:toS(cred.response.authenticatorData),signature:toS(cred.response.signature),userHandle:cred.response.userHandle?toS(cred.response.userHandle):null}})});
    const d=await r2.json();
    if(d.redirect)window.location.href=d.redirect;else{showErr(d.error||'Authentication failed.');reset();}
  }catch(e){showErr(friendlyErr(e));reset();}
});
function friendlyErr(e){
  const m=(e.message||'').toLowerCase();
  if(m.includes('timed out')||m.includes('not allowed')||m.includes('operation either'))
    return 'Sign-in cancelled or timed out. Please try again and complete the browser prompt quickly.';
  if(m.includes('security')||m.includes('invalid domain'))
    return 'Security error — check you are on the right URL: '+window.location.hostname;
  return 'Passkey unavailable. Try another method below, or '+e.name+'.';
}
function showErr(m){const e=document.getElementById('pk-err');e.textContent=m;e.classList.remove('d-none');}
function hideErr(){document.getElementById('pk-err').classList.add('d-none');}
function reset(){const b=document.getElementById('btn-pk');b.disabled=false;b.innerHTML='<i class="bi bi-fingerprint fs-5"></i> Sign in with Passkey';}
// ── Conditional UI (autofill passkey) ─────────────────────────────────────────
// Starts a background discoverable credential request so the browser can offer
// passkeys in the autofill dropdown when the user focuses the email field.
(async()=>{
  try{
    if(!window.PublicKeyCredential?.isConditionalMediationAvailable)return;
    const ok=await PublicKeyCredential.isConditionalMediationAvailable();
    if(!ok)return;
    const toB=s=>Uint8Array.from(atob(s.replace(/-/g,'+').replace(/_/g,'/')),c=>c.charCodeAt(0));
    const toS=b=>btoa(String.fromCharCode(...new Uint8Array(b))).replace(/\+/g,'-').replace(/\//g,'_').replace(/=/g,'');
    const r=await fetch('{{ route('oxalis.passkeys.login.autofill') }}',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:'{}' });
    const o=await r.json();if(o.error)return;
    o.challenge=toB(o.challenge);
    if(o.allowCredentials)o.allowCredentials=o.allowCredentials.map(c=>({...c,id:toB(c.id)}));
    const cred=await navigator.credentials.get({publicKey:o,mediation:'conditional'});
    if(!cred)return;
    const r2=await fetch('{{ route('oxalis.passkeys.login.finish') }}',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify({id:cred.id,rawId:toS(cred.rawId),type:cred.type,response:{clientDataJSON:toS(cred.response.clientDataJSON),authenticatorData:toS(cred.response.authenticatorData),signature:toS(cred.response.signature),userHandle:cred.response.userHandle?toS(cred.response.userHandle):null}})});
    const d=await r2.json();
    if(d.redirect)window.location.href=d.redirect;
  }catch(e){/* conditional UI silently ignored — user can still click the button */}
})();
</script>
@endpush
@endif