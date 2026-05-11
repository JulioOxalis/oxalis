@extends('oxalis::layouts.admin-panel')
@section('title','Webhooks')
@section('content')

<div class="row g-4">
<div class="col-12 col-lg-4">
  <div class="adm-section">Add webhook</div>
  <div class="adm-card">
    <form method="POST" action="{{ route('oxalis.admin.webhooks.store') }}">
      @csrf
      <div class="mb-3">
        <label class="adm-label">Endpoint URL</label>
        <input type="url" name="url" class="adm-input" placeholder="https://myapp.com/hooks/oxalis" required>
      </div>
      <div class="mb-3">
        <label class="adm-label">Events to receive</label>
        <div class="d-flex flex-wrap gap-2 mt-1">
          @foreach($availableEvents as $ev)
          <label style="display:flex;align-items:center;gap:.4rem;font-size:.78rem;color:var(--adm-text);cursor:pointer">
            <input type="checkbox" name="events[]" value="{{ $ev }}" {{ $ev==='*' ? 'checked' : '' }}>
            <span class="font-monospace" style="font-size:.72rem;color:var(--ox)">{{ $ev }}</span>
          </label>
          @endforeach
        </div>
      </div>
      <div class="mb-4">
        <label class="adm-label">Note (optional)</label>
        <input type="text" name="note" class="adm-input" placeholder="e.g. Slack alerts">
      </div>
      <button type="submit" class="btn-adm w-100 justify-content-center">
        <i class="bi bi-plus-lg"></i> Add webhook
      </button>
    </form>
    <div class="mt-3" style="font-size:.72rem;color:var(--adm-muted);border-top:1px solid var(--adm-border);padding-top:.75rem">
      Enable webhooks: <code style="color:var(--ox)">OXALIS_WEBHOOKS=true</code><br>
      Payloads are signed with <code style="color:var(--ox)">X-Oxalis-Signature: sha256=...</code>
    </div>
  </div>
</div>

<div class="col-12 col-lg-8">
  <div class="adm-section">Registered webhooks</div>
  <div class="adm-table-wrap">
    <div style="overflow-x:auto">
    <table class="adm-table">
      <thead><tr>
        <th style="padding-left:1.25rem">URL</th>
        <th>Events</th>
        <th class="text-center">Status</th>
        <th class="d-none d-md-table-cell text-center">Failures</th>
        <th style="padding-right:1.25rem;text-align:right">Actions</th>
      </tr></thead>
      <tbody>
        @forelse($webhooks as $hook)
        <tr>
          <td style="padding-left:1.25rem;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
            <span style="font-size:.75rem;color:var(--adm-text)">{{ $hook->url }}</span>
            @if($hook->note)
            <div style="font-size:.68rem;color:var(--adm-muted)">{{ $hook->note }}</div>
            @endif
          </td>
          <td>
            <div class="d-flex flex-wrap gap-1">
              @foreach($hook->events as $ev)
              <span class="adm-badge adm-badge-ox" style="font-size:.62rem">{{ $ev }}</span>
              @endforeach
            </div>
          </td>
          <td class="text-center">
            @if($hook->active)
            <span class="adm-badge adm-badge-green"><i class="bi bi-circle-fill" style="font-size:.4rem"></i> Active</span>
            @else
            <span class="adm-badge adm-badge-red"><i class="bi bi-circle-fill" style="font-size:.4rem"></i> Disabled</span>
            @endif
          </td>
          <td class="d-none d-md-table-cell text-center">
            <span class="{{ $hook->failures > 5 ? 'text-danger' : '' }}">{{ $hook->failures }}</span>
          </td>
          <td style="padding-right:1.25rem;text-align:right;white-space:nowrap">
            <form method="POST" action="{{ route('oxalis.admin.webhooks.toggle') }}" style="display:inline">
              @csrf
              <input type="hidden" name="webhook_id" value="{{ $hook->id }}">
              <button type="submit" class="adm-badge {{ $hook->active ? 'adm-badge-gray' : 'adm-badge-green' }}" style="border:none;cursor:pointer">
                {{ $hook->active ? 'Disable' : 'Enable' }}
              </button>
            </form>
            <form method="POST" action="{{ route('oxalis.admin.webhooks.destroy') }}" style="display:inline;margin-left:.35rem">
              @csrf
              <input type="hidden" name="webhook_id" value="{{ $hook->id }}">
              <button type="submit" class="adm-badge adm-badge-red" style="border:none;cursor:pointer"
                onclick="return confirm('Delete this webhook?')">
                <i class="bi bi-trash3"></i>
              </button>
            </form>
          </td>
        </tr>
        @empty
        <tr><td colspan="5" style="text-align:center;padding:2rem;color:var(--adm-muted)">No webhooks yet.</td></tr>
        @endforelse
      </tbody>
    </table>
    </div>
  </div>
</div>
</div>
@endsection
