@extends('oxalis::layouts.oxalis')
@section('title','Recovery codes')
@section('content')
<div class="ox-icon"><i class="bi bi-life-preserver"></i></div>
<h5 class="fw-bold text-center mb-2">Recovery codes</h5>
@isset($recoveryCodes)
<div class="alert border-0 rounded-3 mb-3 small" style="background:rgba(255,193,7,.1);color:#997404">
  <i class="bi bi-exclamation-triangle me-1"></i><strong>Save these now</strong> — you won't see them again. Each can only be used once.
</div>
<div class="row g-2 mb-3" id="codes-grid">
  @foreach($recoveryCodes as $code)
  <div class="col-6"><code class="d-block p-2 rounded-3 text-center fw-bold" style="background:var(--bs-tertiary-bg,#f8f9fa);border:1px solid var(--bs-border-color);font-size:.85rem;cursor:pointer" onclick="navigator.clipboard.writeText('{{ $code }}')">{{ $code }}</code></div>
  @endforeach
</div>
<button class="btn btn-ox-out w-100 mb-2" onclick="downloadCodes()"><i class="bi bi-download me-2"></i>Download as text file</button>
@else
<p class="text-secondary small text-center mb-3">You have recovery codes saved. Regenerate them if you think they may be compromised (requires your current authenticator code).</p>
@endisset
<form action="{{ route('oxalis.recovery.regenerate') }}" method="POST" class="mt-2">
  @csrf
  <div class="input-group">
    <input type="text" name="code" inputmode="numeric" pattern="\d{6}" maxlength="6"
      class="form-control rounded-start-3 @error('code') is-invalid @enderror"
      placeholder="Authenticator code" autocomplete="one-time-code">
    <button class="btn btn-ox-out rounded-end-3">Regenerate</button>
    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
</form>
@endsection
@push('scripts')
@isset($recoveryCodes)
<script>
function downloadCodes(){const codes=@json($recoveryCodes);const a=document.createElement('a');a.href='data:text/plain;charset=utf-8,'+encodeURIComponent(codes.join('\n'));a.download='oxalis-recovery-codes.txt';a.click();}
</script>
@endisset
@endpush