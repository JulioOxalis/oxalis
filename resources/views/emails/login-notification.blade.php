<!DOCTYPE html><html><body style="font-family:system-ui,-apple-system,sans-serif;max-width:480px;margin:40px auto;padding:0 20px;color:#1a1a2e">
<div style="background:#5c6ac4;height:4px;border-radius:4px 4px 0 0;margin-bottom:32px"></div>
<h2 style="margin:0 0 8px;font-size:1.3rem">New sign-in detected</h2>
<p style="color:#555;font-size:.9rem;margin:0 0 20px">A new sign-in was recorded on your account.</p>
<table style="width:100%;border-collapse:collapse;font-size:.85rem;margin-bottom:24px">
  <tr><td style="padding:8px 0;color:#888;width:90px">Method</td><td style="padding:8px 0;font-weight:500">{{ $method }}</td></tr>
  <tr><td style="padding:8px 0;color:#888;border-top:1px solid #f0f0f0">IP address</td><td style="padding:8px 0;font-family:monospace;border-top:1px solid #f0f0f0">{{ $ip }}</td></tr>
  <tr><td style="padding:8px 0;color:#888;border-top:1px solid #f0f0f0">Device</td><td style="padding:8px 0;border-top:1px solid #f0f0f0;font-size:.78rem">{{ Str::limit($userAgent,60) }}</td></tr>
  <tr><td style="padding:8px 0;color:#888;border-top:1px solid #f0f0f0">Time</td><td style="padding:8px 0;border-top:1px solid #f0f0f0">{{ $time }}</td></tr>
</table>
<p style="color:#555;font-size:.85rem">If this wasn't you, <a href="{{ url('/oxalis/account/security') }}" style="color:#5c6ac4">secure your account immediately</a>.</p>
</body></html>