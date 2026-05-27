@extends('oxalis::layouts.oxalis')
@section('title','Your passkeys')
@section('content')
@php
  /**
   * Infer a device icon and human label from the passkey's stored label.
   * Falls back to generic key icon when no keywords match.
   * (Future: replace with AAGUID lookup after adding an aaguid column to oxalis_passkeys.)
   */
  function oxDeviceInfo(string $label): array {
    $l = mb_strtolower($label);
    if (str_contains($l,'iphone')||str_contains($l,'ipad'))              return ['bi-phone-fill',  'iPhone / iPad'];
    if (str_contains($l,'face id'))                                       return ['bi-phone-fill',  'Face ID'];
    if (str_contains($l,'touch id')||str_contains($l,'macbook')||str_contains($l,'mac ')) return ['bi-fingerprint', 'Mac / Touch ID'];
    if (str_contains($l,'windows')||str_contains($l,'hello'))            return ['bi-windows',     'Windows Hello'];
    if (str_contains($l,'yubikey')||str_contains($l,'yubico')||str_contains($l,'hardware')||str_contains($l,'usb')) return ['bi-usb-drive',  'Hardware key'];
    if (str_contains($l,'android')||str_contains($l,'pixel')||str_contains($l,'samsung')) return ['bi-android2',   'Android'];
    if (str_contains($l,'chrome')||str_contains($l,'chromebook'))        return ['bi-laptop',      'Chromebook'];
    if (str_contains($l,'google')||str_contains($l,'gmail'))             return ['bi-google',      'Google account'];
    if (str_contains($l,'icloud'))                                        return ['bi-cloud-fill',  'iCloud Keychain'];
    return ['bi-key-fill', 'Security key'];
  }
@endphp

<a href="{{ route('oxalis.account') }}" class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none small mb-3"><i class="bi bi-arrow-left"></i> Account</a>
<h5 class="fw-bold mb-1">Your passkeys</h5>
<p class="text-secondary small mb-4">Passkeys let you sign in with your biometric or device PIN — no password needed.</p>

{{-- Health warning --}}
@if($passkeys->count() === 1)
<div class="alert border-0 rounded-3 mb-3 d-flex align-items-center gap-2" style="background:rgba(255,193,7,.1);color:#997404;font-size:.84rem">
  <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
  <span><strong>One passkey only.</strong> Add a backup so you're not locked out if you lose this device.</span>
</div>
@endif

@forelse($passkeys as $pk)
@php [$devIcon, $devLabel] = oxDeviceInfo($pk->label); @endphp
<div class="d-flex align-items-start gap-3 mb-3 p-3 rounded-3" style="background:var(--bs-tertiary-bg,rgba(0,0,0,.03));border:1px solid var(--bs-border-color)">
  <div class="d-flex align-items-center justify-content-center flex-shrink-0 rounded-3"
       style="width:40px;height:40px;background:var(--ox-sf);color:var(--ox);font-size:1.1rem">
    <i class="bi {{ $devIcon }}"></i>
  </div>
  <div class="flex-grow-1 min-width-0">
    <div class="d-flex align-items-center gap-2 mb-1">
      <span class="fw-semibold small">{{ $pk->label }}</span>
      <span class="badge rounded-pill" style="background:var(--bs-tertiary-bg,#f0f0f0);color:var(--bs-secondary-color);font-size:.65rem;font-weight:500">{{ $devLabel }}</span>
    </div>
    <div class="text-secondary" style="font-size:.72rem">
      Added {{ $pk->created_at->diffForHumans() }}@if($pk->last_used_at) &nbsp;·&nbsp; Used {{ $pk->last_used_at->diffForHumans() }}@endif
    </div>
    <form action="{{ route('oxalis.passkeys.rename') }}" method="POST" class="d-flex gap-1 mt-2">
      @csrf
      <input type="hidden" name="id" value="{{ $pk->id }}">
      <input type="text" name="label" class="form-control form-control-sm rounded-pill" value="{{ $pk->label }}" style="max-width:180px">
      <button class="btn btn-sm btn-ox-out rounded-pill px-3">Save</button>
    </form>
  </div>
  <form action="{{ route('oxalis.passkeys.delete') }}" method="POST" class="flex-shrink-0">
    @csrf
    <input type="hidden" name="id" value="{{ $pk->id }}">
    <button class="btn btn-sm rounded-pill px-2" style="border:1px solid #dc3545;color:#dc3545"
            onclick="return confirm('Remove this passkey?')"><i class="bi bi-trash3"></i></button>
  </form>
</div>
@empty
<div class="text-center py-4 text-secondary">
  <i class="bi bi-key fs-2 d-block mb-2 opacity-30"></i>
  No passkeys yet. Add one below.
</div>
@endforelse

<hr class="my-3">
<h6 class="fw-semibold mb-2">Add a passkey</h6>
<div class="d-flex gap-2 mb-1">
  <input type="text" id="new-label" class="form-control rounded-3" placeholder="e.g. My MacBook, YubiKey 5">
  <button id="btn-add" class="btn btn-ox text-nowrap rounded-pill px-3"><i class="bi bi-plus-lg me-1"></i>Add</button>
</div>
<p class="text-secondary" style="font-size:.72rem">Give it a name that helps you identify the device later.</p>
<div id="add-err" class="alert border-0 rounded-3 small d-none mt-2" style="background:rgba(220,53,69,.1);color:#dc3545"></div>

@endsection
@push('scripts')
<script>
document.getElementById('btn-add').addEventListener('click', async () => {
  const btn = document.getElementById('btn-add');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
  const toB = s => Uint8Array.from(atob(s.replace(/-/g,'+').replace(/_/g,'/')), c => c.charCodeAt(0));
  const toS = b => btoa(String.fromCharCode(...new Uint8Array(b))).replace(/\+/g,'-').replace(/\//g,'_').replace(/=/g,'');
  const label = document.getElementById('new-label').value.trim() || 'My Passkey';
  try {
    const r1 = await fetch('{{ route('oxalis.passkeys.register.begin') }}',{
      method:'POST',
      headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
      body:JSON.stringify({label})
    });
    const o = await r1.json();
    if (o.error) { showAddErr(o.error); reset(); return; }
    o.challenge = toB(o.challenge); o.user.id = toB(o.user.id);
    if (o.excludeCredentials) o.excludeCredentials = o.excludeCredentials.map(c=>({...c,id:toB(c.id)}));
    const cred = await navigator.credentials.create({publicKey:o});
    const r2 = await fetch('{{ route('oxalis.passkeys.register.finish') }}',{
      method:'POST',
      headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
      body:JSON.stringify({
        id:cred.id, rawId:toS(cred.rawId), type:cred.type,
        response:{clientDataJSON:toS(cred.response.clientDataJSON), attestationObject:toS(cred.response.attestationObject)}
      })
    });
    const d = await r2.json();
    if (d.message) location.reload();
    else { showAddErr(d.error||'Registration failed.'); reset(); }
  } catch(e) { showAddErr('Error: ' + e.message); reset(); }
});
function showAddErr(m){const e=document.getElementById('add-err');e.textContent=m;e.classList.remove('d-none');}
function reset(){const b=document.getElementById('btn-add');b.disabled=false;b.innerHTML='<i class="bi bi-plus-lg me-1"></i>Add';}
</script>
@endpush
