@php
  $oxTheme  = config('oxalis.theme', 'indigo');
  $oxColor  = config('oxalis.theme_color');
  $oxLayout = config('oxalis.layout', 'card');
  $oxDark   = in_array($oxTheme, ['neon','aurora','obsidian','ember']);

  // Derive all color tokens from OXALIS_PRIMARY_COLOR (any theme)
  $oxDerived = null;
  if ($oxColor && preg_match('/^#([0-9a-fA-F]{6})$/', $oxColor, $m)) {
      $r  = hexdec(substr($m[1], 0, 2));
      $g  = hexdec(substr($m[1], 2, 2));
      $b  = hexdec(substr($m[1], 4, 2));
      $oxDerived = [
          'ox'     => $oxColor,
          'ox-dk'  => sprintf('#%02x%02x%02x', max(0,(int)($r*.88)), max(0,(int)($g*.88)), max(0,(int)($b*.88))),
          'ox-sf'  => "rgba({$r},{$g},{$b},.12)",
          'btn-fg' => ((0.299*$r + 0.587*$g + 0.114*$b)/255 > 0.5) ? '#000' : '#fff',
      ];
  }
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
          integrity="sha384-XGjxtQfXaH2tnPFa9x+ruJTuLE3Aa6LhHSWRr1XeTyhezb4abCG4ccI5AkVDxqC+" crossorigin="anonymous">
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
      --bs-body-color:#c8f0f2;--bs-secondary-color:#7ac8ce;
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
      --bs-body-color:#e4dbff;--bs-secondary-color:#b0a4e0;
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
      --bs-body-color:#fff;--bs-secondary-color:#999;
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
      --bs-body-color:#f5deb3;--bs-secondary-color:#c08850;
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

    /* ─── SPLIT LAYOUT ──────────────────────────────────────────────────────── */
    body.ox-layout-split{align-items:stretch;justify-content:stretch;padding:0}
    .ox-split-root{display:flex;min-height:100vh;width:100%}
    .ox-split-brand{
      width:40%;background:var(--ox);color:var(--ox-btn-fg,#fff);
      display:flex;flex-direction:column;align-items:center;justify-content:center;
      padding:3rem 2.5rem;text-align:center;position:sticky;top:0;height:100vh;
    }
    .ox-split-form{
      flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;
      padding:2rem 1.5rem;background:var(--ox-body-bg,var(--bs-body-bg,#f4f5fb));
      min-height:100vh;overflow-y:auto;
    }
    .ox-split-form .ox-wrap{max-width:420px;padding:0}
    @media(max-width:768px){
      body.ox-layout-split{flex-direction:column}
      .ox-split-root{flex-direction:column}
      .ox-split-brand{width:100%;height:auto;min-height:160px;position:static;padding:2rem}
      .ox-split-form{min-height:auto}
    }

    /* ─── BARE LAYOUT ───────────────────────────────────────────────────────── */
    body.ox-layout-bare{align-items:stretch;justify-content:flex-start;padding:2rem}
    .ox-bare-root{width:100%;max-width:100%}

    /* ─── UNIVERSAL: form readability across all themes ────────────────────── */
    .form-control{color:var(--bs-body-color)}
    .form-control::placeholder{color:var(--bs-secondary-color);opacity:.8}
    .form-floating>label{color:var(--bs-secondary-color);background:transparent}
    .form-floating>.form-control:focus~label,
    .form-floating>.form-control:not(:placeholder-shown)~label{opacity:1}
    .form-check-label{color:var(--bs-secondary-color)}
    .form-check-input:not(:checked){
      background-color:var(--bs-tertiary-bg,rgba(0,0,0,.06));
      border-color:var(--bs-border-color)
    }
    /* Dark-theme tier badges */
    [data-bs-theme=dark] .ox-tier-best{background:rgba(25,135,84,.22);color:#5fd194}
    [data-bs-theme=dark] .ox-tier-good{background:rgba(13,110,253,.22);color:#7eb4ff}
    [data-bs-theme=dark] .ox-tier-basic{background:rgba(255,193,7,.18);color:#ffd060}
    /* Alert text on dark themes */
    [data-bs-theme=dark] .alert{color:inherit}
    /* Neon: form control text + checkbox */
    [data-ox-theme=neon] .form-check-input:not(:checked){
      background-color:rgba(0,245,255,.04);border-color:rgba(0,245,255,.2)
    }
    /* Frost: badges need darker text on light background */
    [data-ox-theme=frost] .ox-tier-best{background:rgba(25,135,84,.1);color:#146435}
    [data-ox-theme=frost] .ox-tier-good{background:rgba(13,110,253,.1);color:#0847b5}
    [data-ox-theme=frost] .ox-tier-basic{background:rgba(150,100,0,.1);color:#7a5200}
    </style>
    {{-- Primary color override — inlined after theme so it wins specificity --}}
    @if($oxDerived)
    <style>
    [data-ox-theme={{ $oxTheme }}]{
      --ox:{{ $oxDerived['ox'] }};
      --ox-dk:{{ $oxDerived['ox-dk'] }};
      --ox-sf:{{ $oxDerived['ox-sf'] }};
      --ox-btn-fg:{{ $oxDerived['btn-fg'] }};
    }
    </style>
    @endif
    @stack('styles')
</head>
<body class="ox-layout-{{ $oxLayout }}">

@if($oxLayout === 'split')
{{-- ── Split: brand panel left, form right ─────────────────────────────── --}}
<div class="ox-split-root">
    <aside class="ox-split-brand">
        @include('oxalis::partials.split-brand')
    </aside>
    <div class="ox-split-form">
        @if(session('status'))
        <div class="alert rounded-3 border-0 mb-3 w-100" style="max-width:420px;background:rgba(25,135,84,.1);color:#198754"><i class="bi bi-check-circle me-2"></i>{{ session('status') }}</div>
        @endif
        @if($errors->any())
        <div class="alert rounded-3 border-0 mb-3 w-100" style="max-width:420px;background:rgba(220,53,69,.1);color:#dc3545">
        @foreach($errors->all() as $e)<div><i class="bi bi-exclamation-circle me-1"></i>{{ $e }}</div>@endforeach
        </div>
        @endif
        @stack('oxalis:before-card')
        <div class="ox-wrap">
            <div class="ox-card">
                @stack('oxalis:card-top')
                @include('oxalis::partials.card-header')
                @yield('content')
                @stack('oxalis:card-bottom')
            </div>
        </div>
        @stack('oxalis:after-card')
    </div>
</div>

@elseif($oxLayout === 'bare')
{{-- ── Bare: no card, form floats directly on body bg ──────────────────── --}}
<div class="ox-bare-root">
    @if(session('status'))
    <div class="alert rounded-3 border-0 mb-3" style="background:rgba(25,135,84,.1);color:#198754"><i class="bi bi-check-circle me-2"></i>{{ session('status') }}</div>
    @endif
    @if($errors->any())
    <div class="alert rounded-3 border-0 mb-3" style="background:rgba(220,53,69,.1);color:#dc3545">
    @foreach($errors->all() as $e)<div><i class="bi bi-exclamation-circle me-1"></i>{{ $e }}</div>@endforeach
    </div>
    @endif
    @stack('oxalis:before-card')
    @include('oxalis::partials.card-header')
    @yield('content')
    @stack('oxalis:after-card')
</div>

@else
{{-- ── Card (default): centered card on body bg ────────────────────────── --}}
<div class="ox-wrap">
    @if(session('status'))
    <div class="alert rounded-3 border-0 mb-3" style="background:rgba(25,135,84,.1);color:#198754"><i class="bi bi-check-circle me-2"></i>{{ session('status') }}</div>
    @endif
    @if($errors->any())
    <div class="alert rounded-3 border-0 mb-3" style="background:rgba(220,53,69,.1);color:#dc3545">
    @foreach($errors->all() as $e)<div><i class="bi bi-exclamation-circle me-1"></i>{{ $e }}</div>@endforeach
    </div>
    @endif
    @stack('oxalis:before-card')
    <div class="ox-card">
        @stack('oxalis:card-top')
        @include('oxalis::partials.card-header')
        @yield('content')
        @stack('oxalis:card-bottom')
    </div>
    @stack('oxalis:after-card')
</div>
@endif

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
@stack('scripts')
</body>
</html>
