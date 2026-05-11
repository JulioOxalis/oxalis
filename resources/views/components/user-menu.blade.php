@auth
@php
    $user    = auth()->user();
    $initial = strtoupper(substr($user->name ?? $user->email ?? '?', 0, 1));
    $name    = $user->name ?? 'Account';
    $email   = $user->email ?? '';
@endphp
<div class="dropdown">
    <button class="btn d-flex align-items-center gap-2 rounded-pill px-2 py-2 pe-3"
            style="border:1.5px solid var(--bs-border-color,#dee2e6);background:transparent;color:var(--bs-body-color,#212529);transition:border-color .15s"
            id="ox-user-menu-btn"
            data-bs-toggle="dropdown"
            aria-expanded="false">
        <span style="width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,#5c6ac4,#4959b8);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.75rem;flex-shrink:0;letter-spacing:-.01em">{{ $initial }}</span>
        <span class="d-none d-sm-block fw-medium" style="font-size:.875rem;max-width:130px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $name }}</span>
        <i class="bi bi-chevron-down" style="font-size:.6rem;opacity:.55;margin-left:.1rem"></i>
    </button>
    <ul class="dropdown-menu dropdown-menu-end mt-1 py-1"
        style="min-width:220px;border-radius:14px;border:1px solid var(--bs-border-color,#dee2e6);box-shadow:0 8px 32px rgba(0,0,0,.12)"
        aria-labelledby="ox-user-menu-btn">

        {{-- Identity --}}
        <li class="px-3 py-2">
            <div class="d-flex align-items-center gap-2">
                <span style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#5c6ac4,#4959b8);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;flex-shrink:0">{{ $initial }}</span>
                <div style="min-width:0">
                    <div class="fw-semibold" style="font-size:.875rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $name }}</div>
                    <div class="text-secondary" style="font-size:.72rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $email }}</div>
                </div>
            </div>
        </li>

        <li><hr class="dropdown-divider my-1" style="border-color:var(--bs-border-color,#dee2e6)"></li>

        {{-- Account settings --}}
        <li>
            <a class="dropdown-item d-flex align-items-center gap-2 py-2 mx-1 rounded-2"
               href="{{ route('oxalis.account') }}"
               style="font-size:.875rem">
                <span style="width:26px;height:26px;border-radius:8px;background:rgba(92,106,196,.12);color:#5c6ac4;display:flex;align-items:center;justify-content:center;font-size:.8rem;flex-shrink:0">
                    <i class="bi bi-person-gear"></i>
                </span>
                Account settings
            </a>
        </li>

        {{-- Admin link — only visible when admin session is active --}}
        @oxalisAdmin
        <li><hr class="dropdown-divider my-1" style="border-color:var(--bs-border-color,#dee2e6)"></li>
        <li>
            <a class="dropdown-item d-flex align-items-center gap-2 py-2 mx-1 rounded-2"
               href="{{ route('oxalis.admin') }}"
               style="font-size:.875rem;color:#5c6ac4">
                <span style="width:26px;height:26px;border-radius:8px;background:rgba(92,106,196,.12);color:#5c6ac4;display:flex;align-items:center;justify-content:center;font-size:.8rem;flex-shrink:0">
                    <i class="bi bi-shield-fill-check"></i>
                </span>
                Admin panel
            </a>
        </li>
        @endOxalisAdmin

        <li><hr class="dropdown-divider my-1" style="border-color:var(--bs-border-color,#dee2e6)"></li>

        {{-- Sign out --}}
        <li class="px-1 pb-1">
            <form action="{{ route('oxalis.logout') }}" method="POST">
                @csrf
                <button type="submit"
                        class="dropdown-item d-flex align-items-center gap-2 py-2 rounded-2"
                        style="font-size:.875rem;color:#dc3545;width:100%">
                    <span style="width:26px;height:26px;border-radius:8px;background:rgba(220,53,69,.08);display:flex;align-items:center;justify-content:center;font-size:.8rem;flex-shrink:0">
                        <i class="bi bi-box-arrow-right"></i>
                    </span>
                    Sign out
                </button>
            </form>
        </li>

    </ul>
</div>
@endauth
