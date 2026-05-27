@php
    $oxHasLogo    = (bool) config('oxalis.brand.logo_url');
    $oxShowName   = (bool) config('oxalis.brand.show_app_name', false);
    $oxTagline    = config('oxalis.brand.tagline');
@endphp
@if($oxHasLogo || $oxShowName || $oxTagline)
<div class="ox-card-header text-center mb-4">
    @include('oxalis::partials.logo')
    @if($oxShowName)
    <div class="fw-bold" style="font-size:1.15rem;color:var(--bs-body-color)">{{ config('app.name') }}</div>
    @endif
    @if($oxTagline)
    <div class="text-secondary mt-1" style="font-size:.82rem">{{ $oxTagline }}</div>
    @endif
</div>
@endif
