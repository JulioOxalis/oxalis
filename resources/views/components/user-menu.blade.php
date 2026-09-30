@auth
@php
    $user    = auth()->user();
    $initial = strtoupper(substr($user->name ?? $user->email ?? '?', 0, 1));
    $name    = $user->name ?? 'Account';
    $email   = $user->email ?? '';
    $accountUrl = \Illuminate\Support\Facades\Route::has('oxalis.account')
        ? route('oxalis.account')
        : url(trim(config('oxalis.routes.prefix', 'oxalis'), '/').'/account');
    $logoutUrl = \Illuminate\Support\Facades\Route::has('oxalis.logout')
        ? route('oxalis.logout')
        : url(trim(config('oxalis.routes.prefix', 'oxalis'), '/').'/logout');
@endphp
<style>
  .ox-user-menu{position:relative;display:inline-block;font-family:inherit}
  .ox-user-menu>summary{list-style:none;cursor:pointer;border:1.5px solid var(--bs-border-color,#dee2e6);background:var(--bs-body-bg,#fff);color:var(--bs-body-color,#212529);border-radius:999px;padding:.45rem .75rem .45rem .45rem;display:flex;align-items:center;gap:.55rem;transition:border-color .15s,box-shadow .15s}
  .ox-user-menu>summary::-webkit-details-marker{display:none}
  .ox-user-menu[open]>summary,.ox-user-menu>summary:focus{border-color:#5c6ac4;box-shadow:0 0 0 .18rem rgba(92,106,196,.16);outline:0}
  .ox-user-menu__avatar{width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,#5c6ac4,#4959b8);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.75rem;flex-shrink:0;letter-spacing:-.01em}
  .ox-user-menu__name{font-weight:600;font-size:.875rem;max-width:130px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
  .ox-user-menu__chev{font-size:.75rem;opacity:.65;line-height:1}
  .ox-user-menu__panel{position:absolute;right:0;z-index:1050;min-width:230px;margin-top:.45rem;padding:.35rem;background:var(--bs-body-bg,#fff);color:var(--bs-body-color,#212529);border:1px solid var(--bs-border-color,#dee2e6);border-radius:14px;box-shadow:0 12px 34px rgba(15,23,42,.16)}
  .ox-user-menu__identity{display:flex;gap:.7rem;align-items:center;padding:.7rem .75rem}
  .ox-user-menu__identity .ox-user-menu__avatar{width:38px;height:38px;font-size:.85rem}
  .ox-user-menu__meta{min-width:0}
  .ox-user-menu__meta strong{display:block;font-size:.9rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
  .ox-user-menu__meta span{display:block;color:var(--bs-secondary-color,#6c757d);font-size:.73rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
  .ox-user-menu__divider{height:1px;background:var(--bs-border-color,#dee2e6);margin:.25rem .35rem}
  .ox-user-menu__item{width:100%;border:0;background:transparent;text-decoration:none;color:inherit;display:flex;align-items:center;gap:.55rem;padding:.62rem .7rem;border-radius:10px;font-size:.875rem;text-align:left}
  .ox-user-menu__item:hover{background:rgba(92,106,196,.08);color:#5c6ac4}
  .ox-user-menu__item--danger{color:#dc3545}
  .ox-user-menu__item--danger:hover{background:rgba(220,53,69,.08);color:#dc3545}
  .ox-user-menu__icon{width:26px;height:26px;border-radius:8px;background:rgba(92,106,196,.12);color:#5c6ac4;display:flex;align-items:center;justify-content:center;font-size:.8rem;flex-shrink:0}
  .ox-user-menu__item--danger .ox-user-menu__icon{background:rgba(220,53,69,.08);color:#dc3545}
  @media(max-width:575.98px){.ox-user-menu__name{display:none}.ox-user-menu__panel{right:auto;left:0}}
</style>
<details class="ox-user-menu">
    <summary aria-label="Open user menu">
        <span class="ox-user-menu__avatar">{{ $initial }}</span>
        <span class="ox-user-menu__name">{{ $name }}</span>
        <span class="ox-user-menu__chev">⌄</span>
    </summary>

    <div class="ox-user-menu__panel" role="menu">
        <div class="ox-user-menu__identity">
            <span class="ox-user-menu__avatar">{{ $initial }}</span>
            <span class="ox-user-menu__meta">
                <strong>{{ $name }}</strong>
                <span>{{ $email }}</span>
            </span>
        </div>

        <div class="ox-user-menu__divider"></div>

        <a class="ox-user-menu__item" href="{{ $accountUrl }}" role="menuitem">
            <span class="ox-user-menu__icon">⚙</span>
            Account settings
        </a>

        @if ($showAdminLink && session('oxalis_admin_authenticated') === true && \Illuminate\Support\Facades\Route::has('oxalis.admin'))
        <a class="ox-user-menu__item" href="{{ route('oxalis.admin') }}" role="menuitem">
            <span class="ox-user-menu__icon">✓</span>
            Admin panel
        </a>
        @endif

        <div class="ox-user-menu__divider"></div>

        <form action="{{ $logoutUrl }}" method="POST">
            @csrf
            <button type="submit" class="ox-user-menu__item ox-user-menu__item--danger" role="menuitem">
                <span class="ox-user-menu__icon">↪</span>
                Sign out
            </button>
        </form>
    </div>
</details>
@endauth
