<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title','Dashboard') — Oxalis Admin</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
  :root{
    --ox:#5c6ac4;--ox-dk:#4959b8;--ox-sf:rgba(92,106,196,.15);
    --adm-bg:#080a12;--adm-side:#0b0d18;--adm-card:#0f1220;
    --adm-border:#1a1d2e;--adm-text:#c4cbde;--adm-muted:#5a6277;
    --adm-head:#e8ecf8;--side-w:220px;
  }
  *{box-sizing:border-box}
  body{margin:0;font-family:system-ui,-apple-system,sans-serif;background:var(--adm-bg);color:var(--adm-text);font-size:.875rem;display:flex;min-height:100vh}
  /* Sidebar */
  #adm-side{width:var(--side-w);flex-shrink:0;background:var(--adm-side);border-right:1px solid var(--adm-border);display:flex;flex-direction:column;position:fixed;top:0;left:0;height:100vh;overflow-y:auto;z-index:100}
  #adm-side::-webkit-scrollbar{width:3px}
  #adm-side::-webkit-scrollbar-thumb{background:var(--adm-border)}
  .adm-logo{padding:1.25rem 1.25rem .9rem;border-bottom:1px solid var(--adm-border)}
  .adm-logo .brand{font-size:.95rem;font-weight:700;letter-spacing:-.02em;color:var(--adm-head)}
  .adm-logo .brand span{color:var(--ox)}
  .adm-logo .sub{font-size:.65rem;color:var(--adm-muted);letter-spacing:.08em;text-transform:uppercase;margin-top:.2rem}
  .adm-nav{padding:.75rem 0;flex:1}
  .adm-nav-section{font-size:.6rem;letter-spacing:.14em;text-transform:uppercase;font-weight:700;color:var(--adm-muted);padding:.8rem 1.25rem .3rem}
  .adm-nav-link{display:flex;align-items:center;gap:.65rem;padding:.45rem 1.25rem;color:var(--adm-muted);text-decoration:none;font-size:.82rem;border-left:2px solid transparent;transition:all .15s}
  .adm-nav-link i{font-size:.95rem;width:1rem;text-align:center}
  .adm-nav-link:hover{color:var(--adm-head);background:rgba(255,255,255,.03)}
  .adm-nav-link.active{color:var(--ox);border-left-color:var(--ox);background:var(--ox-sf)}
  .adm-side-footer{padding:1rem 1.25rem;border-top:1px solid var(--adm-border)}
  .adm-side-footer .last-login{font-size:.68rem;color:var(--adm-muted);line-height:1.5}
  /* Main */
  #adm-main{margin-left:var(--side-w);flex:1;display:flex;flex-direction:column;min-height:100vh}
  /* Top bar */
  #adm-topbar{background:var(--adm-side);border-bottom:1px solid var(--adm-border);padding:.75rem 1.75rem;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:50}
  .adm-breadcrumb{font-size:.78rem;color:var(--adm-muted);display:flex;align-items:center;gap:.4rem}
  .adm-breadcrumb span{color:var(--adm-head)}
  .adm-session-badge{display:flex;align-items:center;gap:.75rem;font-size:.75rem;color:var(--adm-muted)}
  .adm-session-dot{width:7px;height:7px;border-radius:50%;background:#10b981;box-shadow:0 0 6px rgba(16,185,129,.6);flex-shrink:0}
  /* Content */
  #adm-content{padding:1.75rem;flex:1}
  /* Cards */
  .adm-card{background:var(--adm-card);border:1px solid var(--adm-border);border-radius:12px;padding:1.25rem}
  .adm-kpi{display:flex;align-items:center;gap:.9rem}
  .adm-kpi-icon{width:42px;height:42px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0}
  .adm-kpi-val{font-size:1.5rem;font-weight:700;letter-spacing:-.03em;color:var(--adm-head);line-height:1}
  .adm-kpi-label{font-size:.7rem;color:var(--adm-muted);margin-top:.2rem;letter-spacing:.03em}
  /* Section label */
  .adm-section{font-size:.65rem;letter-spacing:.12em;text-transform:uppercase;font-weight:700;color:var(--adm-muted);margin-bottom:.75rem;margin-top:1.75rem}
  .adm-section:first-child{margin-top:0}
  /* Table */
  .adm-table-wrap{border-radius:12px;border:1px solid var(--adm-border);overflow:hidden;background:var(--adm-card)}
  .adm-table{width:100%;border-collapse:collapse;font-size:.78rem}
  .adm-table thead tr{border-bottom:1px solid var(--adm-border);background:rgba(255,255,255,.02)}
  .adm-table th{padding:.65rem 1rem;text-align:left;color:var(--adm-muted);font-weight:500;font-size:.68rem;letter-spacing:.08em;text-transform:uppercase;white-space:nowrap}
  .adm-table td{padding:.65rem 1rem;border-bottom:1px solid rgba(255,255,255,.035);vertical-align:middle;color:var(--adm-text)}
  .adm-table tbody tr:last-child td{border-bottom:none}
  .adm-table tbody tr:hover td{background:rgba(255,255,255,.025)}
  /* Badges */
  .adm-badge{display:inline-flex;align-items:center;gap:.25rem;font-size:.68rem;padding:.18rem .55rem;border-radius:50rem;font-weight:500}
  .adm-badge-ox{background:var(--ox-sf);color:var(--ox)}
  .adm-badge-green{background:rgba(16,185,129,.12);color:#34d399}
  .adm-badge-red{background:rgba(239,68,68,.12);color:#f87171}
  .adm-badge-gray{background:rgba(255,255,255,.06);color:var(--adm-muted)}
  /* Search/Filter bar */
  .adm-toolbar{display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;margin-bottom:1rem}
  .adm-search{background:var(--adm-card);border:1px solid var(--adm-border);color:var(--adm-text);border-radius:8px;padding:.45rem .85rem;font-size:.82rem;outline:none;width:260px;transition:border .15s}
  .adm-search:focus{border-color:var(--ox)}
  .adm-search::placeholder{color:var(--adm-muted)}
  .adm-filter-btn{background:transparent;border:1px solid var(--adm-border);color:var(--adm-muted);border-radius:8px;padding:.4rem .85rem;font-size:.78rem;cursor:pointer;transition:all .15s;text-decoration:none;display:inline-block}
  .adm-filter-btn:hover,.adm-filter-btn.active{border-color:var(--ox);color:var(--ox);background:var(--ox-sf)}
  /* Alerts */
  .adm-alert{border-radius:10px;padding:.9rem 1.1rem;margin-bottom:1.25rem;display:flex;align-items:flex-start;gap:.75rem;font-size:.83rem}
  .adm-alert-err{background:rgba(239,68,68,.1);border-left:3px solid #ef4444;color:#fca5a5}
  .adm-alert-ok{background:rgba(16,185,129,.1);border-left:3px solid #10b981;color:#6ee7b7}
  /* Forms */
  .adm-input{background:var(--adm-card);border:1px solid var(--adm-border);color:var(--adm-text);border-radius:9px;padding:.6rem .9rem;font-size:.875rem;width:100%;outline:none;transition:border .15s}
  .adm-input:focus{border-color:var(--ox);box-shadow:0 0 0 3px var(--ox-sf)}
  .adm-input::placeholder{color:var(--adm-muted)}
  .adm-label{display:block;font-size:.75rem;color:var(--adm-muted);letter-spacing:.04em;margin-bottom:.4rem}
  .btn-adm{background:var(--ox);color:#fff;border:none;border-radius:9px;padding:.6rem 1.4rem;font-size:.875rem;font-weight:600;cursor:pointer;transition:background .15s;display:inline-flex;align-items:center;gap:.5rem}
  .btn-adm:hover{background:var(--ox-dk)}
  .btn-adm:disabled{opacity:.5;pointer-events:none}
  .btn-adm-ghost{background:transparent;color:var(--adm-muted);border:1px solid var(--adm-border);border-radius:9px;padding:.6rem 1.4rem;font-size:.875rem;cursor:pointer;transition:all .15s}
  .btn-adm-ghost:hover{border-color:var(--ox);color:var(--ox)}
  /* Mobile */
  @media(max-width:768px){
    #adm-side{transform:translateX(-100%);transition:transform .25s;position:fixed;z-index:200}
    #adm-side.open{transform:translateX(0)}
    #adm-main{margin-left:0}
    #adm-content{padding:1rem}
  }
</style>
</head>
<body>

<aside id="adm-side">
  <div class="adm-logo">
    <img src="{{ asset('vendor/oxalis/oxalis-logo.png') }}" alt="Oxalis"
         style="max-height:40px;max-width:150px;width:auto;object-fit:contain;display:block;margin-bottom:.35rem"
         onerror="this.style.display='none'"
         onload="var t=document.getElementById('adm-brand-text');if(t)t.style.display='none'">
    <div class="brand" id="adm-brand-text">JULIO <span>OXALIS</span></div>
    <div class="sub">Admin panel</div>
  </div>
  <nav class="adm-nav">
    <div class="adm-nav-section">Monitoring</div>
    <a class="adm-nav-link {{ request()->routeIs('oxalis.admin') && !request()->routeIs('oxalis.admin.*') ? 'active' : '' }}"
       href="{{ route('oxalis.admin') }}">
      <i class="bi bi-speedometer2"></i> Dashboard
    </a>

    <div class="adm-nav-section">Data</div>
    <a class="adm-nav-link {{ request()->routeIs('oxalis.admin.events') ? 'active' : '' }}"
       href="{{ route('oxalis.admin.events') }}">
      <i class="bi bi-activity"></i> Auth events
    </a>
    <a class="adm-nav-link {{ request()->routeIs('oxalis.admin.lockouts') ? 'active' : '' }}"
       href="{{ route('oxalis.admin.lockouts') }}">
      <i class="bi bi-ban"></i> Lockouts
    </a>

    <div class="adm-nav-section">Tools</div>
    <a class="adm-nav-link {{ request()->routeIs('oxalis.admin.invites*') ? 'active' : '' }}"
       href="{{ route('oxalis.admin.invites') }}">
      <i class="bi bi-ticket-perforated"></i> Invite codes
    </a>
    <a class="adm-nav-link {{ request()->routeIs('oxalis.admin.webhooks*') ? 'active' : '' }}"
       href="{{ route('oxalis.admin.webhooks') }}">
      <i class="bi bi-send-fill"></i> Webhooks
    </a>

    <div class="adm-nav-section">Settings</div>
    <a class="adm-nav-link {{ request()->routeIs('oxalis.admin.password') ? 'active' : '' }}"
       href="{{ route('oxalis.admin.password') }}">
      <i class="bi bi-key-fill"></i> Change password
    </a>

    <div class="adm-nav-section">Session</div>
    <form action="{{ route('oxalis.admin.logout') }}" method="POST" class="px-0">
      @csrf
      <button type="submit" class="adm-nav-link w-100 border-0 text-start" style="background:none;cursor:pointer">
        <i class="bi bi-box-arrow-left"></i> Sign out
      </button>
    </form>
  </nav>
  @php $cred = \Oxalis\Models\AdminCredential::first(); @endphp
  @if($cred?->last_login_at)
  <div class="adm-side-footer">
    <div class="last-login">
      <div><i class="bi bi-clock me-1"></i>Last login</div>
      <div>{{ $cred->last_login_at->diffForHumans() }}</div>
      <div class="font-monospace" style="font-size:.63rem">{{ $cred->last_login_ip }}</div>
    </div>
  </div>
  @endif
</aside>

<div id="adm-main">
  <div id="adm-topbar">
    <div class="adm-breadcrumb">
      <i class="bi bi-shield-fill" style="color:var(--ox)"></i>
      Oxalis Admin &rsaquo; <span>@yield('title','Dashboard')</span>
    </div>
    <div class="adm-session-badge">
      <div class="adm-session-dot"></div>
      <span>Admin session active</span>
      @if($cred?->hasTotpEnabled())
      <span class="adm-badge adm-badge-green"><i class="bi bi-shield-check"></i> 2FA</span>
      @endif
    </div>
  </div>

  <div id="adm-content">
    @if(session('admin_success'))
    <div class="adm-alert adm-alert-ok"><i class="bi bi-check-circle-fill"></i>{{ session('admin_success') }}</div>
    @endif
    @if(session('admin_error'))
    <div class="adm-alert adm-alert-err"><i class="bi bi-exclamation-triangle-fill"></i>{{ session('admin_error') }}</div>
    @endif
    @if($errors->any())
    <div class="adm-alert adm-alert-err">
      @foreach($errors->all() as $e)<div><i class="bi bi-x-circle me-1"></i>{{ $e }}</div>@endforeach
    </div>
    @endif
    @yield('content')
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
