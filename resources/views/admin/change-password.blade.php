@extends('oxalis::layouts.admin-panel')
@section('title','Change password')
@section('content')

<div style="max-width:480px">
  <div class="adm-section">Admin credentials</div>

  <div class="adm-card">
    <form method="POST" action="{{ route('oxalis.admin.password.post') }}">
      @csrf
      <div class="field mb-3">
        <label class="adm-label">Current password</label>
        <input type="password" name="current_password" class="adm-input" placeholder="Your current admin password" autocomplete="current-password">
      </div>
      <div class="field mb-3">
        <label class="adm-label">New password <span style="color:var(--adm-muted)">(min 12 chars)</span></label>
        <input type="password" name="password" id="new-pw" class="adm-input" placeholder="New password" autocomplete="new-password">
        <div class="strength-bar mt-2" style="height:4px;border-radius:50rem;background:#1a1d2e;overflow:hidden">
          <div id="pw-fill" style="height:100%;border-radius:50rem;transition:width .3s,background .3s;width:0"></div>
        </div>
      </div>
      <div class="field mb-4">
        <label class="adm-label">Confirm new password</label>
        <input type="password" name="password_confirmation" class="adm-input" placeholder="Repeat new password" autocomplete="new-password">
      </div>
      <div class="d-flex gap-2">
        <button type="submit" class="btn-adm"><i class="bi bi-key-fill"></i> Update password</button>
        <a href="{{ route('oxalis.admin') }}" class="btn-adm-ghost">Cancel</a>
      </div>
    </form>
  </div>

  <div class="adm-card mt-3" style="border-color:rgba(239,68,68,.2)">
    <div style="font-size:.78rem;color:#f87171;font-weight:600;margin-bottom:.35rem">
      <i class="bi bi-exclamation-triangle-fill me-2"></i>Session security note
    </div>
    <div style="font-size:.75rem;color:var(--adm-muted);line-height:1.6">
      Changing the password immediately invalidates all other active admin sessions.
      Your current session will remain active with the new credentials.
    </div>
  </div>
</div>

@endsection
@push('scripts')
<script>
var pw=document.getElementById('new-pw'),fill=document.getElementById('pw-fill');
pw.addEventListener('input',function(){
  var v=this.value,s=0;
  s+=(v.length>=12?1:0)+(/[A-Z]/.test(v)?1:0)+(/[0-9]/.test(v)?1:0)+(/[^A-Za-z0-9]/.test(v)?1:0);
  fill.style.width=[0,25,50,75,100][s]+'%';
  fill.style.background=['','#ef4444','#f59e0b','#3b82f6','#10b981'][s]||'#ef4444';
});
</script>
@endpush
