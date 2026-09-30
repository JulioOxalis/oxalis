@props(['title', 'subtitle' => null])
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} - {{ config('app.name') }}</title>
    @if(file_exists(public_path('vendor/oxalis/auth.css')))
        <link rel="stylesheet" href="{{ asset('vendor/oxalis/auth.css') }}">
    @endif
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body class="auth-page">
<main class="auth-shell">
    <section class="auth-panel" aria-labelledby="auth-title">
        <a class="auth-brand" href="{{ url('/') }}">{{ config('oxalis.brand.name') }}</a>
        <div class="auth-heading">
            <h1 id="auth-title">{{ $title }}</h1>
            @if($subtitle)
                <p>{{ $subtitle }}</p>
            @endif
        </div>

        @if(session('status'))
            <div class="alert alert-success" role="status">{{ session('status') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{ $slot }}
    </section>
</main>
</body>
</html>
