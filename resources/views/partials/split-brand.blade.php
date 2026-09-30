{{--
  Split-layout brand panel (left side).
  Publish to customise: php artisan vendor:publish --tag=oxalis-partials
  Output: resources/views/vendor/oxalis/partials/split-brand.blade.php
--}}
@php
    $oxLogoUrl = \Oxalis\Support\Branding::logoUrl();
    $oxTagline = \Oxalis\Support\Branding::tagline();
@endphp
@if($oxLogoUrl)
<img src="{{ $oxLogoUrl }}"
     alt="{{ \Oxalis\Support\Branding::logoAlt() }}"
     style="max-height:{{ \Oxalis\Support\Branding::logoHeight(64) }}px;width:auto;max-width:100%;object-fit:contain;margin-bottom:1.5rem">
@endif
<div style="font-size:1.6rem;font-weight:700;letter-spacing:-.02em">{{ config('app.name') }}</div>
@if($oxTagline)
<div style="font-size:.9rem;opacity:.8;margin-top:.5rem">{{ $oxTagline }}</div>
@endif
