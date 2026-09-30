@php
    $oxCardImageUrl = \Oxalis\Support\Branding::cardImageUrl();
    $oxCardImagePosition = \Oxalis\Support\Branding::cardImagePosition();
    $oxRequestedPosition = $position ?? 'top';
@endphp
@if($oxCardImageUrl && $oxCardImagePosition === $oxRequestedPosition)
<div class="ox-card-image text-center {{ $oxCardImagePosition === 'top' ? 'mb-4' : 'mt-4' }}">
    <img src="{{ $oxCardImageUrl }}"
         alt="{{ \Oxalis\Support\Branding::cardImageAlt() }}"
         style="display:block;width:100%;max-height:{{ \Oxalis\Support\Branding::cardImageHeight() }}px;object-fit:cover;border-radius:calc(var(--ox-r,14px) * .7)">
</div>
@endif
