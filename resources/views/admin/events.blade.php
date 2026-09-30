@extends('oxalis::layouts.admin-panel')
@section('title','Auth events')
@section('content')

{{-- Filter bar --}}
<form method="GET" action="{{ route('oxalis.admin.events') }}" class="d-flex gap-2 flex-wrap mb-3 align-items-center">
  <select name="method" class="adm-search" style="width:auto">
    <option value="">All methods</option>
    @foreach($methods as $m)
    <option value="{{ $m }}" {{ $method===$m?'selected':'' }}>{{ $m }}</option>
    @endforeach
  </select>
  <select name="status" class="adm-search" style="width:auto">
    <option value="">All statuses</option>
    <option value="success" {{ $status==='success'?'selected':'' }}>Success</option>
    <option value="failed" {{ $status==='failed'?'selected':'' }}>Failed</option>
  </select>
  <button type="submit" class="btn-adm"><i class="bi bi-funnel"></i> Filter</button>
  @if($method || $status)
  <a href="{{ route('oxalis.admin.events') }}" class="btn-adm-ghost">Clear</a>
  @endif
  <span style="color:var(--adm-muted);font-size:.75rem;margin-left:auto">
    {{ number_format($events->total()) }} events
  </span>
</form>

<div class="adm-table-wrap mb-3">
  <div style="overflow-x:auto">
  <table class="adm-table">
    <thead><tr>
      <th style="padding-left:1.25rem">When</th>
      <th>User</th>
      <th>Method</th>
      <th class="d-none d-md-table-cell">IP address</th>
      <th style="padding-right:1.25rem;text-align:right">Status</th>
    </tr></thead>
    <tbody>
      @forelse($events as $e)
      <tr>
        <td style="padding-left:1.25rem;color:var(--adm-muted);white-space:nowrap">{{ $e->created_at->diffForHumans() }}</td>
        <td style="font-family:monospace;font-size:.72rem;color:var(--adm-muted)">{{ substr((string)$e->user_id,0,8) }}…</td>
        <td><span class="adm-badge adm-badge-ox">{{ $e->method }}</span></td>
        <td class="d-none d-md-table-cell" style="font-family:monospace;font-size:.72rem;color:var(--adm-muted)">{{ $e->ip_address??'—' }}</td>
        <td style="padding-right:1.25rem;text-align:right">
          @if($e->status==='success')
          <span class="adm-badge adm-badge-green"><i class="bi bi-check-circle-fill"></i> OK</span>
          @else
          <span class="adm-badge adm-badge-red"><i class="bi bi-x-circle-fill"></i> Failed</span>
          @endif
        </td>
      </tr>
      @empty
      <tr><td colspan="5" style="text-align:center;padding:2rem;color:var(--adm-muted)">No events found.</td></tr>
      @endforelse
    </tbody>
  </table>
  </div>
</div>

@if($events->hasPages())
<div class="d-flex align-items-center justify-content-between" style="font-size:.75rem;color:var(--adm-muted)">
  <span>Showing {{ $events->firstItem() }}–{{ $events->lastItem() }} of {{ number_format($events->total()) }}</span>
  <div class="d-flex gap-1">
    @if($events->onFirstPage())
    <span class="adm-filter-btn" style="opacity:.4;cursor:default">&lsaquo; Prev</span>
    @else
    <a href="{{ $events->previousPageUrl() }}" class="adm-filter-btn">&lsaquo; Prev</a>
    @endif
    @if($events->hasMorePages())
    <a href="{{ $events->nextPageUrl() }}" class="adm-filter-btn">Next &rsaquo;</a>
    @else
    <span class="adm-filter-btn" style="opacity:.4;cursor:default">Next &rsaquo;</span>
    @endif
  </div>
</div>
@endif
@endsection
