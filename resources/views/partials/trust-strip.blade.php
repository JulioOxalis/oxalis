@php
    $oxMethods = config('oxalis.methods', []);
    $oxSecurityStrip = (bool) config('oxalis.brand.security_strip', true);
    $oxPasskeys = (bool) ($oxMethods['passkey'] ?? true);
    $oxTotp = ! config('oxalis.passkey_only', false) && (bool) ($oxMethods['totp'] ?? true);
@endphp

@if($oxSecurityStrip)
<div class="ox-trust-strip" aria-label="Oxalis security highlights">
    <span class="ox-trust-chip ox-trust-chip-strong">
        <i class="bi bi-shield-lock"></i>
        Secured by Oxalis
    </span>
    @if($oxPasskeys)
    <span class="ox-trust-chip">
        <i class="bi bi-fingerprint"></i>
        Passkey ready
    </span>
    @endif
    @if($oxTotp)
    <span class="ox-trust-chip">
        <i class="bi bi-phone"></i>
        2FA supported
    </span>
    @endif
    <span class="ox-trust-chip">
        <i class="bi bi-speedometer2"></i>
        Rate limited
    </span>
</div>
@endif
