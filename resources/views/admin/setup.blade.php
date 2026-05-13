<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Set up admin access — Oxalis</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
  :root{--ox:#5c6ac4;--ox-sf:rgba(92,106,196,.15)}
  body{background:#080a12;color:#c4cbde;font-family:system-ui,-apple-system,sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem 1rem}
  .setup-card{width:100%;max-width:460px;background:#0f1220;border:1px solid #1a1d2e;border-radius:16px;padding:2.25rem 2rem}
  .setup-icon{width:56px;height:56px;border-radius:16px;background:var(--ox-sf);color:var(--ox);display:flex;align-items:center;justify-content:center;font-size:1.5rem;margin:0 auto 1.25rem}
  h1{font-size:1.3rem;font-weight:700;color:#e8ecf8;text-align:center;letter-spacing:-.02em;margin-bottom:.35rem}
  .sub{color:#5a6277;font-size:.83rem;text-align:center;margin-bottom:2rem;line-height:1.6}
  .field{margin-bottom:1.1rem}
  label{display:block;font-size:.72rem;color:#5a6277;letter-spacing:.05em;text-transform:uppercase;margin-bottom:.4rem}
  input[type=password],input[type=text]{width:100%;background:#0b0d18;border:1px solid #1a1d2e;color:#c4cbde;border-radius:9px;padding:.65rem .9rem;font-size:.875rem;outline:none;transition:border .15s}
  input:focus{border-color:var(--ox);box-shadow:0 0 0 3px var(--ox-sf)}
  input::placeholder{color:#3a3f52}
  .hint{font-size:.72rem;color:#5a6277;margin-top:.3rem}
  .strength-bar{height:4px;border-radius:50rem;background:#1a1d2e;overflow:hidden;margin-top:.5rem}
  .strength-fill{height:100%;border-radius:50rem;transition:width .3s,background .3s;width:0}
  .btn-setup{width:100%;background:var(--ox);color:#fff;border:none;border-radius:10px;padding:.8rem;font-size:.9rem;font-weight:600;cursor:pointer;margin-top:.5rem;transition:background .15s;display:flex;align-items:center;justify-content:center;gap:.5rem}
  .btn-setup:hover{background:#4959b8}
  .btn-setup:disabled{opacity:.5;pointer-events:none}
  .totp-toggle{background:#0b0d18;border:1px solid #1a1d2e;border-radius:10px;padding:1rem;margin-bottom:1.1rem}
  .totp-toggle label{display:flex;align-items:center;gap:.65rem;cursor:pointer;text-transform:none;letter-spacing:0;font-size:.85rem;color:#c4cbde;margin:0}
  .totp-toggle .toggle-icon{width:34px;height:34px;border-radius:8px;background:var(--ox-sf);color:var(--ox);display:flex;align-items:center;justify-content:center;flex-shrink:0}
  .totp-section{margin-top:1rem;padding-top:1rem;border-top:1px solid #1a1d2e}
  .qr-wrap{background:#fff;border-radius:10px;padding:.5rem;display:inline-block;margin:0 auto .75rem}
  .manual-key{background:#0b0d18;border:1px solid #1a1d2e;border-radius:8px;padding:.65rem;font-family:monospace;font-size:.82rem;color:var(--ox);word-break:break-all;user-select:all;text-align:center;cursor:copy}
  .req-list{list-style:none;padding:0;margin:.75rem 0 0;display:flex;flex-direction:column;gap:.3rem}
  .req-list li{font-size:.72rem;display:flex;align-items:center;gap:.4rem}
  .req-list li i{font-size:.8rem}
  .req-ok{color:#34d399}.req-bad{color:#5a6277}
  .err-box{background:rgba(239,68,68,.1);border-left:3px solid #ef4444;color:#fca5a5;border-radius:8px;padding:.75rem 1rem;font-size:.82rem;margin-bottom:1rem}
</style>
</head>
<body>
<div class="setup-card">
  <div class="text-center mb-3">
    <img src="{{ asset('vendor/oxalis/schwarzkopf-logo.png') }}" alt="Oxalis"
         style="max-height:52px;max-width:200px;width:auto;object-fit:contain"
         onerror="this.style.display='none'">
  </div>
  <div class="setup-icon"><i class="bi bi-shield-lock-fill"></i></div>
  <h1>Set up admin access</h1>
  <p class="sub">This is a one-time setup. Choose a strong password to protect the Oxalis admin panel — this is where all user data is managed.</p>

  @if($errors->any())
  <div class="err-box"><i class="bi bi-exclamation-triangle me-2"></i>{{ $errors->first() }}</div>
  @endif

  <form method="POST" action="{{ route('oxalis.admin.setup.post') }}" id="setup-form">
    @csrf
    <div class="field">
      <label>Admin password</label>
      <input type="password" name="password" id="pw" placeholder="Minimum 12 characters" autocomplete="new-password">
      <div class="strength-bar"><div class="strength-fill" id="pw-fill"></div></div>
      <ul class="req-list" id="req-list">
        <li id="r-len"><i class="bi bi-circle"></i> At least 12 characters</li>
        <li id="r-upper"><i class="bi bi-circle"></i> One uppercase letter</li>
        <li id="r-num"><i class="bi bi-circle"></i> One number</li>
        <li id="r-sym"><i class="bi bi-circle"></i> One special character</li>
      </ul>
    </div>
    <div class="field">
      <label>Confirm password</label>
      <input type="password" name="password_confirmation" placeholder="Repeat password" autocomplete="new-password">
    </div>

    {{-- TOTP option --}}
    <div class="totp-toggle">
      <label>
        <div class="toggle-icon"><i class="bi bi-phone-fill"></i></div>
        <div style="flex:1">
          <div class="fw-semibold" style="font-size:.85rem;color:#e8ecf8">Enable authenticator app (recommended)</div>
          <div style="font-size:.72rem;color:#5a6277;margin-top:.15rem">Adds a second factor — your phone generates a 6-digit code on every sign-in</div>
        </div>
        <input type="checkbox" name="enable_totp" id="totp-toggle" value="1" style="width:1rem;height:1rem">
      </label>
      <div class="totp-section d-none" id="totp-section">
        <p style="font-size:.78rem;color:#5a6277;margin-bottom:.75rem">Scan this QR code with Google Authenticator, Authy, or 1Password, then enter the 6-digit code to confirm.</p>
        <div class="text-center">
          <div class="qr-wrap">
            <div id="qr-wrap" data-qr="{{ $qrUri }}"></div>
          </div>
        </div>
        <details style="margin-bottom:.75rem">
          <summary style="font-size:.72rem;color:#5a6277;cursor:pointer">Can't scan? Enter key manually</summary>
          <div class="manual-key mt-2" title="Click to copy" onclick="navigator.clipboard.writeText('{{ $secret }}')">{{ $secret }}</div>
        </details>
        <label>6-digit code from your app</label>
        <input type="text" name="totp_code" inputmode="numeric" pattern="\d{6}" maxlength="6"
          placeholder="000000" autocomplete="one-time-code"
          style="text-align:center;font-size:1.2rem;letter-spacing:.2em;font-family:monospace">
      </div>
    </div>

    <button type="submit" class="btn-setup" id="submit-btn" disabled>
      <i class="bi bi-shield-check"></i> Set up admin access
    </button>
  </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/qrcodejs2@0.0.2/qrcode.min.js"></script>
<script>
// QR code
(function(){
  var w=document.getElementById('qr-wrap');
  if(w)new QRCode(w,{text:w.dataset.qr,width:160,height:160,colorDark:'#000',colorLight:'#fff',correctLevel:QRCode.CorrectLevel.M});
})();

// TOTP toggle
document.getElementById('totp-toggle').addEventListener('change',function(){
  document.getElementById('totp-section').classList.toggle('d-none',!this.checked);
});

// Password strength
var pw=document.getElementById('pw'),fill=document.getElementById('pw-fill'),btn=document.getElementById('submit-btn');
function req(id,ok){var el=document.getElementById(id);el.className=ok?'req-ok':'req-bad';el.querySelector('i').className=ok?'bi bi-check-circle-fill':'bi bi-circle';}
pw.addEventListener('input',function(){
  var v=this.value,s=0;
  var len=v.length>=12,upper=/[A-Z]/.test(v),num=/[0-9]/.test(v),sym=/[^A-Za-z0-9]/.test(v);
  req('r-len',len);req('r-upper',upper);req('r-num',num);req('r-sym',sym);
  s=(len?1:0)+(upper?1:0)+(num?1:0)+(sym?1:0);
  var colors=['','#ef4444','#f59e0b','#3b82f6','#10b981'],pct=[0,25,50,75,100];
  fill.style.width=pct[s]+'%';fill.style.background=colors[s]||'#ef4444';
  btn.disabled=!(len&&upper&&num&&sym);
});
</script>
</body>
</html>
