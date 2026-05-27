@php
  $oxTheme = config('oxalis.theme', 'indigo');
  $oxColor  = config('oxalis.theme_color');
  $oxDark   = in_array($oxTheme, ['neon','aurora','obsidian','ember']);
@endphp
<!DOCTYPE html>
<html lang="en" data-ox-theme="{{ $oxTheme }}" data-bs-theme="{{ $oxDark ? 'dark' : 'auto' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }}@hasSection('title') &middot; @yield('title')@endif</title>
    {{-- Force dark/light before Bootstrap paints to avoid flash --}}
    @if($oxDark)
    <script>document.documentElement.setAttribute('data-bs-theme','dark');</script>
    @else
    <script>(function(){var p=localStorage.getItem('ox-theme')||'auto';document.documentElement.setAttribute('data-bs-theme',p==='auto'?(window.matchMedia('(prefers-color-scheme:dark)').matches?'dark':'light'):p);})();</script>
    @endif
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @if($oxTheme === 'custom' && file_exists(public_path('vendor/oxalis/theme.css')))
    <link rel="stylesheet" href="{{ asset('vendor/oxalis/theme.css') }}">
    @endif
    <style>
    /* ─── THEME: indigo (default) ──────────────────────────────────────────── */
    [data-ox-theme=indigo]{
      --ox:{{ ($oxTheme==='indigo' && $oxColor) ? $oxColor : '#5c6ac4' }};
      --ox-dk:#4959b8;--ox-sf:rgba(92,106,196,.12);--ox-r:14px;
      --ox-btn-fg:#fff;--ox-btn-radius:50rem;
      --ox-font:system-ui,-apple-system,sans-serif;--ox-body-bg:#f0f2fd;
    }
    [data-ox-theme=indigo][data-bs-theme=dark]{
      --bs-body-bg:#0d0f18;--bs-card-bg:#181b2a;--bs-border-color:#252840;--ox-body-bg:#0d0f18;
    }

    /* ─── THEME: neon (cyberpunk) ───────────────────────────────────────────── */
    [data-ox-theme=neon]{
      --ox:#00f5ff;--ox-dk:#00c8d4;--ox-sf:rgba(0,245,255,.07);--ox-r:3px;
      --ox-btn-fg:#07090e;--ox-btn-radius:3px;
      --ox-font:'Courier New',Courier,monospace;--ox-body-bg:#07090e;
      --bs-body-bg:#07090e;--bs-card-bg:#0e1117;--bs-border-color:#00f5ff28;
      --bs-body-color:#c8f0f2;--bs-secondary-color:#4a8a8f;
    }
    [data-ox-theme=neon] .ox-card{
      border-color:#00f5ff30;
      box-shadow:0 0 0 1px #00f5ff18,0 4px 40px rgba(0,245,255,.06);
    }
    [data-ox-theme=neon] .btn-ox{
      box-shadow:0 0 18px rgba(0,245,255,.2);letter-spacing:.07em;
      text-transform:uppercase;font-size:.82rem;
    }
    [data-ox-theme=neon] .btn-ox:hover{box-shadow:0 0 30px rgba(0,245,255,.45);}
    [data-ox-theme=neon] .form-control{
      background:rgba(0,245,255,.03);border-color:#00f5ff28;color:#c8f0f2;
    }
    [data-ox-theme=neon] .form-control:focus{
      background:rgba(0,245,255,.05)!important;border-color:#00f5ff!important;
      box-shadow:0 0 0 2px rgba(0,245,255,.12)!important;
    }
    [data-ox-theme=neon] h5,[data-ox-theme=neon] h4{
      letter-spacing:.1em;text-transform:uppercase;font-size:.88rem;
    }
    [data-ox-theme=neon] .btn-ox-out{color:#00f5ff;border-color:#00f5ff40;}
    [data-ox-theme=neon] .btn-ox-out:hover{background:#00f5ff;color:#07090e;}

    /* ─── THEME: aurora (glassmorphism) ────────────────────────────────────── */
    [data-ox-theme=aurora]{
      --ox:#a78bfa;--ox-dk:#7c3aed;--ox-sf:rgba(167,139,250,.1);--ox-r:22px;
      --ox-btn-fg:#fff;--ox-btn-radius:50rem;
      --ox-font:system-ui,-apple-system,sans-serif;--ox-body-bg:#08001a;
      --bs-body-bg:#08001a;--bs-card-bg:rgba(255,255,255,.05);
      --bs-border-color:rgba(255,255,255,.09);
      --bs-body-color:#e4dbff;--bs-secondary-color:#8b7fc0;
    }
    [data-ox-theme=aurora] body{
      background:linear-gradient(135deg,#08001a 0%,#0d1b32 50%,#001a10 100%)!important;
      background-attachment:fixed!important;
    }
    [data-ox-theme=aurora] .ox-card{
      backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);
      box-shadow:0 8px 32px rgba(0,0,0,.45),inset 0 1px 0 rgba(255,255,255,.06);
    }
    [data-ox-theme=aurora] .form-control{
      background:rgba(255,255,255,.05);border-color:rgba(255,255,255,.1);color:#e4dbff;
    }

    /* ─── THEME: obsidian (brutalist minimal) ──────────────────────────────── */
    [data-ox-theme=obsidian]{
      --ox:#fff;--ox-dk:#d4d4d4;--ox-sf:rgba(255,255,255,.05);--ox-r:0px;
      --ox-btn-fg:#000;--ox-btn-radius:0px;
      --ox-font:'Courier New',Courier,monospace;--ox-body-bg:#000;
      --bs-body-bg:#000;--bs-card-bg:#000;--bs-border-color:#2a2a2a;
      --bs-body-color:#fff;--bs-secondary-color:#777;
    }
    [data-ox-theme=obsidian] .ox-card{
      border:1px solid #2a2a2a;box-shadow:none;
    }
    [data-ox-theme=obsidian] .form-control{
      background:transparent;border:none;border-bottom:2px solid #2a2a2a;
      border-radius:0;color:#fff;padding-left:0;
    }
    [data-ox-theme=obsidian] .form-control:focus{
      border-bottom-color:#fff!important;box-shadow:none!important;
    }
    [data-ox-theme=obsidian] .form-floating>label{left:0;}
    [data-ox-theme=obsidian] .btn-ox{
      border:2px solid #fff;letter-spacing:.1em;text-transform:uppercase;
    }
    [data-ox-theme=obsidian] .btn-ox-out{
      border-color:#fff;color:#fff;
    }
    [data-ox-theme=obsidian] .btn-ox-out:hover{background:#fff;color:#000;}
    [data-ox-theme=obsidian] h5,[data-ox-theme=obsidian] h4{
      text-transform:uppercase;letter-spacing:.14em;font-size:.88rem;
    }

    /* ─── THEME: ember (warm dark) ──────────────────────────────────────────── */
    [data-ox-theme=ember]{
      --ox:#f59e0b;--ox-dk:#d97706;--ox-sf:rgba(245,158,11,.1);--ox-r:10px;
      --ox-btn-fg:#1a0e00;--ox-btn-radius:8px;
      --ox-font:system-ui,-apple-system,sans-serif;--ox-body-bg:#100a02;
      --bs-body-bg:#100a02;--bs-card-bg:#1c1206;--bs-border-color:#3a2810;
      --bs-body-color:#f5deb3;--bs-secondary-color:#8a6a3a;
    }
    [data-ox-theme=ember] .ox-card{box-shadow:0 4px 24px rgba(0,0,0,.6);}
    [data-ox-theme=ember] .btn-ox:hover{box-shadow:0 0 22px rgba(245,158,11,.3);}
    [data-ox-theme=ember] .form-control{
      background:rgba(245,158,11,.04);border-color:#3a2810;color:#f5deb3;
    }
    [data-ox-theme=ember] .form-control:focus{
      border-color:#f59e0b!important;box-shadow:0 0 0 2px rgba(245,158,11,.15)!important;
    }

    /* ─── THEME: frost (light glassmorphism) ────────────────────────────────── */
    [data-ox-theme=frost]{
      --ox:#38bdf8;--ox-dk:#0ea5e9;--ox-sf:rgba(56,189,248,.12);--ox-r:20px;
      --ox-btn-fg:#0c3c5c;--ox-btn-radius:50rem;
      --ox-font:system-ui,-apple-system,sans-serif;--ox-body-bg:#dbeafe;
      --bs-body-bg:#dbeafe;--bs-border-color:rgba(56,189,248,.25);
      --bs-body-color:#0c3c5c;--bs-secondary-color:#4d8aa8;
    }
    [data-ox-theme=frost] .ox-card{
      background:rgba(255,255,255,.72)!important;
      backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);
      box-shadow:0 8px 32px rgba(56,189,248,.15),0 1px 3px rgba(0,0,0,.06);
    }
    [data-ox-theme=frost] .form-control{background:rgba(255,255,255,.6);}

    /* ─── BASE ──────────────────────────────────────────────────────────────── */
    body{
      min-height:100vh;display:flex;flex-direction:column;
      align-items:center;justify-content:center;
      font-family:var(--ox-font,system-ui,-apple-system,sans-serif);
      background:var(--ox-body-bg,var(--bs-body-bg,#f4f5fb));
    }
    .ox-wrap{width:100%;max-width:440px;padding:0 1rem}
    .ox-card{
      border-radius:var(--ox-r);border:1px solid var(--bs-border-color);
      background:var(--bs-card-bg,#fff);box-shadow:0 4px 24px rgba(0,0,0,.07);
      padding:2rem 2rem 1.75rem;
    }
    .btn-ox{
      background:var(--ox);color:var(--ox-btn-fg,#fff);border:none;
      border-radius:var(--ox-btn-radius,50rem);font-weight:600;
      font-family:var(--ox-font);transition:background .15s,box-shadow .15s;
    }
    .btn-ox:hover,.btn-ox:focus{background:var(--ox-dk);color:var(--ox-btn-fg,#fff);}
    .btn-ox:disabled{opacity:.55;pointer-events:none;}
    .btn-ox-out{
      background:transparent;color:var(--ox);border:2px solid var(--ox);
      border-radius:var(--ox-btn-radius,50rem);font-weight:500;
      font-family:var(--ox-font);transition:all .15s;
    }
    .btn-ox-out:hover{background:var(--ox);color:var(--ox-btn-fg,#fff);}
    .ox-div{
      display:flex;align-items:center;gap:.6rem;
      color:var(--bs-secondary-color);font-size:.78rem;margin:1rem 0;
    }
    .ox-div::before,.ox-div::after{content:'';flex:1;border-top:1px solid var(--bs-border-color);}
    .form-control:focus{border-color:var(--ox)!important;box-shadow:0 0 0 .2rem var(--ox-sf)!important;}
    a{color:var(--ox);}a:hover{color:var(--ox-dk);}
    </style>
</head>
<body>
<div class="ox-wrap">
@if(session('status'))
<div class="alert rounded-3 border-0 mb-3" style="background:rgba(25,135,84,.1);color:#198754"><i class="bi bi-check-circle me-2"></i>{{ session('status') }}</div>
@endif
@if($errors->any())
<div class="alert rounded-3 border-0 mb-3" style="background:rgba(220,53,69,.1);color:#dc3545">
@foreach($errors->all() as $e)<div><i class="bi bi-exclamation-circle me-1"></i>{{ $e }}</div>@endforeach
</div>
@endif
<div class="ox-card">@yield('content')</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
