@extends('oxalis::layouts.oxalis')
@section('title','Your passkeys')
@section('content')
<h5 class="fw-bold mb-4">Your passkeys</h5>
@forelse($passkeys as $pk)
<div class="d-flex align-items-start gap-2 mb-3 p-3 rounded-3" style="background:var(--bs-tertiary-bg,#f8f9fa);border:1px solid var(--bs-border-color)">
  <i class="bi bi-key fs-5 mt-1" style="color:var(--ox)"></i>
  <div class="flex-grow-1 min-width-0">
    <div class="fw-semibold small">{{ $pk->label }}</div>
    <div class="text-secondary" style="font-size:.72rem">Added {{ $pk->created_at->diffForHumans() }}@if($pk->last_used_at) · Last used {{ $pk->last_used_at->diffForHumans() }}@endif</div>
    <form action="{{ route('oxalis.passkeys.rename') }}" method="POST" class="d-flex gap-1 mt-2">
      @csrf
      <input type="hidden" name="id" value="{{ $pk->id }}">
      <input type="text" name="label" class="form-control form-control-sm rounded-pill" value="{{ $pk->label }}" style="max-width:180px">
      <button class="btn btn-sm btn-ox-out rounded-pill px-3">Save</button>
    </form>
  </div>
  <form action="{{ route('oxalis.passkeys.delete') }}" method="POST">
    @csrf
    <input type="hidden" name="id" value="{{ $pk->id }}">
    <button class="btn btn-sm rounded-pill" style="border:1px solid #dc3545;color:#dc3545" onclick="return confirm('Remove this passkey?')"><i class="bi bi-trash"></i></button>
  </form>
</div>
@empty
<div class="text-center py-4 text-secondary">
  <i class="bi bi-key fs-3 d-block mb-2 opacity-30"></i>
  No passkeys registered yet.
</div>
@endforelse
<hr class="my-3">
<h6 class="fw-semibold mb-2">Add another passkey</h6>
<div class="d-flex gap-2">
  <input type="text" id="new-label" class="form-control rounded-3" placeholder="e.g. My MacBook">
  <button id="btn-add" class="btn btn-ox text-nowrap rounded-pill px-3"><i class="bi bi-plus-lg me-1"></i>Add</button>
</div>
<div id="add-err" class="alert border-0 rounded-3 small d-none mt-2" style="background:rgba(220,53,69,.1);color:#dc3545"></div>
@endsection
@push('scripts')
<script>
document.getElementById('btn-add').addEventListener('click',async()=>{
  const btn=document.getElementById('btn-add');btn.disabled=true;btn.innerHTML='<span class="spinner-border spinner-border-sm"></span>';
  const toB=s=>Uint8Array.from(atob(s.replace(/-/g,'+').replace(/_/g,'/')),c=>c.charCodeAt(0));
  const toS=b=>btoa(String.fromCharCode(...new Uint8Array(b))).replace(/\+/g,'-').replace(/\//g,'_').replace(/=/g,'');
  const label=document.getElementById('new-label').value||'New Passkey';
  try{
    const r1=await fetch('{{ route('oxalis.passkeys.register.begin') }}',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify({label})});
    const o=await r1.json();if(o.error){showAddErr(o.error);reset();return;}
    o.challenge=toB(o.challenge);o.user.id=toB(o.user.id);
    if(o.excludeCredentials)o.excludeCredentials=o.excludeCredentials.map(c=>({...c,id:toB(c.id)}));
    const cred=await navigator.credentials.create({publicKey:o});
    const r2=await fetch('{{ route('oxalis.passkeys.register.finish') }}',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify({id:cred.id,rawId:toS(cred.rawId),type:cred.type,response:{clientDataJSON:toS(cred.response.clientDataJSON),attestationObject:toS(cred.response.attestationObject)}})});
    const d=await r2.json();
    if(d.message)location.reload();else{showAddErr(d.error||'Registration failed.');reset();}
  }catch(e){showAddErr('Error: '+e.message);reset();}
});
function showAddErr(m){const e=document.getElementById('add-err');e.textContent=m;e.classList.remove('d-none');}
function reset(){const b=document.getElementById('btn-add');b.disabled=false;b.innerHTML='<i class="bi bi-plus-lg me-1"></i>Add';}
</script>
@endpush