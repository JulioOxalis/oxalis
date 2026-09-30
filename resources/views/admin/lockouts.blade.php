@extends('oxalis::layouts.admin-panel')
@section('title','Lockouts')
@section('content')

<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
  <div class="d-flex gap-1">
    <a href="{{ route('oxalis.admin.lockouts', ['active'=>1]) }}"
       class="adm-filter-btn {{ $active ? 'active' : '' }}">
      <i class="bi bi-ban me-1"></i>Active only
    </a>
    <a href="{{ route('oxalis.admin.lockouts', ['active'=>0]) }}"
       class="adm-filter-btn {{ !$active ? 'active' : '' }}">
      All lockouts
    </a>
  </div>
  @if($active && $lockouts->total() > 0)
  <form method="POST" action="{{ route('oxalis.admin.lockouts.clear-all') }}"
    onsubmit="return confirm('Clear all active lockouts?')">
    @csrf
    <button type="submit" class="btn-adm" style="background:#ef4444">
      <i class="bi bi-slash-circle"></i> Clear all active
    </button>
  </form>
  @endif
</div>

<div class="adm-table-wrap mb-3">
  <div style="overflow-x:auto">
  <table class="adm-table">
    <thead><tr>
      <th style="padding-left:1.25rem">Key (IP / credential)</th>
      <th class="text-center">Attempts</th>
      <th class="d-none d-md-table-cell">Locked until</th>
      <th style="padding-right:1.25rem;text-align:right">Action</th>
    </tr></thead>
    <tbody>
      @forelse($lockouts as $l)
      <tr>
        <td style="padding-left:1.25rem;font-family:monospace;font-size:.72rem;color:var(--adm-muted);max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
          {{ $l->key }}
        </td>
        <td class="text-center">
          <span class="adm-badge {{ $l->attempts>=5?'adm-badge-red':($l->attempts>=3?'adm-badge-gray':'adm-badge-green') }}">
            {{ $l->attempts }}
          </span>
        </td>
        <td class="d-none d-md-table-cell" style="font-size:.75rem;color:var(--adm-muted)">
          @if($l->locked_until && $l->locked_until->isFuture())
          <span style="color:#f87171">{{ $l->locked_until->diffForHumans() }}</span>
          @else
          <span style="color:var(--adm-muted)">Expired</span>
          @endif
        </td>
        <td style="padding-right:1.25rem;text-align:right">
          @if($l->locked_until && $l->locked_until->isFuture())
          <form method="POST" action="{{ route('oxalis.admin.lockouts.clear') }}" style="display:inline">
            @csrf
            <input type="hidden" name="lockout_id" value="{{ $l->id }}">
            <button type="submit" class="adm-badge adm-badge-green" style="border:none;cursor:pointer">
              <i class="bi bi-unlock"></i> Unlock
            </button>
          </form>
          @else
          <span style="color:var(--adm-muted);font-size:.72rem">—</span>
          @endif
        </td>
      </tr>
      @empty
      <tr><td colspan="4" style="text-align:center;padding:2rem;color:var(--adm-muted)">
        {{ $active ? 'No active lockouts.' : 'No lockouts recorded.' }}
      </td></tr>
      @endforelse
    </tbody>
  </table>
  </div>
</div>

@if($lockouts->hasPages())
<div class="d-flex justify-content-end gap-1" style="font-size:.75rem">
  @if(!$lockouts->onFirstPage())
  <a href="{{ $lockouts->previousPageUrl() }}" class="adm-filter-btn">&lsaquo; Prev</a>
  @endif
  @if($lockouts->hasMorePages())
  <a href="{{ $lockouts->nextPageUrl() }}" class="adm-filter-btn">Next &rsaquo;</a>
  @endif
</div>
@endif
@endsection
