<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin sign in — Oxalis</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
  :root{--ox:#5c6ac4;--ox-sf:rgba(92,106,196,.15)}
  body{background:#080a12;color:#c4cbde;font-family:system-ui,-apple-system,sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem 1rem}
  .login-card{width:100%;max-width:400px;background:#0f1220;border:1px solid #1a1d2e;border-radius:16px;padding:2.25rem 2rem}
  .login-icon{width:56px;height:56px;border-radius:16px;background:var(--ox-sf);color:var(--ox);display:flex;align-items:center;justify-content:center;font-size:1.5rem;margin:0 auto 1.25rem}
  h1{font-size:1.3rem;font-weight:700;color:#e8ecf8;text-align:center;letter-spacing:-.02em;margin-bottom:.35rem}
  .sub{color:#5a6277;font-size:.83rem;text-align:center;margin-bottom:2rem;line-height:1.6}
  .field{margin-bottom:1.1rem}
  label{display:block;font-size:.72rem;color:#5a6277;letter-spacing:.05em;text-transform:uppercase;margin-bottom:.4rem}
  input[type=password],input[type=text]{width:100%;background:#0b0d18;border:1px solid #1a1d2e;color:#c4cbde;border-radius:9px;padding:.65rem .9rem;font-size:.875rem;outline:none;transition:border .15s}
  input:focus{border-color:var(--ox);box-shadow:0 0 0 3px var(--ox-sf)}
  input::placeholder{color:#3a3f52}
  .btn-login{width:100%;background:var(--ox);color:#fff;border:none;border-radius:10px;padding:.8rem;font-size:.9rem;font-weight:600;cursor:pointer;margin-top:.25rem;transition:background .15s;display:flex;align-items:center;justify-content:center;gap:.5rem}
  .btn-login:hover{background:#4959b8}
  .err-box{background:rgba(239,68,68,.1);border-left:3px solid #ef4444;color:#fca5a5;border-radius:8px;padding:.75rem 1rem;font-size:.82rem;margin-bottom:1rem;display:flex;align-items:center;gap:.6rem}
  .ok-box{background:rgba(16,185,129,.1);border-left:3px solid #10b981;color:#6ee7b7;border-radius:8px;padding:.75rem 1rem;font-size:.82rem;margin-bottom:1rem;display:flex;align-items:center;gap:.6rem}
  .locked-box{background:rgba(245,158,11,.08);border-left:3px solid #f59e0b;color:#fcd34d;border-radius:8px;padding:.75rem 1rem;font-size:.82rem;margin-bottom:1rem}
  .last-login{background:#0b0d18;border:1px solid #1a1d2e;border-radius:8px;padding:.65rem .9rem;font-size:.72rem;color:#5a6277;margin-bottom:1.1rem;display:flex;align-items:center;gap:.6rem}
  .last-login strong{color:#c4cbde}
  .totp-field{background:#0b0d18;border:1px solid var(--ox);border-radius:9px;padding:.75rem;margin-bottom:1rem;text-align:center}
  .totp-field input{border:none;background:transparent;color:#e8ecf8;font-size:1.4rem;letter-spacing:.25em;font-family:monospace;text-align:center;width:100%;outline:none}
  .totp-field label{font-size:.7rem;color:var(--ox);margin-bottom:.35rem}
</style>
</head>
<body>
<div class="login-card">
  <div class="login-icon"><i class="bi bi-shield-fill-lock"></i></div>
  <h1>Admin access</h1>
  <p class="sub">{{ config('app.name') }} · Restricted area</p>

  @if(session('admin_error'))
  <div class="err-box"><i class="bi bi-exclamation-triangle-fill"></i>{{ session('admin_error') }}</div>
  @endif
  @if(session('admin_success'))
  <div class="ok-box"><i class="bi bi-check-circle-fill"></i>{{ session('admin_success') }}</div>
  @endif

  @if($lockedUntil)
  <div class="locked-box">
    <i class="bi bi-ban me-2"></i>
    Too many failed attempts. Access locked until <strong>{{ $lockedUntil->format('H:i:s') }}</strong>.
  </div>
  @else

  @if($cred?->last_login_at)
  <div class="last-login">
    <i class="bi bi-clock"></i>
    Last login: <strong>{{ $cred->last_login_at->diffForHumans() }}</strong>
    from <strong class="font-monospace">{{ $cred->last_login_ip }}</strong>
  </div>
  @endif

  <form method="POST" action="{{ route('oxalis.admin.login.post') }}">
    @csrf
    <div class="field">
      <label>Admin password</label>
      <input type="password" name="password" autofocus autocomplete="current-password" placeholder="Enter admin password">
    </div>

    @if($cred?->hasTotpEnabled() || old('show_totp'))
    <div class="totp-field">
      <label>Authenticator code</label>
      <input type="text" name="totp_code" inputmode="numeric" pattern="\d{6}" maxlength="6"
        placeholder="000000" autocomplete="one-time-code">
    </div>
    @endif

    <input type="hidden" name="show_totp" value="{{ old('show_totp', $cred?->hasTotpEnabled() ? '1' : '0') }}">

    <button type="submit" class="btn-login">
      <i class="bi bi-shield-check"></i> Sign in to admin
    </button>
  </form>
  @endif
</div>
</body>
</html>
