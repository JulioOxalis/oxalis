@extends('oxalis::layouts.admin-panel')
@section('title','Invite codes')
@section('content')

<div class="row g-4">
<div class="col-12 col-lg-4">
  <div class="adm-section">Generate invite</div>
  <div class="adm-card">
    <form method="POST" action="{{ route('oxalis.admin.invites.store') }}">
      @csrf
      <div class="mb-3">
        <label class="adm-label">Note (optional)</label>
        <input type="text" name="note" class="adm-input" placeholder="e.g. Beta tester - John">
      </div>
      <div class="mb-3">
        <label class="adm-label">Max uses</label>
        <input type="number" name="max_uses" class="adm-input" value="1" min="1" max="1000">
      </div>
      <div class="mb-4">
        <label class="adm-label">Expires in (days, optional)</label>
        <input type="number" name="expires_days" class="adm-input" placeholder="Leave blank = never">
      </div>
      <button type="submit" class="btn-adm w-100 justify-content-center">
        <i class="bi bi-plus-lg"></i> Generate code
      </button>
    </form>
    <div class="mt-3" style="font-size:.72rem;color:var(--adm-muted);border-top:1px solid var(--adm-border);padding-top:.75rem">
      Enable invite-only registration: <code style="color:var(--ox)">OXALIS_INVITE_ONLY=true</code>
    </div>
  </div>
</div>

<div class="col-12 col-lg-8">
  <div class="adm-section">All invite codes</div>
  <div class="adm-table-wrap">
    <div style="overflow-x:auto">
    <table class="adm-table">
      <thead><tr>
        <th style="padding-left:1.25rem">Code</th>
        <th>Note</th>
        <th class="text-center">Uses</th>
        <th class="d-none d-md-table-cell">Expires</th>
        <th style="padding-right:1.25rem;text-align:right">Actions</th>
      </tr></thead>
      <tbody>
        @forelse($invites as $inv)
        <tr>
          <td style="padding-left:1.25rem">
            <span class="font-monospace fw-bold" style="color:var(--ox);font-size:.85rem;letter-spacing:.08em">{{ $inv->code }}</span>
          </td>
          <td style="color:var(--adm-muted);font-size:.78rem">{{ $inv->note ?? '—' }}</td>
          <td class="text-center">
            <span class="adm-badge {{ $inv->uses >= $inv->max_uses ? 'adm-badge-red' : 'adm-badge-green' }}">
              {{ $inv->uses }} / {{ $inv->max_uses }}
            </span>
          </td>
          <td class="d-none d-md-table-cell" style="font-size:.75rem;color:var(--adm-muted)">
            {{ $inv->expires_at ? $inv->expires_at->diffForHumans() : 'Never' }}
          </td>
          <td style="padding-right:1.25rem;text-align:right">
            <form method="POST" action="{{ route('oxalis.admin.invites.destroy') }}" style="display:inline">
              @csrf
              <input type="hidden" name="invite_id" value="{{ $inv->id }}">
              <button type="submit" class="adm-badge adm-badge-red" style="border:none;cursor:pointer"
                onclick="return confirm('Delete this invite code?')">
                <i class="bi bi-trash3"></i>
              </button>
            </form>
          </td>
        </tr>
        @empty
        <tr><td colspan="5" style="text-align:center;padding:2rem;color:var(--adm-muted)">No invite codes yet.</td></tr>
        @endforelse
      </tbody>
    </table>
    </div>
  </div>
  @if($invites->hasPages())
  <div class="mt-2">{{ $invites->links() }}</div>
  @endif
</div>
</div>
@endsection
