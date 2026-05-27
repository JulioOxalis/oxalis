@php
  $oxTheme = config('oxalis.theme', 'indigo');
  $oxColor  = config('oxalis.theme_color');
  $oxDark   = in_array($oxTheme, ['neon','aurora','obsidian','ember']);
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
          integrity="sha384-tViUnnbplR7Re/4Wy/4ULR7ACzGxbUJTXzC7SaCiXQbJhvSiXatDKPmMxMLX1RNb" crossorigin="anonymous">
    @if($oxTheme === 'custom' && file_exists(public_path('vendor/oxalis/theme.css')))
    <link rel="stylesheet" href="{{ asset('vendor/oxalis/theme.css') }}">
    @endif
    <style>
    /* Theme variables — mirrors oxalis.blade.php for consistency */
    [data-ox-theme=indigo]{
      --ox:{{ ($oxTheme==='indigo' && $oxColor) ? $oxColor : '#5c6ac4' }};
      --ox-dk:#4959b8;--ox-sf:rgba(92,106,196,.12);--ox-r:14px;
      --ox-btn-fg:#fff;--ox-btn-radius:50rem;
      --ox-font:system-ui,-apple-system,sans-serif;
    }
    [data-ox-theme=indigo][data-bs-theme=dark]{
      --bs-body-bg:#0d0f18;--bs-card-bg:#181b2a;--bs-border-color:#252840;
    }
    [data-ox-theme=neon]{
      --ox:#00f5ff;--ox-dk:#00c8d4;--ox-sf:rgba(0,245,255,.07);--ox-r:3px;
      --ox-btn-fg:#07090e;--ox-btn-radius:3px;
      --ox-font:'Courier New',Courier,monospace;
      --bs-body-bg:#07090e;--bs-card-bg:#0e1117;--bs-border-color:#00f5ff28;
      --bs-body-color:#c8f0f2;--bs-secondary-color:#4a8a8f;
    }
    [data-ox-theme=aurora]{
      --ox:#a78bfa;--ox-dk:#7c3aed;--ox-sf:rgba(167,139,250,.1);--ox-r:22px;
      --ox-btn-fg:#fff;--ox-btn-radius:50rem;
      --ox-font:system-ui,-apple-system,sans-serif;
      --bs-body-bg:#08001a;--bs-card-bg:rgba(255,255,255,.05);
      --bs-border-color:rgba(255,255,255,.09);
      --bs-body-color:#e4dbff;--bs-secondary-color:#8b7fc0;
    }
    [data-ox-theme=aurora] body{
      background:linear-gradient(135deg,#08001a 0%,#0d1b32 50%,#001a10 100%)!important;
      background-attachment:fixed!important;
    }
    [data-ox-theme=obsidian]{
      --ox:#fff;--ox-dk:#d4d4d4;--ox-sf:rgba(255,255,255,.05);--ox-r:0px;
      --ox-btn-fg:#000;--ox-btn-radius:0px;
      --ox-font:'Courier New',Courier,monospace;
      --bs-body-bg:#000;--bs-card-bg:#000;--bs-border-color:#2a2a2a;
      --bs-body-color:#fff;--bs-secondary-color:#777;
    }
    [data-ox-theme=ember]{
      --ox:#f59e0b;--ox-dk:#d97706;--ox-sf:rgba(245,158,11,.1);--ox-r:10px;
      --ox-btn-fg:#1a0e00;--ox-btn-radius:8px;
      --ox-font:system-ui,-apple-system,sans-serif;
      --bs-body-bg:#100a02;--bs-card-bg:#1c1206;--bs-border-color:#3a2810;
      --bs-body-color:#f5deb3;--bs-secondary-color:#8a6a3a;
    }
    [data-ox-theme=frost]{
      --ox:#38bdf8;--ox-dk:#0ea5e9;--ox-sf:rgba(56,189,248,.12);--ox-r:20px;
      --ox-btn-fg:#0c3c5c;--ox-btn-radius:50rem;
      --ox-font:system-ui,-apple-system,sans-serif;
      --bs-body-bg:#dbeafe;--bs-border-color:rgba(56,189,248,.25);
      --bs-body-color:#0c3c5c;--bs-secondary-color:#4d8aa8;
    }

    body{font-family:var(--ox-font,system-ui,-apple-system,sans-serif);min-height:100vh;background:var(--bs-body-bg)}
    .ox-card{border-radius:var(--ox-r);border:1px solid var(--bs-border-color);background:var(--bs-card-bg,#fff);padding:1.5rem}
    .ox-avatar{width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,var(--ox),var(--ox-dk));color:var(--ox-btn-fg,#fff);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:1.35rem;flex-shrink:0;letter-spacing:-.02em}
    .ox-method-card{border-radius:var(--ox-r);border:1.5px solid var(--bs-border-color);padding:1.4rem 1.5rem;background:var(--bs-card-bg,#fff);transition:border-color .2s,box-shadow .2s;height:100%}
    .ox-method-card:hover{border-color:var(--ox);box-shadow:0 2px 16px var(--ox-sf)}
    .ox-method-icon{width:42px;height:42px;border-radius:11px;background:var(--ox-sf);display:flex;align-items:center;justify-content:center;color:var(--ox);font-size:1.15rem;margin-bottom:1rem}
    .ox-section-label{font-size:.67rem;letter-spacing:.12em;text-transform:uppercase;font-weight:700;color:var(--bs-secondary-color);margin-bottom:.85rem}
    .ox-theme-btn{border:1.5px solid var(--bs-border-color);border-radius:50rem;padding:.3rem .85rem;font-size:.78rem;background:transparent;color:var(--bs-secondary-color);cursor:pointer;transition:all .15s;line-height:1.4}
    .ox-theme-btn.active,.ox-theme-btn:hover{border-color:var(--ox);color:var(--ox);background:var(--ox-sf)}
    .ox-passkey-row{display:flex;align-items:center;gap:.85rem;padding:.85rem 0;border-bottom:1px solid var(--bs-border-color)}
    .ox-passkey-row:last-child{border-bottom:none;padding-bottom:0}
    .ox-passkey-row:first-child{padding-top:0}
    .ox-status-pill{display:inline-flex;align-items:center;gap:.35rem;font-size:.75rem;padding:.2rem .7rem;border-radius:50rem}
    .btn-ox{background:var(--ox);color:var(--ox-btn-fg,#fff);border:none;border-radius:var(--ox-btn-radius,50rem);font-weight:500;transition:background .15s;font-size:.84rem;font-family:var(--ox-font)}
    .btn-ox:hover{background:var(--ox-dk);color:var(--ox-btn-fg,#fff)}
    .btn-ox-out{background:transparent;color:var(--ox);border:1.5px solid var(--ox);border-radius:var(--ox-btn-radius,50rem);font-weight:500;font-size:.84rem;font-family:var(--ox-font);transition:all .15s}
    .btn-ox-out:hover{background:var(--ox);color:var(--ox-btn-fg,#fff)}
    .form-control:focus{border-color:var(--ox)!important;box-shadow:0 0 0 .2rem var(--ox-sf)!important}
    a{color:var(--ox)}a:hover{color:var(--ox-dk)}
    </style>
</head>
<body>
<div class="container-lg py-4 py-md-5 px-4" style="max-width:860px">

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

    @yield('content')

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc4s9bIOgUxi8T/jzmBiMh9+WkUBrBaFIKmhUcK93BF+" crossorigin="anonymous"></script>
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
