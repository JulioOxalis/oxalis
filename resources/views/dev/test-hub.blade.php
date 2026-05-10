<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Oxalis — Auth Test Hub</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: #0d1117; color: #e6edf3; }
        .card { background: #161b22; border: 1px solid #30363d; }
        .card-header { background: #21262d; border-bottom: 1px solid #30363d; font-weight: 600; }
        .badge-method { font-size: .7rem; letter-spacing: .05em; }
        .env-box { background: #0d1117; border: 1px solid #30363d; border-radius: 6px; padding: 12px; font-family: monospace; font-size: .8rem; }
        .check { color: #3fb950; } .warn { color: #d29922; } .fail { color: #f85149; }
        h1 small { font-size: .45em; color: #8b949e; }
    </style>
</head>
<body>
<div class="container py-4" style="max-width:960px">

    {{-- ── Header ── --}}
    <div class="d-flex align-items-baseline gap-3 mb-4">
        <h1 class="fw-bold mb-0 fs-3">
            <span style="color:#388bfd">by</span> <span class="text-white text-decoration-underline">julio</span>
            <small>/ oxalis  ·  dev test hub</small>
        </h1>
        <span class="badge bg-warning text-dark badge-method">LOCAL ONLY</span>
    </div>

    @if(!app()->isLocal())
        <div class="alert alert-danger">This page is only accessible in local development (<code>APP_ENV=local</code>).</div>
    @endif

    <div class="row g-3">

        {{-- ── Config health ── --}}
        <div class="col-md-5">
            <div class="card h-100">
                <div class="card-header"><i class="bi bi-gear me-2"></i>Config health</div>
                <div class="card-body">
                    <div class="env-box mb-3">
                        <div><span class="check">✓</span> OXALIS_RP_ID = <strong>{{ $rpId }}</strong></div>
                        <div><span class="check">✓</span> OXALIS_ORIGINS = <strong>{{ $origin }}</strong></div>
                        <div><span class="{{ request()->getSchemeAndHttpHost() === $origin ? 'check' : 'fail' }}">
                            {{ request()->getSchemeAndHttpHost() === $origin ? '✓' : '✗' }}
                        </span> Browser origin = <strong>{{ request()->getSchemeAndHttpHost() }}</strong>
                        @if(request()->getSchemeAndHttpHost() !== $origin)
                            <br><span class="fail">⚠ MISMATCH — passkeys will fail! Set OXALIS_ORIGINS={{ request()->getSchemeAndHttpHost() }}</span>
                        @endif
                        </div>
                        <div><span class="{{ parse_url($origin, PHP_URL_HOST) === $rpId ? 'check' : 'fail' }}">
                            {{ parse_url($origin, PHP_URL_HOST) === $rpId ? '✓' : '✗' }}
                        </span> RP_ID matches origin host</div>
                    </div>

                    @php $methods = config('oxalis.methods', []); @endphp
                    <p class="text-muted small mb-2">Enabled methods:</p>
                    @foreach($methods as $key => $enabled)
                    <span class="badge me-1 mb-1 {{ $enabled ? 'bg-success' : 'bg-secondary' }} badge-method">
                        {{ strtoupper(str_replace('_', ' ', $key)) }}
                    </span>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ── Session state ── --}}
        <div class="col-md-7">
            <div class="card h-100">
                <div class="card-header"><i class="bi bi-person-check me-2"></i>Current session</div>
                <div class="card-body">
                    @if($user)
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="rounded-circle bg-success d-flex align-items-center justify-content-center" style="width:40px;height:40px;font-size:1.2rem">
                                {{ strtoupper(substr($user->name ?? $user->email, 0, 1)) }}
                            </div>
                            <div>
                                <div class="fw-bold">{{ $user->name ?? '—' }}</div>
                                <div class="text-muted small">{{ $user->email }}</div>
                            </div>
                            <form action="{{ route('oxalis.logout') }}" method="POST" class="ms-auto">
                                @csrf
                                <button class="btn btn-sm btn-outline-danger">Logout</button>
                            </form>
                        </div>

                        <div class="d-flex gap-2 flex-wrap mb-3">
                            <span class="badge {{ $stats['passkeys'] > 0 ? 'bg-success' : 'bg-secondary' }}">
                                <i class="bi bi-fingerprint me-1"></i>{{ $stats['passkeys'] }} passkey{{ $stats['passkeys'] != 1 ? 's' : '' }}
                            </span>
                            <span class="badge {{ $stats['totp_enabled'] ? 'bg-success' : 'bg-secondary' }}">
                                <i class="bi bi-phone me-1"></i>TOTP {{ $stats['totp_enabled'] ? 'on' : 'off' }}
                            </span>
                        </div>

                        @if($stats['recent_logins']->count())
                        <p class="text-muted small mb-1">Recent logins:</p>
                        <table class="table table-sm table-dark table-borderless mb-0" style="font-size:.78rem">
                            @foreach($stats['recent_logins'] as $e)
                            <tr>
                                <td><span class="badge bg-dark border border-secondary">{{ $e->method }}</span></td>
                                <td class="text-muted">{{ $e->ip_address }}</td>
                                <td class="text-muted text-end">{{ $e->created_at->diffForHumans() }}</td>
                            </tr>
                            @endforeach
                        </table>
                        @endif
                    @else
                        <p class="text-muted">Not logged in.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- ── Auth method tests ── --}}
        <div class="col-12">
            <div class="card">
                <div class="card-header"><i class="bi bi-shield-lock me-2"></i>Test every auth method</div>
                <div class="card-body">
                    <div class="row g-3">

                        <div class="col-sm-6 col-lg-4">
                            <div class="p-3 rounded" style="background:#0d1117;border:1px solid #30363d">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="bi bi-fingerprint fs-4 text-primary"></i>
                                    <strong>Passkey</strong>
                                    <span class="badge {{ ($methods['passkey'] ?? true) ? 'bg-success' : 'bg-secondary' }} badge-method ms-auto">{{ ($methods['passkey'] ?? true) ? 'ON' : 'OFF' }}</span>
                                </div>
                                <p class="text-muted small mb-2">Biometric / hardware key login. Requires browser + device support.</p>
                                <a href="{{ route('oxalis.login') }}" class="btn btn-sm btn-outline-primary w-100 mb-1">Test login</a>
                                @auth
                                <a href="{{ route('oxalis.passkeys.enroll') }}" class="btn btn-sm btn-outline-secondary w-100">Enroll passkey</a>
                                @endauth
                            </div>
                        </div>

                        <div class="col-sm-6 col-lg-4">
                            <div class="p-3 rounded" style="background:#0d1117;border:1px solid #30363d">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="bi bi-link-45deg fs-4 text-info"></i>
                                    <strong>Magic Link</strong>
                                    <span class="badge {{ ($methods['magic_link'] ?? true) ? 'bg-success' : 'bg-secondary' }} badge-method ms-auto">{{ ($methods['magic_link'] ?? true) ? 'ON' : 'OFF' }}</span>
                                </div>
                                <p class="text-muted small mb-2">Passwordless — click a link emailed to you. Dev link shown on screen.</p>
                                <form action="{{ route('oxalis.magic-link.send') }}" method="POST">
                                    @csrf
                                    <div class="input-group input-group-sm">
                                        <input type="email" name="email" class="form-control bg-dark text-white border-secondary" placeholder="email" value="{{ $user?->email ?? '' }}">
                                        <button class="btn btn-outline-info">Send</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="col-sm-6 col-lg-4">
                            <div class="p-3 rounded" style="background:#0d1117;border:1px solid #30363d">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="bi bi-envelope fs-4 text-warning"></i>
                                    <strong>Email OTP</strong>
                                    <span class="badge {{ ($methods['email_otp'] ?? true) ? 'bg-success' : 'bg-secondary' }} badge-method ms-auto">{{ ($methods['email_otp'] ?? true) ? 'ON' : 'OFF' }}</span>
                                </div>
                                <p class="text-muted small mb-2">6-digit code emailed. Dev mode shows code on the OTP form.</p>
                                <form action="{{ route('oxalis.otp.send') }}" method="POST">
                                    @csrf
                                    <div class="input-group input-group-sm">
                                        <input type="email" name="email" class="form-control bg-dark text-white border-secondary" placeholder="email" value="{{ $user?->email ?? '' }}">
                                        <button class="btn btn-outline-warning">Send</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="col-sm-6 col-lg-4">
                            <div class="p-3 rounded" style="background:#0d1117;border:1px solid #30363d">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="bi bi-phone fs-4 text-success"></i>
                                    <strong>TOTP</strong>
                                    <span class="badge {{ ($methods['totp'] ?? true) ? 'bg-success' : 'bg-secondary' }} badge-method ms-auto">{{ ($methods['totp'] ?? true) ? 'ON' : 'OFF' }}</span>
                                </div>
                                <p class="text-muted small mb-2">Google Auth / Authy app. Must set up in account settings first.</p>
                                @auth
                                <a href="{{ route('oxalis.totp.setup') }}" class="btn btn-sm btn-outline-success w-100 mb-1">Set up TOTP</a>
                                <a href="{{ route('oxalis.totp.manage') }}" class="btn btn-sm btn-outline-secondary w-100">Manage</a>
                                @else
                                <a href="{{ route('oxalis.login') }}" class="btn btn-sm btn-outline-secondary w-100">Login first</a>
                                @endauth
                            </div>
                        </div>

                        <div class="col-sm-6 col-lg-4">
                            <div class="p-3 rounded" style="background:#0d1117;border:1px solid #30363d">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="bi bi-lock fs-4 text-secondary"></i>
                                    <strong>Password</strong>
                                    <span class="badge {{ ($methods['password'] ?? true) ? 'bg-success' : 'bg-secondary' }} badge-method ms-auto">{{ ($methods['password'] ?? true) ? 'ON' : 'OFF' }}</span>
                                </div>
                                <p class="text-muted small mb-2">Classic email + password. Rate-limited to 5 attempts then 15-min lockout.</p>
                                <a href="{{ route('oxalis.password.login.show') }}" class="btn btn-sm btn-outline-secondary w-100">Test login</a>
                            </div>
                        </div>

                        <div class="col-sm-6 col-lg-4">
                            <div class="p-3 rounded" style="background:#0d1117;border:1px solid #30363d">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="bi bi-shield-shaded fs-4 text-danger"></i>
                                    <strong>Step-up Auth</strong>
                                </div>
                                <p class="text-muted small mb-2">Re-verification before sensitive actions. 15-min session window.</p>
                                @auth
                                <a href="{{ route('oxalis.step-up.prompt') }}" class="btn btn-sm btn-outline-danger w-100 mb-1">Trigger step-up</a>
                                <a href="{{ route('oxalis.account.security') }}" class="btn btn-sm btn-outline-secondary w-100">Security page</a>
                                @else
                                <a href="{{ route('oxalis.login') }}" class="btn btn-sm btn-outline-secondary w-100">Login first</a>
                                @endauth
                            </div>
                        </div>

                        <div class="col-sm-6 col-lg-4">
                            <div class="p-3 rounded" style="background:#0d1117;border:1px solid #30363d">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="bi bi-stars fs-4" style="color:#f0c040"></i>
                                    <strong>Smart Dispatch</strong>
                                    <span class="badge {{ config('oxalis.smart_dispatch') ? 'bg-success' : 'bg-secondary' }} badge-method ms-auto">{{ config('oxalis.smart_dispatch') ? 'ON' : 'OFF' }}</span>
                                </div>
                                <p class="text-muted small mb-2">One field — auto-picks best method per user. Enable with <code>OXALIS_SMART_DISPATCH=true</code>.</p>
                                <a href="{{ route('oxalis.dispatch') }}" class="btn btn-sm btn-outline-warning w-100">Try it</a>
                            </div>
                        </div>

                        <div class="col-sm-6 col-lg-4">
                            <div class="p-3 rounded" style="background:#0d1117;border:1px solid #30363d">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="bi bi-envelope-check fs-4 text-info"></i>
                                    <strong>Email Verification</strong>
                                </div>
                                <p class="text-muted small mb-2">Sent after registration. Dev link shown on verification page.</p>
                                <a href="{{ route('oxalis.email.notice') }}" class="btn btn-sm btn-outline-info w-100">Notice page</a>
                            </div>
                        </div>

                        <div class="col-sm-6 col-lg-4">
                            <div class="p-3 rounded" style="background:#0d1117;border:1px solid #30363d">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="bi bi-terminal fs-4 text-muted"></i>
                                    <strong>Auth Log</strong>
                                </div>
                                <p class="text-muted small mb-2">View all auth events in terminal.</p>
                                <div class="env-box">php artisan oxalis:log</div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>
    <p class="text-center text-muted small mt-4">
        This page is only visible when <code>APP_ENV=local</code>. It is not registered in production.
    </p>
</div>
</body>
</html>
