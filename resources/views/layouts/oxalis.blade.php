<!DOCTYPE html>
<html lang="en" data-bs-theme="auto">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }}@hasSection('title') &middot; @yield('title')@endif</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root{--ox:#5c6ac4;--ox-dk:#4959b8;--ox-sf:rgba(92,106,196,.12);--ox-r:14px}
        body{min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;font-family:system-ui,-apple-system,sans-serif}
        [data-bs-theme=dark]{--bs-body-bg:#0d0f18;--bs-card-bg:#181b2a;--bs-border-color:#252840}
        .ox-wrap{width:100%;max-width:440px;padding:0 1rem}
        .ox-card{border-radius:var(--ox-r);border:1px solid var(--bs-border-color);background:var(--bs-card-bg,#fff);box-shadow:0 4px 24px rgba(0,0,0,.07);padding:2rem 2rem 1.75rem}
        [data-bs-theme=dark] .ox-card{box-shadow:0 4px 32px rgba(0,0,0,.45)}
        .btn-ox{background:var(--ox);color:#fff;border:none;border-radius:50rem;font-weight:500;transition:background .15s}
        .btn-ox:hover,.btn-ox:focus{background:var(--ox-dk);color:#fff}
        .btn-ox:disabled{opacity:.6;pointer-events:none}
        .btn-ox-out{background:transparent;color:var(--ox);border:2px solid var(--ox);border-radius:50rem;font-weight:500;transition:all .15s}
        .btn-ox-out:hover{background:var(--ox);color:#fff}
        .ox-div{display:flex;align-items:center;gap:.6rem;color:var(--bs-secondary-color);font-size:.78rem;margin:1rem 0}
        .ox-div::before,.ox-div::after{content:'';flex:1;border-top:1px solid var(--bs-border-color)}
        .ox-icon{width:60px;height:60px;border-radius:50%;background:var(--ox-sf);display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem;font-size:1.6rem;color:var(--ox)}
        .ox-brand{font-size:.68rem;letter-spacing:.07em;text-transform:uppercase;text-align:center;color:var(--bs-secondary-color);margin-top:.9rem}
        .form-control:focus{border-color:var(--ox)!important;box-shadow:0 0 0 .2rem var(--ox-sf)}
        a{color:var(--ox)}a:hover{color:var(--ox-dk)}
        .rounded-pill-start{border-radius:50rem 0 0 50rem!important}
        .rounded-pill-end{border-radius:0 50rem 50rem 0!important}
    </style>
</head>
<body>
<div class="ox-wrap">
<div class="text-center mb-4">
    <img src="{{ asset('vendor/oxalis/oxalis-logo.png') }}"
         alt="{{ config('app.name') }}"
         style="max-height:52px;max-width:220px;width:auto;object-fit:contain"
         onerror="this.style.display='none'">
</div>
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