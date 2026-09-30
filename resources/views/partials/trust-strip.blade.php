@php
    $oxSecurityStrip = (bool) config('oxalis.brand.security_strip', false);
@endphp

@if($oxSecurityStrip)
<div class="ox-security-note" aria-label="Oxalis security note">
    <i class="bi bi-shield-lock"></i>
    <span>Protected by Oxalis security controls</span>
</div>
@endif
