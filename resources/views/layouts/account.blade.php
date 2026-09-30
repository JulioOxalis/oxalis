@php
  $oxTheme = config('oxalis.theme', 'indigo');
  $oxColor  = config('oxalis.theme_color');
  $oxDark   = in_array($oxTheme, ['neon','aurora','obsidian','ember']);

  // Derive all color tokens from OXALIS_PRIMARY_COLOR
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
<html lang="en" data-ox-theme="{{ $oxTheme }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} · @yield('title','Account')</title>
    <script>
    (function(){
      @if($oxDark)
      document.documentElement.setAttribute('data-bs-theme','dark');
      @else
      var p=localStorage.getItem('ox-theme')||'auto';
      var actual=p==='auto'?(window.matchMedia('(prefers-color-scheme:dark)').matches?'dark':'light'):p;
      document.documentElement.setAttribute('data-bs-theme',actual);
      @endif
    })();
    </script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
          integrity="sha384-XGjxtQfXaH2tnPFa9x+ruJTuLE3Aa6LhHSWRr1XeTyhezb4abCG4ccI5AkVDxqC+" crossorigin="anonymous">
    @if($oxTheme === 'custom' && file_exists(public_path('vendor/oxalis/theme.css')))
    <link rel="stylesheet" href="{{ asset('vendor/oxalis/theme.css') }}">
    @endif
    <style>
    :root{
      --ox:#4f46e5;--ox-dk:#3730a3;--ox-sf:rgba(79,70,229,.11);--ox-r:22px;
      --ox-btn-fg:#fff;--ox-btn-radius:14px;--ox-font:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif;
      --bs-body-bg:#f6f7fb;--bs-card-bg:#fff;--bs-border-color:#e4e7ec;--bs-body-color:#101828;--bs-secondary-color:#667085;
    }
    /* Professional theme variables — mirrors oxalis.blade.php for consistency */
    [data-ox-theme=indigo]{
      --ox:{{ ($oxTheme==='indigo' && $oxColor) ? $oxColor : '#4f46e5' }};--ox-dk:#3730a3;--ox-sf:rgba(79,70,229,.11);--ox-r:22px;
      --ox-btn-fg:#fff;--ox-btn-radius:14px;--ox-font:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif;
      --bs-body-bg:#f6f7fb;--bs-card-bg:#fff;--bs-border-color:#e4e7ec;--bs-body-color:#101828;--bs-secondary-color:#667085;
    }
    [data-ox-theme=indigo][data-bs-theme=dark]{
      --ox:#8b8cf6;--ox-dk:#c4b5fd;--ox-sf:rgba(139,140,246,.14);
      --bs-body-bg:#0b1020;--bs-card-bg:#111827;--bs-border-color:#253044;--bs-body-color:#f8fafc;--bs-secondary-color:#a7b0c3;
    }
    [data-ox-theme=neon]{
      --ox:#14b8a6;--ox-dk:#5eead4;--ox-sf:rgba(20,184,166,.13);--ox-r:18px;
      --ox-btn-fg:#07111f;--ox-btn-radius:13px;--ox-font:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif;
      --bs-body-bg:#07111f;--bs-card-bg:#0c1726;--bs-border-color:#1d3347;--bs-body-color:#eef6ff;--bs-secondary-color:#9eb3c7;
    }
    [data-ox-theme=aurora]{
      --ox:#a78bfa;--ox-dk:#c4b5fd;--ox-sf:rgba(167,139,250,.14);--ox-r:26px;
      --ox-btn-fg:#fff;--ox-btn-radius:15px;--ox-font:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif;
      --bs-body-bg:#0c1020;--bs-card-bg:rgba(18,24,43,.86);--bs-border-color:rgba(255,255,255,.12);--bs-body-color:#f5f3ff;--bs-secondary-color:#b9b3d6;
    }
    [data-ox-theme=aurora] body{
      background:radial-gradient(circle at 12% 15%,rgba(167,139,250,.18),transparent 32%),linear-gradient(135deg,#0c1020 0%,#10192f 100%)!important;
      background-attachment:fixed!important;
    }
    [data-ox-theme=obsidian]{
      --ox:#e5e7eb;--ox-dk:#fff;--ox-sf:rgba(255,255,255,.10);--ox-r:16px;
      --ox-btn-fg:#101828;--ox-btn-radius:12px;--ox-font:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif;
      --bs-body-bg:#070707;--bs-card-bg:#111;--bs-border-color:#2c2c2c;--bs-body-color:#f5f5f5;--bs-secondary-color:#a3a3a3;
    }
    [data-ox-theme=ember]{
      --ox:#f97316;--ox-dk:#fdba74;--ox-sf:rgba(249,115,22,.14);--ox-r:20px;
      --ox-btn-fg:#1f1308;--ox-btn-radius:14px;--ox-font:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif;
      --bs-body-bg:#160f0a;--bs-card-bg:#201610;--bs-border-color:#3a2719;--bs-body-color:#fff7ed;--bs-secondary-color:#d4a983;
    }
    [data-ox-theme=frost]{
      --ox:#0284c7;--ox-dk:#0369a1;--ox-sf:rgba(2,132,199,.12);--ox-r:24px;
      --ox-btn-fg:#fff;--ox-btn-radius:15px;--ox-font:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif;
      --bs-body-bg:#eef7ff;--bs-card-bg:rgba(255,255,255,.84);--bs-border-color:rgba(14,116,144,.18);--bs-body-color:#0f2a3d;--bs-secondary-color:#507085;
    }

    body{font-family:var(--ox-font,system-ui,-apple-system,sans-serif);min-height:100vh;background:var(--bs-body-bg);color:var(--bs-body-color);text-rendering:optimizeLegibility;-webkit-font-smoothing:antialiased}
    body::before{content:"";position:fixed;inset:0;z-index:-1;pointer-events:none;background:radial-gradient(circle at 12% 8%,var(--ox-sf),transparent 32%),radial-gradient(circle at 90% 88%,rgba(20,184,166,.10),transparent 30%)}
    .ox-card{border-radius:var(--ox-r);border:1px solid var(--bs-border-color);background:var(--bs-card-bg,#fff);padding:1.5rem;box-shadow:0 20px 60px rgba(16,24,40,.07)}
    [data-bs-theme=dark] .ox-card{box-shadow:0 20px 60px rgba(0,0,0,.28)}
    .ox-avatar{width:56px;height:56px;border-radius:18px;background:linear-gradient(135deg,var(--ox),var(--ox-dk));color:var(--ox-btn-fg,#fff);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:1.35rem;flex-shrink:0;letter-spacing:-.03em}
    .ox-method-card{border-radius:var(--ox-r);border:1px solid var(--bs-border-color);padding:1.4rem 1.5rem;background:var(--bs-card-bg,#fff);transition:border-color .2s,box-shadow .2s,transform .2s;height:100%}
    .ox-method-card:hover{border-color:var(--ox);box-shadow:0 14px 36px var(--ox-sf);transform:translateY(-2px)}
    .ox-method-icon{width:42px;height:42px;border-radius:14px;background:var(--ox-sf);display:flex;align-items:center;justify-content:center;color:var(--ox);font-size:1.15rem;margin-bottom:1rem}
    .ox-section-label{font-size:.67rem;letter-spacing:.12em;text-transform:uppercase;font-weight:800;color:var(--bs-secondary-color);margin-bottom:.85rem}
    .ox-theme-btn{border:1px solid var(--bs-border-color);border-radius:999px;padding:.3rem .85rem;font-size:.78rem;background:transparent;color:var(--bs-secondary-color);cursor:pointer;transition:all .15s;line-height:1.4}
    .ox-theme-btn.active,.ox-theme-btn:hover{border-color:var(--ox);color:var(--ox);background:var(--ox-sf)}
    .ox-passkey-row{display:flex;align-items:center;gap:.85rem;padding:.85rem 0;border-bottom:1px solid var(--bs-border-color)}
    .ox-passkey-row:last-child{border-bottom:none;padding-bottom:0}
    .ox-passkey-row:first-child{padding-top:0}
    .ox-status-pill{display:inline-flex;align-items:center;gap:.35rem;font-size:.75rem;padding:.2rem .7rem;border-radius:50rem}
    .btn-ox{background:linear-gradient(135deg,var(--ox),var(--ox-dk));color:var(--ox-btn-fg,#fff);border:none;border-radius:var(--ox-btn-radius,14px);font-weight:700;transition:filter .15s,transform .15s,box-shadow .15s;font-size:.84rem;font-family:var(--ox-font);box-shadow:0 10px 24px var(--ox-sf)}
    .btn-ox:hover{filter:saturate(1.05) brightness(.98);transform:translateY(-1px);color:var(--ox-btn-fg,#fff);box-shadow:0 14px 32px var(--ox-sf)}
    .btn-ox-out{background:transparent;color:var(--ox);border:1px solid var(--ox);border-radius:var(--ox-btn-radius,14px);font-weight:650;font-size:.84rem;font-family:var(--ox-font);transition:all .15s}
    .btn-ox-out:hover{background:var(--ox);color:var(--ox-btn-fg,#fff)}
    .form-control{border-radius:14px;border-color:var(--bs-border-color);background:var(--bs-card-bg);color:var(--bs-body-color)}
    .form-control:focus{border-color:var(--ox)!important;box-shadow:0 0 0 .22rem var(--ox-sf)!important}
    a{color:var(--ox)}a:hover{color:var(--ox-dk)}

    /* ─── UNIVERSAL: form readability across all themes ────────────────────── */
    .form-control::placeholder{color:var(--bs-secondary-color);opacity:.8}
    .form-floating>label{color:var(--bs-secondary-color);background:transparent}
    .form-floating>.form-control:focus~label,
    .form-floating>.form-control:not(:placeholder-shown)~label{opacity:1}
    .form-check-label{color:var(--bs-secondary-color)}
    .form-check-input:not(:checked){
      background-color:var(--bs-tertiary-bg,rgba(0,0,0,.06));
      border-color:var(--bs-border-color)
    }
    /* Dark-theme status pills and badges */
    [data-bs-theme=dark] .ox-status-pill{filter:brightness(1.2)}
    [data-bs-theme=dark] .badge{filter:none}
    [data-bs-theme=dark] .alert{color:inherit}
    /* Neon: table, badges */
    [data-ox-theme=neon] table{--bs-table-color:var(--bs-body-color)}
    [data-ox-theme=neon] .badge{background:rgba(20,184,166,.12)!important;color:#5eead4!important}
    /* Frost: secondary text contrast on light bg */
    [data-ox-theme=frost] .text-secondary{color:#3a7a9c!important}
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
<body>
<div class="container-lg py-4 py-md-5 px-4" style="max-width:860px">

    @stack('oxalis:before-card')

    @if(session('status'))
    <div class="alert border-0 rounded-3 mb-4" style="background:rgba(25,135,84,.1);color:#198754"><i class="bi bi-check-circle me-2"></i>{{ session('status') }}</div>
    @endif
    @if($errors->any())
    <div class="alert border-0 rounded-3 mb-4" style="background:rgba(220,53,69,.1);color:#dc3545">
    @foreach($errors->all() as $e)<div><i class="bi bi-exclamation-circle me-1"></i>{{ $e }}</div>@endforeach
    </div>
    @endif

    <div class="d-flex align-items-center justify-content-between mb-5">
        <a href="{{ config('oxalis.routes.home','/dashboard') }}" class="d-flex align-items-center gap-2 text-decoration-none text-secondary" style="font-size:.85rem">
            <i class="bi bi-arrow-left"></i><span>Back</span>
        </a>
        {{-- Theme toggle only for light-capable themes --}}
        @if(!$oxDark)
        <div class="d-flex align-items-center gap-1" id="ox-theme-toggle" role="group" aria-label="Theme">
            <button class="ox-theme-btn" data-theme="light" title="Light"><i class="bi bi-sun-fill"></i></button>
            <button class="ox-theme-btn" data-theme="auto"  title="Auto" ><i class="bi bi-circle-half"></i></button>
            <button class="ox-theme-btn" data-theme="dark"  title="Dark" ><i class="bi bi-moon-fill"></i></button>
        </div>
        @endif
    </div>

    @stack('oxalis:card-top')
    @yield('content')
    @stack('oxalis:card-bottom')

</div>
@stack('oxalis:after-card')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
@if(!$oxDark)
<script>
(function(){
    var cur=localStorage.getItem('ox-theme')||'auto';
    document.querySelectorAll('#ox-theme-toggle [data-theme]').forEach(function(b){
        if(b.dataset.theme===cur)b.classList.add('active');
        b.addEventListener('click',function(){
            var t=this.dataset.theme;
            localStorage.setItem('ox-theme',t);
            document.querySelectorAll('#ox-theme-toggle [data-theme]').forEach(function(x){x.classList.remove('active')});
            this.classList.add('active');
            var actual=t==='auto'?(window.matchMedia('(prefers-color-scheme:dark)').matches?'dark':'light'):t;
            document.documentElement.setAttribute('data-bs-theme',actual);
        });
    });
})();
</script>
@endif
@stack('scripts')
</body>
</html>
