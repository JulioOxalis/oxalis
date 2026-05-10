<!DOCTYPE html><html><body style="font-family:system-ui,-apple-system,sans-serif;max-width:480px;margin:40px auto;padding:0 20px;color:#1a1a2e">
<div style="background:#5c6ac4;height:4px;border-radius:4px 4px 0 0;margin-bottom:32px"></div>
<h2 style="margin:0 0 8px;font-size:1.3rem">Your one-time code</h2>
<p style="color:#555;font-size:.9rem;margin:0 0 24px">Use the code below to sign in. It expires in {{ $expiresInMinutes }} minutes.</p>
<div style="background:#f4f5fb;border:1px solid #e0e2f0;border-radius:12px;text-align:center;padding:28px 20px;margin-bottom:24px">
  <span style="font-size:2.4rem;font-weight:700;letter-spacing:.2em;font-family:monospace;color:#5c6ac4">{{ $code }}</span>
</div>
<p style="color:#888;font-size:.8rem">If you didn't request this, you can safely ignore this email.</p>
</body></html>