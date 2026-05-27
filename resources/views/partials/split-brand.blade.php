{{--
  Split-layout brand panel (left side).
  Publish to customise: php artisan vendor:publish --tag=oxalis-partials
  Output: resources/views/vendor/oxalis/partials/split-brand.blade.php
--}}
@if(config('oxalis.brand.logo_url'))
<img src="{{ config('oxalis.brand.logo_url') }}"
     alt="{{ config('app.name') }}"
     style="max-height:64px;width:auto;object-fit:contain;margin-bottom:1.5rem">
@endif
<div style="font-size:1.6rem;font-weight:700;letter-spacing:-.02em">{{ config('app.name') }}</div>
@if(config('oxalis.brand.tagline'))
<div style="font-size:.9rem;opacity:.8;margin-top:.5rem">{{ config('oxalis.brand.tagline') }}</div>
@endif
