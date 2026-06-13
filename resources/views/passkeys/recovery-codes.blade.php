@extends('oxalis::layouts.oxalis')
@section('title','Passkey recovery codes')
@section('content')
<div class="ox-icon"><i class="bi bi-life-preserver"></i></div>
<h5 class="fw-bold text-center mb-2">Passkey recovery codes</h5>

@if($recoveryCodes)
<div class="alert border-0 rounded-3 mb-3 small" style="background:rgba(255,193,7,.1);color:#997404">
  <i class="bi bi-exclamation-triangle me-1"></i><strong>Save these now</strong> — they are shown once and each code works only one time.
</div>
<div class="row g-2 mb-3" id="codes-grid">
  @foreach($recoveryCodes as $code)
  <div class="col-6"><code class="d-block p-2 rounded-3 text-center fw-bold" style="background:var(--bs-tertiary-bg,#f8f9fa);border:1px solid var(--bs-border-color);font-size:.78rem;cursor:pointer" onclick="navigator.clipboard.writeText('{{ $code }}')">{{ $code }}</code></div>
  @endforeach
</div>
<button class="btn btn-ox-out w-100 mb-2" onclick="downloadCodes()"><i class="bi bi-download me-2"></i>Download as text file</button>
@else
<p class="text-secondary small text-center mb-3">
  You have {{ $activeCount }} active passkey recovery code{{ $activeCount === 1 ? '' : 's' }}. Regenerate them if they may be compromised.
</p>
@endif

<form action="{{ route('oxalis.passkeys.recovery.regenerate') }}" method="POST" class="mt-2">
  @csrf
  <button class="btn btn-ox w-100">{{ $activeCount ? 'Regenerate recovery codes' : 'Generate recovery codes' }}</button>
</form>
<p class="text-center small mt-3 mb-0"><a href="{{ route('oxalis.account') }}">Back to account</a></p>
@endsection
@push('scripts')
@if($recoveryCodes)
<script>
function downloadCodes(){const codes=@json($recoveryCodes);const a=document.createElement('a');a.href='data:text/plain;charset=utf-8,'+encodeURIComponent(codes.join('\n'));a.download='oxalis-passkey-recovery-codes.txt';a.click();}
</script>
@endif
@endpush
