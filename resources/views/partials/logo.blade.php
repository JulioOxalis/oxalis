@php
    $oxLogoUrl = \Oxalis\Support\Branding::logoUrl();
@endphp
@if($oxLogoUrl)
<div class="ox-brand-logo text-center mb-3">
    <img src="{{ $oxLogoUrl }}"
         alt="{{ \Oxalis\Support\Branding::logoAlt() }}"
         style="max-height:{{ \Oxalis\Support\Branding::logoHeight() }}px;width:auto;max-width:100%;object-fit:contain">
</div>
@endif
