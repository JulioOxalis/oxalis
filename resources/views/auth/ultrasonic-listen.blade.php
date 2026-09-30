@extends('oxalis::layouts.oxalis')
@section('title','Listen & Approve')
@section('content')

<style>
.ox-ult-icon {
  width:72px;height:72px;border-radius:50%;
  background:var(--ox-sf);color:var(--ox);
  display:flex;align-items:center;justify-content:center;
  font-size:2rem;margin:0 auto .75rem;
  transition:background .3s,box-shadow .3s;
}
.ox-ult-icon.listening {
  background:var(--ox);color:var(--ox-btn-fg,#fff);
  box-shadow:0 0 0 8px var(--ox-sf),0 0 0 16px rgba(var(--ox-rgb,.5));
  animation:ox-sonar 1.5s ease-out infinite;
}
@keyframes ox-sonar {
  0%   { box-shadow:0 0 0 0 var(--ox-sf),0 0 0 0 rgba(92,106,196,.2); }
  100% { box-shadow:0 0 0 14px rgba(92,106,196,0),0 0 0 28px rgba(92,106,196,0); }
}
.ox-ult-bars {
  display:flex;align-items:flex-end;justify-content:center;gap:4px;
  height:32px;margin:.5rem auto 0;width:fit-content;
}
.ox-ult-bar {
  width:5px;border-radius:3px;background:var(--ox);opacity:.2;
  transition:height .08s,opacity .08s;
}
</style>

<div class="text-center">
  <div class="ox-ult-icon" id="ult-icon"><i class="bi bi-soundwave"></i></div>
  <h5 class="fw-bold mb-1">Listen &amp; Approve</h5>
  <p class="text-secondary small mb-3" id="ult-desc">
    Tap <strong>Start Listening</strong> on this phone, then on your desktop click <em>Play Token</em>.
    Hold the devices close together.
  </p>
  <div class="ox-ult-bars" id="ult-bars">
    @for($i=0;$i<8;$i++) <div class="ox-ult-bar" style="height:{{ 8+$i%3*4 }}px"></div> @endfor
  </div>
</div>

<div class="mt-3 d-flex flex-column gap-2">
  <button id="btn-listen" class="btn btn-ox w-100">
    <i class="bi bi-mic-fill me-2"></i>Start Listening
  </button>
  <div id="ult-token-confirm" class="d-none">
    <div class="d-flex gap-2 align-items-center justify-content-center mb-2">
      <span class="text-secondary small">Decoded token:</span>
      <code id="ult-decoded" class="fw-bold" style="letter-spacing:.15em;font-size:1rem"></code>
    </div>
    <button id="btn-approve" class="btn btn-ox w-100">Approve this sign-in →</button>
  </div>
</div>

<div id="ult-status" class="mt-3 small text-center text-secondary"></div>
<div id="ult-err" class="alert border-0 rounded-3 small d-none mt-2" style="background:rgba(220,53,69,.1);color:#dc3545"></div>

<hr class="my-3">
<p class="text-center small mb-0 text-secondary">
  Signed in as <strong>{{ auth()->user()->email }}</strong> ·
  <a href="{{ config('oxalis.routes.home','/dashboard') }}">Back</a>
</p>

@endsection
@push('scripts')
<script>
(function(){
'use strict';
const FREQS      = [18000, 18500, 19000, 19500]; // 2-bit FSK: 00,01,10,11
const SYMBOL_MS  = 65;
const FFT_SIZE   = 8192;
const PREAMBLE   = 3;   // leading freq-0 symbols before data
const DATA_SYMS  = 16;  // 8 hex chars × 2 symbols
const NOISE_DB   = -72; // minimum magnitude to trigger detection
const CSRF       = document.querySelector('meta[name="csrf-token"]').content;
const APPROVE_URL = @json(route('oxalis.ultrasonic.approve'));

let listening = false, interval, timeout;
let audioCtx, analyser, stream;

// ── Animated bars (VU-meter style) ──────────────────────────────────────────
function animateBars(freqData) {
  const bars = document.querySelectorAll('.ox-ult-bar');
  bars.forEach((bar, i) => {
    const binIdx = Math.round(FREQS[i % 4] * FFT_SIZE / (audioCtx?.sampleRate ?? 48000));
    const val    = freqData ? Math.max(0, (freqData[binIdx] + 90) / 90) : 0;
    bar.style.height   = (8 + val * 22) + 'px';
    bar.style.opacity  = 0.2 + val * 0.8;
  });
}

// ── Start microphone listener ────────────────────────────────────────────────
document.getElementById('btn-listen').addEventListener('click', async function() {
  if (listening) return;

  hideErr();
  setStatus('Requesting microphone…');

  try {
    stream   = await navigator.mediaDevices.getUserMedia({ audio: true, video: false });
    audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    const source = audioCtx.createMediaStreamSource(stream);
    analyser     = audioCtx.createAnalyser();
    analyser.fftSize              = FFT_SIZE;
    analyser.smoothingTimeConstant = 0;
    source.connect(analyser);
  } catch(e) {
    showErr('Microphone access denied. Please allow microphone access and try again.');
    return;
  }

  listening = true;
  document.getElementById('btn-listen').disabled = true;
  document.getElementById('ult-icon').classList.add('listening');
  setStatus('Listening… keep devices close');

  const freqData      = new Float32Array(analyser.frequencyBinCount);
  const sampleRate    = audioCtx.sampleRate;
  const bins          = FREQS.map(f => Math.round(f * FFT_SIZE / sampleRate));
  let preambleCount   = 0;
  let dataSymbols     = [];
  let silenceStreak   = 0;

  function tick() {
    analyser.getFloatFrequencyData(freqData);
    animateBars(freqData);

    // Find dominant frequency among our 4 bins
    const magnitudes = bins.map(b => freqData[b]);
    const maxMag     = Math.max(...magnitudes);
    const maxIdx     = magnitudes.indexOf(maxMag);

    if (maxMag < NOISE_DB) {
      silenceStreak++;
      if (silenceStreak > 6 && preambleCount > 0 && dataSymbols.length < DATA_SYMS) {
        // Signal dropped mid-stream — reset
        preambleCount = 0; dataSymbols = []; silenceStreak = 0;
      }
      return;
    }
    silenceStreak = 0;

    // Preamble: 3 consecutive freq-0 symbols
    if (preambleCount < PREAMBLE) {
      if (maxIdx === 0) preambleCount++;
      else if (preambleCount < 2) preambleCount = 0; // false start
      return;
    }

    dataSymbols.push(maxIdx);

    if (dataSymbols.length === DATA_SYMS) {
      clearInterval(interval);
      clearTimeout(timeout);
      stopMic();
      const token = symbolsToHex(dataSymbols);
      showDecoded(token);
    }
  }

  interval = setInterval(tick, SYMBOL_MS);
  timeout  = setTimeout(() => {
    clearInterval(interval);
    stopMic();
    setStatus('');
    showErr('Nothing detected. Make sure the desktop is playing the token and devices are close.');
  }, 8000);
});

// ── Approve the decoded token ────────────────────────────────────────────────
document.getElementById('btn-approve').addEventListener('click', async function() {
  const token = document.getElementById('ult-decoded').textContent;
  if (!token || token.length !== 8) return;

  this.disabled = true;
  this.textContent = 'Approving…';
  setStatus('');

  try {
    const r = await fetch(APPROVE_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
      body: JSON.stringify({ token }),
    });
    const d = await r.json();

    if (d.message) {
      document.getElementById('ult-token-confirm').classList.add('d-none');
      setStatus('✓ ' + d.message);
      document.getElementById('ult-icon').innerHTML = '<i class="bi bi-check-circle-fill"></i>';
      document.getElementById('ult-icon').style.cssText = 'background:rgba(25,135,84,.15);color:#198754';
    } else {
      showErr(d.error || 'Approval failed.');
      this.disabled = false;
      this.textContent = 'Approve this sign-in →';
    }
  } catch(e) {
    showErr('Network error — please try again.');
    this.disabled = false;
    this.textContent = 'Approve this sign-in →';
  }
});

// ── Helpers ──────────────────────────────────────────────────────────────────
function symbolsToHex(symbols) {
  let hex = '';
  for (let i = 0; i < symbols.length; i += 2) {
    const nibble = ((symbols[i] & 3) << 2) | (symbols[i + 1] & 3);
    hex += nibble.toString(16).toUpperCase();
  }
  return hex;
}

function showDecoded(token) {
  document.getElementById('ult-decoded').textContent = token;
  document.getElementById('ult-token-confirm').classList.remove('d-none');
  document.getElementById('ult-icon').classList.remove('listening');
  setStatus('Token received! Tap Approve to complete the sign-in.');
  animateBars(null);
}

function stopMic() {
  listening = false;
  if (stream) { stream.getTracks().forEach(t => t.stop()); stream = null; }
  if (audioCtx) { audioCtx.close().catch(() => {}); audioCtx = null; }
  document.getElementById('ult-icon').classList.remove('listening');
  document.getElementById('btn-listen').disabled = false;
}

function setStatus(m) { document.getElementById('ult-status').textContent = m; }
function showErr(m)   { const e=document.getElementById('ult-err'); e.textContent=m; e.classList.remove('d-none'); }
function hideErr()    { document.getElementById('ult-err').classList.add('d-none'); }

})();
</script>
@endpush
