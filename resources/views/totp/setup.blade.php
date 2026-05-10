@extends('oxalis::layouts.oxalis')
@section('title','Set up authenticator app')
@section('content')
<div class="ox-icon"><i class="bi bi-phone"></i></div>
<h5 class="fw-bold text-center mb-1">Authenticator app</h5>
<p class="text-secondary small text-center mb-3">Scan the QR code with Google Authenticator, Authy, or any TOTP app.</p>
<div class="d-flex flex-column align-items-center mb-3">
  <div id="qr-wrap" class="rounded-3 p-2" style="background:#fff;display:inline-block" data-qr="{{ $qrUri }}"></div>
  <p id="qr-err" class="text-danger small text-center d-none mt-2 mb-0">QR could not render — use the manual key below.</p>
</div>
<details class="mb-3">
  <summary class="small text-secondary" style="cursor:pointer">Can't scan? Enter key manually</summary>
  <div class="mt-2 p-2 rounded-3 text-center" style="background:var(--bs-tertiary-bg,#f8f9fa);border:1px solid var(--bs-border-color)">
    <code class="user-select-all fw-bold" style="font-size:.85rem;letter-spacing:.08em">{{ $secret }}</code>
  </div>
</details>
<form action="{{ route('oxalis.totp.confirm') }}" method="POST">
  @csrf
  <div class="form-floating mb-3">
    <input type="text" name="code" inputmode="numeric" pattern="\d{6}" maxlength="6"
      class="form-control rounded-3 @error('code') is-invalid @enderror"
      placeholder="000000" autofocus autocomplete="one-time-code">
    <label>6-digit code from your app</label>
    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <button class="btn btn-ox w-100">Confirm &amp; enable</button>
</form>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/qrcodejs2@0.0.2/qrcode.min.js"></script>
<script>
(function(){
  var w=document.getElementById('qr-wrap');
  if(!w)return;
  try{
    new QRCode(w,{text:w.dataset.qr,width:192,height:192,colorDark:'#000',colorLight:'#fff',correctLevel:QRCode.CorrectLevel.M});
  }catch(e){
    w.style.display='none';
    document.getElementById('qr-err').classList.remove('d-none');
  }
})();
</script>
@endpush