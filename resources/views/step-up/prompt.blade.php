@extends('oxalis::layouts.oxalis')
@section('title','Confirm your identity')
@section('content')
<div class="ox-icon" style="background:rgba(255,193,7,.1);color:#997404"><i class="bi bi-shield-exclamation"></i></div>
<h5 class="fw-bold text-center mb-1">Confirm your identity</h5>
<p class="text-secondary small text-center mb-4">This action requires re-verification for your security.</p>

@if($hasPasskey)
<button id="btn-su-pk" class="btn btn-ox w-100 mb-2 d-flex align-items-center justify-content-center gap-2">
  <i class="bi bi-fingerprint fs-5"></i> Verify with passkey
</button>
<div id="su-pk-err" class="alert border-0 rounded-3 small d-none mb-2" style="background:rgba(220,53,69,.1);color:#dc3545"></div>
@endif

@if($hasTotp)
@if($hasPasskey)<div class="ox-div">or enter code</div>@endif
<form action="{{ route('oxalis.step-up.totp') }}" method="POST">
  @csrf
  <div class="input-group">
    <input type="text" name="code" inputmode="numeric" pattern="\d{6}" maxlength="6"
      class="form-control rounded-start-3 @error('code') is-invalid @enderror"
      placeholder="6-digit code" autofocus autocomplete="one-time-code">
    <button class="btn btn-ox rounded-end-3">Verify</button>
    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
</form>
@endif
@endsection
@if($hasPasskey)
@push('scripts')
<script>
document.getElementById('btn-su-pk').addEventListener('click',async()=>{
  const btn=document.getElementById('btn-su-pk');btn.disabled=true;btn.innerHTML='<span class="spinner-border spinner-border-sm"></span>';
  const toB=s=>Uint8Array.from(atob(s.replace(/-/g,'+').replace(/_/g,'/')),c=>c.charCodeAt(0));
  const toS=b=>btoa(String.fromCharCode(...new Uint8Array(b))).replace(/\+/g,'-').replace(/\//g,'_').replace(/=/g,'');
  const opts=@json($passkeyOptions??[]);
  try{
    opts.challenge=toB(opts.challenge);
    if(opts.allowCredentials)opts.allowCredentials=opts.allowCredentials.map(c=>({...c,id:toB(c.id)}));
    const cred=await navigator.credentials.get({publicKey:opts});
    const r=await fetch('{{ route('oxalis.step-up.passkey') }}',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify({id:cred.id,rawId:toS(cred.rawId),type:cred.type,response:{clientDataJSON:toS(cred.response.clientDataJSON),authenticatorData:toS(cred.response.authenticatorData),signature:toS(cred.response.signature),userHandle:cred.response.userHandle?toS(cred.response.userHandle):null}})});
    const d=await r.json();
    if(d.redirect)window.location.href=d.redirect;else{const e=document.getElementById('su-pk-err');e.textContent=d.error||'Verification failed.';e.classList.remove('d-none');btn.disabled=false;btn.innerHTML='<i class="bi bi-fingerprint fs-5"></i> Try again';}
  }catch(e){const el=document.getElementById('su-pk-err');el.textContent='Passkey error: '+e.message;el.classList.remove('d-none');btn.disabled=false;btn.innerHTML='<i class="bi bi-fingerprint fs-5"></i> Try again';}
});
</script>
@endpush
@endif