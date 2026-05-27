@extends('oxalis::layouts.oxalis')
@section('title','QR Login')
@section('content')

<style>
.ox-qr-frame{background:#fff;padding:12px;border-radius:var(--ox-r);display:inline-block;box-shadow:0 2px 8px rgba(0,0,0,.12)}
#qr-canvas { display:block; }
.ox-qr-wrap {
  display:flex; flex-direction:column; align-items:center;
  gap:.75rem; padding:1rem 0;
}
.ox-qr-status {
  font-size:.78rem; color:var(--bs-secondary-color);
  display:flex; align-items:center; gap:.45rem;
}
.ox-pulse { width:8px; height:8px; border-radius:50%; background:var(--ox);
  animation:ox-pulse-ring 1.4s ease-in-out infinite; flex-shrink:0; }
@keyframes ox-pulse-ring {
  0%,100%{opacity:1;transform:scale(1)}
  50%{opacity:.5;transform:scale(.8)}
}
.ox-qr-expire { font-size:.7rem; color:var(--bs-secondary-color); }
</style>

<h5 class="fw-bold mb-1 text-center">Scan to sign in</h5>
<p class="text-secondary small text-center mb-3">Open {{ config('app.name') }} on your phone, sign in, and scan this code.</p>

<div class="ox-qr-wrap">
  <div class="ox-qr-frame"><canvas id="qr-canvas" width="200" height="200"></canvas></div>
  <div class="ox-qr-status" id="qr-status">
    <div class="ox-pulse"></div>
    <span>Waiting for phone approval…</span>
  </div>
  <div class="ox-qr-expire" id="qr-expire"></div>
</div>

<div id="qr-alert" class="alert border-0 rounded-3 small d-none mt-2" style="background:rgba(220,53,69,.1);color:#dc3545"></div>

<hr class="my-3">
<p class="text-center small mb-0 text-secondary">
  Or <a href="{{ route('oxalis.login') }}">sign in another way →</a>
</p>

@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js" crossorigin="anonymous"></script>
<script>
(function(){
const token    = @json($token);
const ttl      = @json($ttl);
const approveUrl = @json($approveUrl);
const pollUrl  = @json(route('oxalis.qr.poll', '__TOKEN__')).replace('__TOKEN__', token);
const loginUrl = @json(route('oxalis.qr.login', '__TOKEN__')).replace('__TOKEN__', token);
const csrf     = document.querySelector('meta[name="csrf-token"]').content;

// Generate QR code pointing to the mobile approval page
QRCode.toCanvas(document.getElementById('qr-canvas'), approveUrl, {
  width: 200,
  margin: 0,
  color: { dark: '#000000', light: '#ffffff' }
}, function(err){ if (err) console.error(err); });

// Countdown timer
let remaining = ttl;
const expireEl = document.getElementById('qr-expire');
function tick() {
  if (remaining <= 0) {
    expireEl.textContent = 'Expired — refresh the page for a new code.';
    clearInterval(countdownId);
    clearInterval(pollId);
    document.getElementById('qr-status').innerHTML = '<i class="bi bi-x-circle-fill text-danger"></i> Code expired';
    return;
  }
  expireEl.textContent = 'Expires in ' + remaining + 's';
  remaining--;
}
tick();
const countdownId = setInterval(tick, 1000);

// Poll server
async function poll() {
  try {
    const r = await fetch(pollUrl, { headers: { 'X-CSRF-TOKEN': csrf } });
    const d = await r.json();
    if (d.status === 'approved') {
      clearInterval(pollId);
      clearInterval(countdownId);
      document.getElementById('qr-status').innerHTML = '<i class="bi bi-check-circle-fill text-success"></i> Approved — signing you in…';
      // Complete login
      const r2 = await fetch(loginUrl, { method:'POST', headers:{ 'X-CSRF-TOKEN':csrf,'Content-Type':'application/json' }, body:'{}' });
      const d2 = await r2.json();
      if (d2.redirect) window.location.href = d2.redirect;
      else showErr(d2.error || 'Login failed.');
    } else if (d.status === 'expired') {
      clearInterval(pollId);
      document.getElementById('qr-status').innerHTML = '<i class="bi bi-x-circle-fill text-danger"></i> Code expired';
    }
  } catch(e) { /* ignore transient network errors during poll */ }
}

const pollId = setInterval(poll, 2000);

function showErr(m) {
  const el = document.getElementById('qr-alert');
  el.textContent = m; el.classList.remove('d-none');
}
})();
</script>
@endpush
