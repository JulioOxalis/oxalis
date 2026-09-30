@extends('oxalis::layouts.oxalis')
@section('title','Set your password')
@section('content')

{{-- Step indicator --}}
<div class="d-flex align-items-center justify-content-center gap-0 mb-4">
  <div class="d-flex flex-column align-items-center">
    <div style="width:28px;height:28px;border-radius:50%;background:rgba(25,135,84,.15);color:#198754;display:flex;align-items:center;justify-content:center;font-size:.8rem">
      <i class="bi bi-check2" style="font-weight:800"></i>
    </div>
    <div style="font-size:.65rem;color:#198754;margin-top:.3rem;font-weight:600">Details</div>
  </div>
  <div style="width:40px;height:2px;background:#198754;margin-bottom:1.1rem"></div>
  <div class="d-flex flex-column align-items-center">
    <div style="width:28px;height:28px;border-radius:50%;background:rgba(25,135,84,.15);color:#198754;display:flex;align-items:center;justify-content:center;font-size:.8rem">
      <i class="bi bi-check2" style="font-weight:800"></i>
    </div>
    <div style="font-size:.65rem;color:#198754;margin-top:.3rem;font-weight:600">Verified</div>
  </div>
  <div style="width:40px;height:2px;background:var(--ox);margin-bottom:1.1rem"></div>
  <div class="d-flex flex-column align-items-center">
    <div style="width:28px;height:28px;border-radius:50%;background:var(--ox);color:#fff;display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:700">3</div>
    <div style="font-size:.65rem;color:var(--ox);margin-top:.3rem;font-weight:600">Password</div>
  </div>
</div>

<div class="ox-icon"><i class="bi bi-shield-lock"></i></div>
<h5 class="fw-bold text-center mb-1">Set your password</h5>
<p class="text-secondary small text-center mb-1">Almost there, <strong>{{ $name }}</strong>.</p>
<p class="text-secondary small text-center mb-4" style="font-size:.78rem">Creating account for <code>{{ $email }}</code></p>

<form action="{{ route('oxalis.register.password') }}" method="POST">
  @csrf
  <div class="form-floating mb-3">
    <input type="password" name="password" id="password"
      class="form-control rounded-3 @error('password') is-invalid @enderror"
      placeholder="p" autocomplete="new-password" autofocus>
    <label for="password">Password <span class="text-secondary fw-normal">(min 8 chars)</span></label>
    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <div class="form-floating mb-1">
    <input type="password" name="password_confirmation" id="password_confirmation"
      class="form-control rounded-3"
      placeholder="c" autocomplete="new-password">
    <label for="password_confirmation">Confirm password</label>
  </div>

  {{-- Strength meter --}}
  <div class="mb-4 mt-2">
    <div style="height:4px;border-radius:50rem;background:var(--bs-tertiary-bg,#f0f0f0);overflow:hidden">
      <div id="strength-bar" style="height:100%;width:0;border-radius:50rem;transition:width .3s,background .3s"></div>
    </div>
    <div id="strength-label" class="text-secondary mt-1" style="font-size:.7rem"></div>
  </div>

  <button class="btn btn-ox w-100 d-flex align-items-center justify-content-center gap-2">
    <i class="bi bi-person-check-fill"></i> Create account
  </button>
</form>
@endsection
@push('scripts')
<script>
document.getElementById('password').addEventListener('input',function(){
  var v=this.value,s=0,label='',color='';
  if(v.length>=8)s++;if(v.length>=12)s++;
  if(/[A-Z]/.test(v))s++;if(/[0-9]/.test(v))s++;if(/[^A-Za-z0-9]/.test(v))s++;
  var pct=[0,20,40,65,85,100][Math.min(s,5)];
  if(s<=1){label='Weak';color='#ef4444';}
  else if(s<=2){label='Fair';color='#f59e0b';}
  else if(s<=3){label='Good';color='#3b82f6';}
  else{label='Strong';color='#10b981';}
  document.getElementById('strength-bar').style.cssText='height:100%;width:'+pct+'%;background:'+color+';border-radius:50rem;transition:width .3s,background .3s';
  document.getElementById('strength-label').textContent=v.length?label:'';
  document.getElementById('strength-label').style.color=color;
});
</script>
@endpush
