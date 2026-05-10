@extends('oxalis::layouts.oxalis')
@section('title','Set up a passkey')
@section('content')
<div class="ox-icon"><i class="bi bi-fingerprint"></i></div>
<h5 class="fw-bold text-center mb-1">Set up a passkey</h5>
<p class="text-secondary small text-center mb-4">Use your fingerprint, Face ID, or device PIN — no password needed.</p>

@if(config('oxalis.rp_id'))
<div id="rpid-warn" class="alert border-0 rounded-3 small mb-3 d-none" style="background:rgba(255,193,7,.12);color:#997404">
  <i class="bi bi-exclamation-triangle me-1"></i>
  Origin mismatch — server expects <code>{{ config('oxalis.rp_id') }}</code> but your browser is on <code id="actual-host"></code>.<br>
  Visit <a href="{{ (config('oxalis.origins')[0]??'http://localhost').'/oxalis/passkeys/enroll' }}" class="fw-semibold">the correct URL</a> or update <code>OXALIS_ORIGINS</code> in <code>.env</code>.
</div>
@endif

<div class="mb-3">
  <input type="text" id="pk-label" class="form-control rounded-3" placeholder="Passkey name (e.g. My iPhone)" value="My Passkey">
</div>
<button id="btn-enroll" class="btn btn-ox w-100 d-flex align-items-center justify-content-center gap-2">
  <i class="bi bi-shield-check fs-5"></i> Create passkey
</button>
<div id="enroll-err" class="alert border-0 rounded-3 small d-none mt-3" style="background:rgba(220,53,69,.1);color:#dc3545"></div>
<p class="text-secondary small text-center mt-3 mb-0">
  <i class="bi bi-phone me-1"></i>No passkey on this device? Your browser will offer to use your phone via QR code.
</p>
@endsection
@push('scripts')
<script>
(function(){
  const rp='{!! config('oxalis.rp_id','localhost') !!}',h=window.location.hostname;
  if(h!==rp){const w=document.getElementById('rpid-warn');if(w){document.getElementById('actual-host').textContent=h;w.classList.remove('d-none');document.getElementById('btn-enroll').disabled=true;}}
})();
document.getElementById('btn-enroll').addEventListener('click',async()=>{
  const btn=document.getElementById('btn-enroll');
  btn.disabled=true;btn.innerHTML='<span class="spinner-border spinner-border-sm"></span> Waiting for biometrics…';
  document.getElementById('enroll-err').classList.add('d-none');
  const toB=s=>Uint8Array.from(atob(s.replace(/-/g,'+').replace(/_/g,'/')),c=>c.charCodeAt(0));
  const toS=b=>btoa(String.fromCharCode(...new Uint8Array(b))).replace(/\+/g,'-').replace(/\//g,'_').replace(/=/g,'');
  const label=document.getElementById('pk-label').value||'My Passkey';
  try{
    const r1=await fetch('{{ route('oxalis.passkeys.register.begin') }}',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify({label})});
    const o=await r1.json();
    if(o.error){showErr(o.error);reset();return;}
    console.log('[oxalis] rp.id=',o.rp?.id,'| hostname=',window.location.hostname);
    if(o.rp?.id&&o.rp.id!==window.location.hostname){showErr(`RP ID mismatch: server says "${o.rp.id}" but browser is on "${window.location.hostname}". Visit http://${o.rp.id}/oxalis/passkeys/enroll`);reset();return;}
    o.challenge=toB(o.challenge);o.user.id=toB(o.user.id);
    if(o.excludeCredentials)o.excludeCredentials=o.excludeCredentials.map(c=>({...c,id:toB(c.id)}));
    const cred=await navigator.credentials.create({publicKey:o});
    const r2=await fetch('{{ route('oxalis.passkeys.register.finish') }}',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify({id:cred.id,rawId:toS(cred.rawId),type:cred.type,response:{clientDataJSON:toS(cred.response.clientDataJSON),attestationObject:toS(cred.response.attestationObject)}})});
    const d=await r2.json();
    if(d.message)window.location.href='{{ config('oxalis.routes.home','/dashboard') }}';
    else{showErr(d.error||'Server rejected the passkey. Ensure OXALIS_RP_ID and OXALIS_ORIGINS match your URL.');reset();}
  }catch(e){showErr(friendlyErr(e));reset();}
});
function friendlyErr(e){
  const m=(e.message||'').toLowerCase();
  if(m.includes('timed out')||m.includes('not allowed')||m.includes('operation either'))
    return 'Passkey creation was cancelled or timed out. Please click "Create passkey" and complete the browser prompt without waiting.';
  if(m.includes('not supported')||m.includes('authenticatorselection'))
    return 'Your browser or device does not support passkeys. Try Chrome, Edge 114+, or Safari 16+.';
  if(m.includes('security')||m.includes('invalid domain'))
    return 'Security check failed — make sure you are visiting: '+window.location.origin+'/oxalis/passkeys/enroll';
  if(m.includes('excludecredentials'))
    return 'A passkey already exists for this account on this device.';
  return 'Could not create passkey ('+e.name+'). Please try again or use another sign-in method.';
}
function showErr(m){const e=document.getElementById('enroll-err');e.textContent=m;e.classList.remove('d-none');}
function reset(){const b=document.getElementById('btn-enroll');b.disabled=false;b.innerHTML='<i class="bi bi-shield-check fs-5"></i> Try again';}
</script>
@endpush