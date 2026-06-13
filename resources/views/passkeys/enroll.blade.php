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
  Visit <a href="{{ route('oxalis.passkeys.enroll') }}" class="fw-semibold">the correct URL</a> or update <code>OXALIS_ORIGINS</code> in <code>.env</code>.
</div>
@endif

<div class="mb-3">
  <input type="text" id="pk-label" class="form-control rounded-3" placeholder="Passkey name (e.g. My iPhone)" value="My Passkey">
</div>
<div class="mb-3">
  <div class="small fw-semibold mb-2">Create it with</div>
  <div class="d-grid gap-2">
    <input class="btn-check" type="radio" name="authenticator_attachment" id="pk-platform" value="platform" checked>
    <label class="btn btn-ox-out rounded-3 text-start p-3" for="pk-platform">
      <span class="d-flex align-items-center gap-2 fw-semibold"><i class="bi bi-laptop"></i>This device or password manager</span>
      <span class="d-block text-secondary small mt-1">Windows Hello, Touch ID, Face ID, iCloud Keychain, or Google Password Manager.</span>
    </label>

    <input class="btn-check" type="radio" name="authenticator_attachment" id="pk-cross-platform" value="cross-platform">
    <label class="btn btn-ox-out rounded-3 text-start p-3" for="pk-cross-platform">
      <span class="d-flex align-items-center gap-2 fw-semibold"><i class="bi bi-usb-drive"></i>Phone or security key</span>
      <span class="d-block text-secondary small mt-1">Use a QR-code phone flow, USB key, NFC key, or another external authenticator.</span>
    </label>
  </div>
</div>
<button id="btn-enroll" class="btn btn-ox w-100 d-flex align-items-center justify-content-center gap-2">
  <i class="bi bi-shield-check fs-5"></i> Create passkey
</button>
<div id="enroll-err" class="alert border-0 rounded-3 small d-none mt-3" style="background:rgba(220,53,69,.1);color:#dc3545"></div>
<p class="text-secondary small text-center mt-3 mb-0">
  <i class="bi bi-info-circle me-1"></i>The exact prompt text comes from your browser and operating system.
</p>
@endsection
@push('scripts')
<script>
(function(){
  const rp='{!! config('oxalis.rp_id','localhost') !!}',h=window.location.hostname;
  if(!rpMatchesHost(rp,h)){const w=document.getElementById('rpid-warn');if(w){document.getElementById('actual-host').textContent=h;w.classList.remove('d-none');document.getElementById('btn-enroll').disabled=true;}}
})();
document.getElementById('btn-enroll').addEventListener('click',async()=>{
  const btn=document.getElementById('btn-enroll');
  btn.disabled=true;btn.innerHTML='<span class="spinner-border spinner-border-sm"></span> Waiting for biometrics…';
  document.getElementById('enroll-err').classList.add('d-none');
  const label=document.getElementById('pk-label').value||'My Passkey';
  const authenticator_attachment=document.querySelector('input[name="authenticator_attachment"]:checked')?.value||'platform';
  try{
    if(!window.PublicKeyCredential||!navigator.credentials?.create){
      showErr('Your browser does not support passkeys. Try a current version of Chrome, Edge, Safari, or Firefox.');
      reset();
      return;
    }
    if(!await selectedAuthenticatorAvailable(authenticator_attachment)){
      showErr('This browser does not report a built-in passkey authenticator on this device. Choose "Phone or security key" to use a QR-code phone flow or external key.');
      reset();
      return;
    }
    const r1=await fetch('{{ route('oxalis.passkeys.register.begin') }}',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify({label,authenticator_attachment})});
    const o=await jsonResponse(r1);
    if(o.error){showErr(o.error);reset();return;}
    console.log('[oxalis] rp.id=',o.rp?.id,'| hostname=',window.location.hostname);
    if(o.rp?.id&&!rpMatchesHost(o.rp.id,window.location.hostname)){showErr(`RP ID mismatch: server says "${o.rp.id}" but browser is on "${window.location.hostname}". Check {{ route('oxalis.health.passkeys') }}`);reset();return;}
    o.challenge=toB(o.challenge);o.user.id=toB(o.user.id);
    if(o.excludeCredentials)o.excludeCredentials=o.excludeCredentials.map(c=>({...c,id:toB(c.id)}));
    const cred=await navigator.credentials.create({publicKey:o});
    const r2=await fetch('{{ route('oxalis.passkeys.register.finish') }}',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify({id:cred.id,rawId:toS(cred.rawId),type:cred.type,response:{clientDataJSON:toS(cred.response.clientDataJSON),attestationObject:toS(cred.response.attestationObject)}})});
    const d=await jsonResponse(r2);
    if(d.message)window.location.href=d.recovery_url||'{{ config('oxalis.routes.home','/dashboard') }}';
    else{showErr((d.error||'Server rejected the passkey.')+' Check {{ route('oxalis.health.passkeys') }} for configuration issues.');reset();}
  }catch(e){showErr(friendlyErr(e));reset();}
});
function rpMatchesHost(rp,host){
  return !!rp&&!!host&&(host===rp||host.endsWith('.'+rp));
}
function toB(s){
  const b64=String(s).replace(/-/g,'+').replace(/_/g,'/');
  const padded=b64+'='.repeat((4-b64.length%4)%4);
  return Uint8Array.from(atob(padded),c=>c.charCodeAt(0));
}
function toS(b){
  const bytes=new Uint8Array(b);
  let bin='';
  for(let i=0;i<bytes.length;i+=0x8000)bin+=String.fromCharCode(...bytes.subarray(i,i+0x8000));
  return btoa(bin).replace(/\+/g,'-').replace(/\//g,'_').replace(/=/g,'');
}
async function jsonResponse(response){
  const text=await response.text();
  let data=null;
  try{data=text?JSON.parse(text):{};}catch(e){
    if(response.redirected||response.status===401||response.status===419)return {error:'Your session expired. Refresh the page, sign in again, and retry passkey setup.'};
    throw new Error('Unexpected server response while setting up the passkey.');
  }
  if(!response.ok&&!data.error)data.error='Passkey setup failed. Check {{ route('oxalis.health.passkeys') }} for configuration issues.';
  return data;
}
async function selectedAuthenticatorAvailable(attachment){
  if(attachment!=='platform'||typeof PublicKeyCredential.isUserVerifyingPlatformAuthenticatorAvailable!=='function')return true;
  try{return await PublicKeyCredential.isUserVerifyingPlatformAuthenticatorAvailable();}
  catch(e){return true;}
}
function friendlyErr(e){
  const m=(e.message||'').toLowerCase();
  if(e.name==='AbortError')
    return 'Passkey creation was cancelled. Please click "Create passkey" and approve the browser prompt.';
  if(e.name==='InvalidCharacterError')
    return 'The passkey challenge from the server could not be read. Refresh the page and try again.';
  if(e.name==='InvalidStateError'||m.includes('excludecredentials'))
    return 'A passkey for this account already exists on this device. Use it to sign in, or add a passkey from a different device.';
  if(e.name==='NotAllowedError'||m.includes('timed out')||m.includes('not allowed')||m.includes('operation either'))
    return 'Passkey creation was cancelled or timed out. Please click "Create passkey" and complete the browser prompt without waiting.';
  if(e.name==='NotSupportedError'||m.includes('not supported')||m.includes('authenticatorselection'))
    return 'Your browser or device does not support passkeys. Try Chrome, Edge 114+, or Safari 16+.';
  if(e.name==='SecurityError'||m.includes('security')||m.includes('invalid domain'))
    return 'Security check failed. Make sure the site uses HTTPS, and check {{ route('oxalis.health.passkeys') }}.';
  if(m.includes('session expired')||m.includes('unexpected server response'))
    return e.message;
  return 'Could not create passkey ('+e.name+'). Please try again or use another sign-in method.';
}
function showErr(m){const e=document.getElementById('enroll-err');e.textContent=m;e.classList.remove('d-none');}
function reset(){const b=document.getElementById('btn-enroll');b.disabled=false;b.innerHTML='<i class="bi bi-shield-check fs-5"></i> Try again';}
</script>
@endpush
