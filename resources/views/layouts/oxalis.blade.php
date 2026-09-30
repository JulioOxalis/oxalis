@php
  $oxTheme     = config('oxalis.theme', 'indigo');
  $oxColor     = config('oxalis.theme_color');
  $oxLayout    = \Oxalis\Support\Branding::layout();
  $oxSplitBg   = config('oxalis.brand.split_bg');
  $oxSplitText = config('oxalis.brand.split_text');

  // Keep the existing contract: these layouts are immersive and dark by design.
  $oxForceDark = in_array($oxLayout, ['bare', 'glass']);
  $oxDark      = in_array($oxTheme, ['neon','aurora','obsidian','ember']) || $oxForceDark;

  // Derive usable UI tokens from OXALIS_PRIMARY_COLOR.
  $oxDerived = null;
  if ($oxColor && preg_match('/^#([0-9a-fA-F]{6})$/', $oxColor, $oxM)) {
      $r = hexdec(substr($oxM[1], 0, 2));
      $g = hexdec(substr($oxM[1], 2, 2));
      $b = hexdec(substr($oxM[1], 4, 2));
      $oxDerived = [
          'ox'     => $oxColor,
          'ox-dk'  => sprintf('#%02x%02x%02x', max(0,(int)($r*.82)), max(0,(int)($g*.82)), max(0,(int)($b*.82))),
          'ox-sf'  => "rgba({$r},{$g},{$b},.13)",
          'ring'   => "rgba({$r},{$g},{$b},.22)",
          'btn-fg' => ((0.299*$r + 0.587*$g + 0.114*$b)/255 > 0.62) ? '#101828' : '#ffffff',
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
    @if($oxDark)
    <script>document.documentElement.setAttribute('data-bs-theme','dark');</script>
    @else
    <script>(function(){var p=localStorage.getItem('ox-theme')||'auto';var m=window.matchMedia('(prefers-color-scheme:dark)').matches?'dark':'light';document.documentElement.setAttribute('data-bs-theme',p==='auto'?m:p);})();</script>
    @endif
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
          integrity="sha384-XGjxtQfXaH2tnPFa9x+ruJTuLE3Aa6LhHSWRr1XeTyhezb4abCG4ccI5AkVDxqC+" crossorigin="anonymous">
    @if($oxTheme === 'custom' && file_exists(public_path('vendor/oxalis/theme.css')))
    <link rel="stylesheet" href="{{ asset('vendor/oxalis/theme.css') }}">
    @endif
    <style>
    :root{
      color-scheme:light dark;
      --ox-font:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;
      --ox-r:22px;
      --ox-btn-radius:14px;
      --ox-btn-fg:#fff;
      --ox-success:#16a34a;
      --ox-danger:#dc2626;
      --ox-focus:rgba(79,70,229,.22);
      --ox-transition:180ms ease;
      --ox:#4f46e5;--ox-dk:#3730a3;--ox-sf:rgba(79,70,229,.11);
      --ox-body-bg:#f6f7fb;--ox-surface:#ffffff;--ox-surface-strong:#ffffff;
      --ox-text:#101828;--ox-muted:#667085;--ox-border:#e4e7ec;
      --ox-shadow:0 24px 70px rgba(16,24,40,.10),0 4px 18px rgba(16,24,40,.06);
      --ox-gradient:linear-gradient(135deg,#4f46e5 0%,#2563eb 58%,#0891b2 100%);
    }

    /* Professional built-in palettes. Existing .env theme names remain supported. */
    [data-ox-theme=indigo]{
      --ox:#4f46e5;--ox-dk:#3730a3;--ox-sf:rgba(79,70,229,.11);--ox-focus:rgba(79,70,229,.22);
      --ox-body-bg:#f6f7fb;--ox-surface:#ffffff;--ox-surface-strong:#ffffff;
      --ox-text:#101828;--ox-muted:#667085;--ox-border:#e4e7ec;
      --ox-shadow:0 24px 70px rgba(16,24,40,.10),0 4px 18px rgba(16,24,40,.06);
      --ox-gradient:linear-gradient(135deg,#4f46e5 0%,#2563eb 58%,#0891b2 100%);
    }
    [data-ox-theme=indigo][data-bs-theme=dark]{
      --ox:#8b8cf6;--ox-dk:#c4b5fd;--ox-sf:rgba(139,140,246,.14);--ox-focus:rgba(139,140,246,.26);
      --ox-body-bg:#0b1020;--ox-surface:#111827;--ox-surface-strong:#172033;
      --ox-text:#f8fafc;--ox-muted:#a7b0c3;--ox-border:#253044;
      --ox-shadow:0 24px 70px rgba(0,0,0,.36),0 4px 18px rgba(0,0,0,.22);
    }

    [data-ox-theme=neon]{
      --ox:#14b8a6;--ox-dk:#5eead4;--ox-sf:rgba(20,184,166,.13);--ox-focus:rgba(20,184,166,.24);
      --ox-r:18px;--ox-btn-radius:13px;--ox-btn-fg:#07111f;
      --ox-body-bg:#07111f;--ox-surface:#0c1726;--ox-surface-strong:#101d30;
      --ox-text:#eef6ff;--ox-muted:#9eb3c7;--ox-border:#1d3347;
      --ox-shadow:0 26px 80px rgba(0,0,0,.42),0 0 0 1px rgba(20,184,166,.04);
      --ox-gradient:linear-gradient(135deg,#14b8a6 0%,#0ea5e9 100%);
    }

    [data-ox-theme=aurora]{
      --ox:#a78bfa;--ox-dk:#c4b5fd;--ox-sf:rgba(167,139,250,.14);--ox-focus:rgba(167,139,250,.27);
      --ox-r:26px;--ox-btn-radius:15px;
      --ox-body-bg:#0c1020;--ox-surface:rgba(18,24,43,.86);--ox-surface-strong:rgba(25,33,58,.92);
      --ox-text:#f5f3ff;--ox-muted:#b9b3d6;--ox-border:rgba(255,255,255,.12);
      --ox-shadow:0 26px 90px rgba(0,0,0,.42),inset 0 1px 0 rgba(255,255,255,.06);
      --ox-gradient:linear-gradient(135deg,#8b5cf6 0%,#06b6d4 55%,#22c55e 100%);
    }

    [data-ox-theme=obsidian]{
      --ox:#e5e7eb;--ox-dk:#ffffff;--ox-sf:rgba(255,255,255,.10);--ox-focus:rgba(255,255,255,.18);
      --ox-r:16px;--ox-btn-radius:12px;--ox-btn-fg:#101828;
      --ox-body-bg:#070707;--ox-surface:#111111;--ox-surface-strong:#171717;
      --ox-text:#f5f5f5;--ox-muted:#a3a3a3;--ox-border:#2c2c2c;
      --ox-shadow:0 26px 80px rgba(0,0,0,.55);
      --ox-gradient:linear-gradient(135deg,#fafafa 0%,#a3a3a3 100%);
    }

    [data-ox-theme=ember]{
      --ox:#f97316;--ox-dk:#fdba74;--ox-sf:rgba(249,115,22,.14);--ox-focus:rgba(249,115,22,.24);
      --ox-r:20px;--ox-btn-radius:14px;--ox-btn-fg:#1f1308;
      --ox-body-bg:#160f0a;--ox-surface:#201610;--ox-surface-strong:#2a1b12;
      --ox-text:#fff7ed;--ox-muted:#d4a983;--ox-border:#3a2719;
      --ox-shadow:0 26px 80px rgba(0,0,0,.45);
      --ox-gradient:linear-gradient(135deg,#f97316 0%,#f59e0b 100%);
    }

    [data-ox-theme=frost]{
      --ox:#0284c7;--ox-dk:#0369a1;--ox-sf:rgba(2,132,199,.12);--ox-focus:rgba(2,132,199,.22);
      --ox-r:24px;--ox-btn-radius:15px;--ox-btn-fg:#ffffff;
      --ox-body-bg:#eef7ff;--ox-surface:rgba(255,255,255,.82);--ox-surface-strong:#ffffff;
      --ox-text:#0f2a3d;--ox-muted:#507085;--ox-border:rgba(14,116,144,.18);
      --ox-shadow:0 24px 70px rgba(14,116,144,.13),0 5px 20px rgba(14,116,144,.08);
      --ox-gradient:linear-gradient(135deg,#0284c7 0%,#22d3ee 100%);
    }

    html[data-bs-theme=dark]{
      --bs-body-bg:var(--ox-body-bg);--bs-body-color:var(--ox-text);
      --bs-card-bg:var(--ox-surface);--bs-border-color:var(--ox-border);
      --bs-secondary-color:var(--ox-muted);color-scheme:dark;
    }

    *{box-sizing:border-box}
    body{
      min-height:100vh;margin:0;display:flex;flex-direction:column;align-items:center;justify-content:center;
      font-family:var(--ox-font);color:var(--ox-text);background:var(--ox-body-bg);
      text-rendering:optimizeLegibility;-webkit-font-smoothing:antialiased;
    }
    body::before{
      content:"";position:fixed;inset:0;z-index:-2;pointer-events:none;
      background:
        radial-gradient(circle at 15% 12%,var(--ox-sf) 0,transparent 32%),
        radial-gradient(circle at 88% 80%,rgba(20,184,166,.10) 0,transparent 30%),
        linear-gradient(180deg,rgba(255,255,255,.36),transparent 36%);
      opacity:.92;
    }
    [data-bs-theme=dark] body::before{background:
      radial-gradient(circle at 12% 10%,var(--ox-sf) 0,transparent 32%),
      radial-gradient(circle at 84% 85%,rgba(14,165,233,.13) 0,transparent 28%),
      linear-gradient(180deg,rgba(255,255,255,.035),transparent 42%);
    }

    .ox-wrap{width:100%;max-width:464px;padding:1.2rem}
    .ox-card,.ox-float-card,.ox-glass-card,.ox-holo-inner{
      width:100%;border:1px solid var(--ox-border);border-radius:var(--ox-r);
      background:var(--ox-surface);box-shadow:var(--ox-shadow);padding:2rem;
    }
    .ox-card,.ox-float-card{backdrop-filter:saturate(130%);-webkit-backdrop-filter:saturate(130%)}
    .ox-card-header{margin-bottom:1.65rem!important}
    .ox-card-header .fw-bold{letter-spacing:-.035em}
    .ox-card-image img{box-shadow:0 16px 42px rgba(16,24,40,.10)}

    h1,h2,h3,h4,h5,h6{color:var(--ox-text);letter-spacing:-.025em}
    .text-secondary{color:var(--ox-muted)!important}
    a{color:var(--ox);text-decoration:none;text-underline-offset:3px}
    a:hover{color:var(--ox-dk);text-decoration:underline}

    .btn-ox{
      min-height:44px;background:var(--ox-gradient,var(--ox));color:var(--ox-btn-fg,#fff);border:0;
      border-radius:var(--ox-btn-radius);font-weight:700;font-family:var(--ox-font);
      box-shadow:0 10px 24px var(--ox-sf);transition:transform var(--ox-transition),box-shadow var(--ox-transition),filter var(--ox-transition);
    }
    .btn-ox:hover,.btn-ox:focus{color:var(--ox-btn-fg,#fff);filter:saturate(1.06) brightness(.98);box-shadow:0 14px 34px var(--ox-sf);transform:translateY(-1px)}
    .btn-ox:disabled{opacity:.58;pointer-events:none;box-shadow:none;transform:none}
    .btn-ox-out{
      min-height:42px;background:color-mix(in srgb,var(--ox-surface) 92%,var(--ox) 8%);
      color:var(--ox);border:1px solid color-mix(in srgb,var(--ox) 38%,var(--ox-border));
      border-radius:var(--ox-btn-radius);font-weight:650;font-family:var(--ox-font);
      transition:background var(--ox-transition),border-color var(--ox-transition),transform var(--ox-transition),color var(--ox-transition);
    }
    .btn-ox-out:hover{background:var(--ox);border-color:var(--ox);color:var(--ox-btn-fg,#fff);transform:translateY(-1px);text-decoration:none}

    .form-control,.form-select{
      min-height:46px;border-radius:14px;border:1px solid var(--ox-border);
      background:color-mix(in srgb,var(--ox-surface-strong) 86%,transparent);
      color:var(--ox-text);font-size:.95rem;transition:border-color var(--ox-transition),box-shadow var(--ox-transition),background var(--ox-transition);
    }
    .form-control::placeholder{color:var(--ox-muted);opacity:.72}
    .form-control:focus,.form-select:focus{
      border-color:var(--ox)!important;box-shadow:0 0 0 .24rem var(--ox-focus)!important;
      background:var(--ox-surface-strong);color:var(--ox-text);
    }
    .form-floating>label{color:var(--ox-muted);background:transparent}
    .form-floating>.form-control:focus~label,
    .form-floating>.form-control:not(:placeholder-shown)~label{opacity:1;color:var(--ox)}
    .form-check-input{border-color:var(--ox-border)}
    .form-check-input:checked{background-color:var(--ox);border-color:var(--ox)}
    .form-check-input:focus{box-shadow:0 0 0 .22rem var(--ox-focus);border-color:var(--ox)}
    .form-check-label{color:var(--ox-muted)}
    .alert{border-radius:16px;border:1px solid transparent}
    .ox-alert-ok{background:rgba(22,163,74,.10);color:var(--ox-success);border-color:rgba(22,163,74,.16)!important}
    .ox-alert-err{background:rgba(220,38,38,.10);color:var(--ox-danger);border-color:rgba(220,38,38,.16)!important}
    [data-bs-theme=dark] .ox-alert-ok{color:#86efac}
    [data-bs-theme=dark] .ox-alert-err{color:#fca5a5}
    .ox-div{display:flex;align-items:center;gap:.75rem;color:var(--ox-muted);font-size:.78rem;margin:1.1rem 0}
    .ox-div::before,.ox-div::after{content:"";flex:1;border-top:1px solid var(--ox-border)}

    /* Split layout: calmer product-style landing panel. */
    body.ox-layout-split{align-items:stretch;justify-content:stretch;padding:0}
    .ox-split-root{display:grid;grid-template-columns:minmax(360px,44%) 1fr;min-height:100vh;width:100%}
    .ox-split-brand{
      position:relative;overflow:hidden;display:flex;flex-direction:column;justify-content:center;
      padding:clamp(2rem,6vw,5rem);color:{{ $oxSplitText ? $oxSplitText : 'var(--ox-btn-fg,#fff)' }};
      background:{{ $oxSplitBg ? $oxSplitBg : 'var(--ox-gradient)' }};background-size:cover;background-position:center;
    }
    .ox-split-brand::before,.ox-split-brand::after{content:"";position:absolute;border-radius:999px;pointer-events:none}
    .ox-split-brand::before{width:360px;height:360px;right:-130px;top:-130px;background:rgba(255,255,255,.16);filter:blur(2px)}
    .ox-split-brand::after{width:260px;height:260px;left:-120px;bottom:-110px;background:rgba(255,255,255,.10)}
    .ox-split-brand>*{position:relative;z-index:1}
    .ox-split-brand .ox-split-proof{margin-top:2rem;display:grid;gap:.85rem;max-width:420px}
    .ox-split-proof-item{
      display:flex;align-items:center;gap:.75rem;padding:.9rem 1rem;border-radius:18px;
      background:rgba(255,255,255,.13);border:1px solid rgba(255,255,255,.18);
      backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);font-size:.88rem;
    }
    .ox-split-proof-item i{font-size:1.05rem}
    .ox-split-form{min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:2rem;background:var(--ox-body-bg);overflow-y:auto}
    .ox-split-form .ox-wrap{max-width:464px;padding:0}
    @media(max-width:900px){
      .ox-split-root{grid-template-columns:1fr}
      .ox-split-brand{min-height:260px;padding:2rem;text-align:left}
      .ox-split-form{min-height:auto;padding:1.25rem}
    }

    /* Bare layout: focused, minimal, premium. */
    body.ox-layout-bare{background:#05070f}
    .ox-bare-bg{position:fixed;inset:0;z-index:-1;pointer-events:none;background:
      radial-gradient(circle at 50% -10%,rgba(255,255,255,.12),transparent 34%),
      radial-gradient(circle at 10% 80%,var(--ox-sf),transparent 34%),
      linear-gradient(180deg,#05070f,#080b16 58%,#05070f)}
    .ox-bare-root,.ox-glass-root,.ox-float-root{position:relative;width:100%;display:flex;flex-direction:column;align-items:center;padding:2rem 1rem}
    .ox-holo-outer{width:100%;max-width:464px;padding:1px;border-radius:calc(var(--ox-r) + 1px);background:linear-gradient(135deg,rgba(255,255,255,.22),rgba(255,255,255,.05),var(--ox-sf))}
    .ox-holo-inner{background:rgba(11,16,32,.88);backdrop-filter:blur(24px);-webkit-backdrop-filter:blur(24px);box-shadow:0 28px 90px rgba(0,0,0,.52)}

    /* Glass layout: soft translucent card with restrained movement. */
    body.ox-layout-glass{background:#0b1020}
    .ox-glass-bg{position:fixed;inset:0;z-index:-1;pointer-events:none;background:
      radial-gradient(circle at 18% 20%,rgba(167,139,250,.36),transparent 34%),
      radial-gradient(circle at 78% 72%,rgba(14,165,233,.26),transparent 32%),
      radial-gradient(circle at 48% 8%,rgba(45,212,191,.18),transparent 30%);animation:ox-drift 16s ease-in-out infinite alternate}
    @keyframes ox-drift{to{transform:scale(1.06) translate3d(1.5%,-1%,0)}}
    .ox-glass-card{max-width:464px;background:rgba(255,255,255,.10);backdrop-filter:blur(30px) saturate(145%);-webkit-backdrop-filter:blur(30px) saturate(145%);border-color:rgba(255,255,255,.18)}

    /* Float layout: elevated but quiet. */
    .ox-float-root{min-height:100vh;justify-content:center}
    .ox-float-brand-above{text-align:center;margin-bottom:1.35rem;width:100%;max-width:464px}
    .ox-float-card{max-width:464px;transition:transform .28s ease,box-shadow .28s ease}
    .ox-float-card:hover{transform:translateY(-3px);box-shadow:0 32px 90px rgba(16,24,40,.14),0 7px 24px rgba(16,24,40,.08)}
    [data-bs-theme=dark] .ox-float-card:hover{box-shadow:0 32px 90px rgba(0,0,0,.46),0 7px 24px rgba(0,0,0,.24)}

    @media(max-width:575.98px){
      body{justify-content:flex-start}
      .ox-wrap{padding:1rem;max-width:100%}
      .ox-card,.ox-float-card,.ox-glass-card,.ox-holo-inner{padding:1.35rem;border-radius:18px}
      .ox-bare-root,.ox-glass-root,.ox-float-root{padding:1rem}
    }
    @media(prefers-reduced-motion:reduce){
      *,*::before,*::after{animation:none!important;transition:none!important;scroll-behavior:auto!important}
      .btn-ox:hover,.btn-ox-out:hover,.ox-float-card:hover{transform:none}
    }
    </style>
    @if($oxDerived)
    <style>
    [data-ox-theme="{{ $oxTheme }}"]{
      --ox:{{ $oxDerived['ox'] }};
      --ox-dk:{{ $oxDerived['ox-dk'] }};
      --ox-sf:{{ $oxDerived['ox-sf'] }};
      --ox-focus:{{ $oxDerived['ring'] }};
      --ox-btn-fg:{{ $oxDerived['btn-fg'] }};
      --ox-gradient:linear-gradient(135deg,{{ $oxDerived['ox'] }} 0%,{{ $oxDerived['ox-dk'] }} 100%);
    }
    </style>
    @endif
    @stack('styles')
</head>
<body class="ox-layout-{{ $oxLayout }}">

@if($oxLayout === 'split')
<div class="ox-split-root">
    <aside class="ox-split-brand">
        @include('oxalis::partials.split-brand')
        <div class="ox-split-proof" aria-label="Authentication highlights">
            <div class="ox-split-proof-item"><i class="bi bi-fingerprint"></i><span>Passkeys, OTP, TOTP, and password flows in one secure layer.</span></div>
            <div class="ox-split-proof-item"><i class="bi bi-shield-check"></i><span>Rate-limited, audited, and designed for Laravel teams.</span></div>
        </div>
    </aside>
    <main class="ox-split-form">
        @if(session('status'))
        <div class="alert ox-alert-ok mb-3" style="max-width:464px;width:100%"><i class="bi bi-check-circle me-2"></i>{{ session('status') }}</div>
        @endif
        @if($errors->any())
        <div class="alert ox-alert-err mb-3" style="max-width:464px;width:100%">
        @foreach($errors->all() as $e)<div><i class="bi bi-exclamation-circle me-1"></i>{{ $e }}</div>@endforeach
        </div>
        @endif
        @stack('oxalis:before-card')
        <div class="ox-wrap">
            <section class="ox-card">
                @stack('oxalis:card-top')
                @include('oxalis::partials.card-image', ['position' => 'top'])
                @yield('content')
                @include('oxalis::partials.card-image', ['position' => 'bottom'])
                @stack('oxalis:card-bottom')
            </section>
        </div>
        @stack('oxalis:after-card')
    </main>
</div>

@elseif($oxLayout === 'bare')
<div class="ox-bare-bg" aria-hidden="true"></div>
<main class="ox-bare-root">
    @if(session('status'))
    <div class="alert ox-alert-ok mb-3" style="max-width:464px;width:100%"><i class="bi bi-check-circle me-2"></i>{{ session('status') }}</div>
    @endif
    @if($errors->any())
    <div class="alert ox-alert-err mb-3" style="max-width:464px;width:100%">
    @foreach($errors->all() as $e)<div><i class="bi bi-exclamation-circle me-1"></i>{{ $e }}</div>@endforeach
    </div>
    @endif
    @stack('oxalis:before-card')
    <div class="ox-holo-outer">
        <section class="ox-holo-inner">
            @stack('oxalis:card-top')
            @include('oxalis::partials.card-header')
            @include('oxalis::partials.card-image', ['position' => 'top'])
            @yield('content')
            @include('oxalis::partials.card-image', ['position' => 'bottom'])
            @stack('oxalis:card-bottom')
        </section>
    </div>
    @stack('oxalis:after-card')
</main>

@elseif($oxLayout === 'glass')
<div class="ox-glass-bg" aria-hidden="true"></div>
<main class="ox-glass-root">
    @if(session('status'))
    <div class="alert ox-alert-ok mb-3" style="max-width:464px;width:100%"><i class="bi bi-check-circle me-2"></i>{{ session('status') }}</div>
    @endif
    @if($errors->any())
    <div class="alert ox-alert-err mb-3" style="max-width:464px;width:100%">
    @foreach($errors->all() as $e)<div><i class="bi bi-exclamation-circle me-1"></i>{{ $e }}</div>@endforeach
    </div>
    @endif
    @stack('oxalis:before-card')
    <section class="ox-glass-card">
        @stack('oxalis:card-top')
        @include('oxalis::partials.card-header')
        @include('oxalis::partials.card-image', ['position' => 'top'])
        @yield('content')
        @include('oxalis::partials.card-image', ['position' => 'bottom'])
        @stack('oxalis:card-bottom')
    </section>
    @stack('oxalis:after-card')
</main>

@elseif($oxLayout === 'float')
<main class="ox-float-root">
    <div class="ox-float-brand-above">
        @include('oxalis::partials.card-header')
    </div>
    @if(session('status'))
    <div class="alert ox-alert-ok mb-3" style="max-width:464px;width:100%"><i class="bi bi-check-circle me-2"></i>{{ session('status') }}</div>
    @endif
    @if($errors->any())
    <div class="alert ox-alert-err mb-3" style="max-width:464px;width:100%">
    @foreach($errors->all() as $e)<div><i class="bi bi-exclamation-circle me-1"></i>{{ $e }}</div>@endforeach
    </div>
    @endif
    @stack('oxalis:before-card')
    <section class="ox-float-card">
        @stack('oxalis:card-top')
        @include('oxalis::partials.card-image', ['position' => 'top'])
        @yield('content')
        @include('oxalis::partials.card-image', ['position' => 'bottom'])
        @stack('oxalis:card-bottom')
    </section>
    @stack('oxalis:after-card')
</main>

@else
<main class="ox-wrap">
    @if(session('status'))
    <div class="alert ox-alert-ok mb-3"><i class="bi bi-check-circle me-2"></i>{{ session('status') }}</div>
    @endif
    @if($errors->any())
    <div class="alert ox-alert-err mb-3">
    @foreach($errors->all() as $e)<div><i class="bi bi-exclamation-circle me-1"></i>{{ $e }}</div>@endforeach
    </div>
    @endif
    @stack('oxalis:before-card')
    <section class="ox-card">
        @stack('oxalis:card-top')
        @include('oxalis::partials.card-header')
        @include('oxalis::partials.card-image', ['position' => 'top'])
        @yield('content')
        @include('oxalis::partials.card-image', ['position' => 'bottom'])
        @stack('oxalis:card-bottom')
    </section>
    @stack('oxalis:after-card')
</main>
@endif

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
@stack('scripts')
</body>
</html>
