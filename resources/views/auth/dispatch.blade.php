@extends('oxalis::layouts.oxalis')
@section('title','Sign in')
@section('content')
<h5 class="fw-bold text-center mb-1">Welcome</h5>
<p class="text-secondary small text-center mb-4">Enter your email — we'll take it from there.</p>

<div id="s-email">
  <div class="form-floating mb-3">
    <input type="email" id="d-email" class="form-control rounded-3" placeholder="e" autocomplete="email">
    <label>Email address</label>
  </div>
  <div id="d-err" class="alert border-0 rounded-3 small d-none py-2 mb-2" style="background:rgba(220,53,69,.1);color:#dc3545"></div>
  <button id="btn-d" class="btn btn-ox w-100">Continue <i class="bi bi-arrow-right ms-1"></i></button>
</div>

<div id="s-magic" class="d-none text-center">
  <div class="ox-icon" style="background:rgba(25,135,84,.1);color:#198754"><i class="bi bi-envelope-check"></i></div>
  <p id="magic-msg" class="text-secondary small mb-2"></p>
  <div id="d-devlink" class="alert border-0 rounded-3 small d-none text-start" style="background:rgba(255,193,7,.1);color:#997404">
    <strong>Dev link:</strong><br><a id="d-devurl" href="#" class="text-break small"></a>
  </div>
</div>

<div id="s-passkey" class="d-none text-center">
  <div class="ox-icon"><i class="bi bi-fingerprint"></i></div>
  <p class="text-secondary small mb-0">Verifying your identity…</p>
  <div class="spinner-border text-secondary mt-2" style="width:1.4rem;height:1.4rem"></div>
</div>

@if(config('oxalis.methods.social'))
<div class="ox-div mt-3">or</div>
<div class="d-grid gap-2">
  @if(config('oxalis.social.google.enabled'))
  <a href="{{ route('oxalis.social.redirect','google') }}" class="btn btn-ox-out">Continue with Google</a>
  @endif
  @if(config('oxalis.social.github.enabled'))
  <a href="{{ route('oxalis.social.redirect','github') }}" class="btn btn-dark rounded-pill">Continue with GitHub</a>
  @endif
</div>
@endif

<p class="text-center small text-secondary mt-3 mb-0"><a href="{{ route('oxalis.login') }}">See all sign-in options</a></p>
@endsection
@push('scripts')
<script>
const CSRF='{{ csrf_token() }}';
const post=(u,d)=>fetch(u,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},body:JSON.stringify(d)}).then(r=>r.json());
const toB=s=>Uint8Array.from(atob(s.replace(/-/g,'+').replace(/_/g,'/')),c=>c.charCodeAt(0));
const toS=b=>btoa(String.fromCharCode(...new Uint8Array(b))).replace(/\+/g,'-').replace(/\//g,'_').replace(/=/g,'');

document.getElementById('btn-d').addEventListener('click',go);
document.getElementById('d-email').addEventListener('keydown',e=>{if(e.key==='Enter')go();});

async function go(){
  const email=document.getElementById('d-email').value.trim();
  if(!email){showErr('Enter your email.');return;}
  const btn=document.getElementById('btn-d');btn.disabled=true;btn.innerHTML='<span class="spinner-border spinner-border-sm"></span>';
  document.getElementById('d-err').classList.add('d-none');
  const d=await post('{{ route('oxalis.dispatch.check') }}',{email});
  if(d.method==='magic_link'){showStep('magic');document.getElementById('magic-msg').textContent=d.message||'Sign-in link sent.';if(d.dev_link){document.getElementById('d-devlink').classList.remove('d-none');const a=document.getElementById('d-devurl');a.href=d.dev_link;a.textContent=d.dev_link;}return;}
  if(d.method==='totp'){window.location.href=d.redirect;return;}
  if(d.method==='passkey'){showStep('passkey');await doPasskey(d.options);return;}
  showErr(d.error||'Something went wrong.');resetBtn();
}
async function doPasskey(opts){
  opts.challenge=toB(opts.challenge);
  if(opts.allowCredentials)opts.allowCredentials=opts.allowCredentials.map(c=>({...c,id:toB(c.id)}));
  let cred;try{cred=await navigator.credentials.get({publicKey:opts});}catch(e){showErr('Passkey error: '+e.message);showStep('email');resetBtn();return;}
  const d=await post('{{ route('oxalis.passkeys.login.finish') }}',{id:cred.id,rawId:toS(cred.rawId),type:cred.type,response:{clientDataJSON:toS(cred.response.clientDataJSON),authenticatorData:toS(cred.response.authenticatorData),signature:toS(cred.response.signature),userHandle:cred.response.userHandle?toS(cred.response.userHandle):null}});
  if(d.redirect)window.location.href=d.redirect;else{showErr(d.error||'Authentication failed.');showStep('email');resetBtn();}
}
function showStep(n){['email','magic','passkey'].forEach(s=>document.getElementById('s-'+s).classList.toggle('d-none',s!==n));}
function showErr(m){const e=document.getElementById('d-err');e.textContent=m;e.classList.remove('d-none');}
function resetBtn(){const b=document.getElementById('btn-d');b.disabled=false;b.innerHTML='Continue <i class="bi bi-arrow-right ms-1"></i>';}
</script>
@endpush