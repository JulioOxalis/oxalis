<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Julio Oxalis — Documentation</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link id="prism-theme" rel="stylesheet" href="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/themes/prism-tomorrow.min.css">
<script>
(function(){
  var p=localStorage.getItem('ox-docs-theme')||(window.matchMedia('(prefers-color-scheme:dark)').matches?'dark':'light');
  document.documentElement.setAttribute('data-theme',p);
  document.getElementById('prism-theme').href=p==='dark'
    ?'https://cdn.jsdelivr.net/npm/prismjs@1.29.0/themes/prism-tomorrow.min.css'
    :'https://cdn.jsdelivr.net/npm/prismjs@1.29.0/themes/prism.min.css';
})();
</script>
<style>
/* ── Theme variables ───────────────────────────────────────────────────────── */
:root{
  --ox:#5c6ac4;--ox-dk:#4959b8;--ox-sf:rgba(92,106,196,.14);
  --sidebar-w:260px;
  /* light defaults */
  --sidebar-bg:#ffffff;
  --sidebar-border:#e5e7eb;
  --content-bg:#f7f8fc;
  --border:#e5e7eb;
  --text:#4b5563;
  --muted:#9ca3af;
  --heading:#111827;
  --code-bg:#f3f4f6;
  --code-color:#4338ca;
  --nav-link:#6b7280;
  --nav-link-active:#111827;
  --feature-bg:#ffffff;
  --install-bg:#f3f4f6;
  --badge-gray-bg:#f3f4f6;
  --badge-gray-color:#6b7280;
  --search-bg:#f9fafb;
  --row-border:#f3f4f6;
  --td-first-color:#16a34a;
  --alert-info-bg:rgba(92,106,196,.1);
  --alert-info-color:#4338ca;
  --alert-warn-bg:rgba(234,179,8,.1);
  --alert-warn-color:#92400e;
  --alert-tip-bg:rgba(16,185,129,.1);
  --alert-tip-color:#065f46;
  --shadow:0 1px 3px rgba(0,0,0,.08);
}
html[data-theme=dark]{
  --sidebar-bg:#0d0f18;
  --sidebar-border:#1e2132;
  --content-bg:#111318;
  --border:#1e2132;
  --text:#c9cfe8;
  --muted:#6b7280;
  --heading:#f9fafb;
  --code-bg:#0a0c14;
  --code-color:#a5b4fc;
  --nav-link:#8892b0;
  --nav-link-active:#ffffff;
  --feature-bg:#0d0f18;
  --install-bg:#0a0c14;
  --badge-gray-bg:#1e2132;
  --badge-gray-color:#6b7280;
  --search-bg:#1a1d2e;
  --row-border:#171a2a;
  --td-first-color:#34d399;
  --alert-info-bg:rgba(92,106,196,.15);
  --alert-info-color:#a5b4fc;
  --alert-warn-bg:rgba(234,179,8,.08);
  --alert-warn-color:#fde047;
  --alert-tip-bg:rgba(16,185,129,.08);
  --alert-tip-color:#6ee7b7;
  --shadow:0 1px 3px rgba(0,0,0,.4);
}

/* ── Base ──────────────────────────────────────────────────────────────────── */
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:system-ui,-apple-system,sans-serif;background:var(--content-bg);color:var(--text);display:flex;min-height:100vh;font-size:.9rem;line-height:1.7;transition:background .2s,color .2s}

/* ── Sidebar ───────────────────────────────────────────────────────────────── */
#sidebar{width:var(--sidebar-w);flex-shrink:0;background:var(--sidebar-bg);border-right:1px solid var(--border);position:fixed;top:0;left:0;height:100vh;overflow-y:auto;display:flex;flex-direction:column;z-index:100;transition:background .2s}
#sidebar::-webkit-scrollbar{width:4px}
#sidebar::-webkit-scrollbar-thumb{background:var(--border);border-radius:4px}
.sidebar-logo{padding:1.25rem 1.25rem .9rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.brand{font-size:1.05rem;font-weight:700;letter-spacing:-.02em;color:var(--heading)}
.brand span{color:var(--ox)}
.version{font-size:.68rem;background:var(--ox-sf);color:var(--ox);padding:.15rem .55rem;border-radius:50rem;margin-top:.35rem;display:inline-block;letter-spacing:.03em}
.theme-toggle{background:none;border:1.5px solid var(--border);border-radius:8px;color:var(--muted);cursor:pointer;padding:.28rem .5rem;font-size:.85rem;display:flex;align-items:center;transition:all .15s;flex-shrink:0}
.theme-toggle:hover{border-color:var(--ox);color:var(--ox)}
.sidebar-search{padding:.75rem 1rem;border-bottom:1px solid var(--border)}
.sidebar-search input{width:100%;background:var(--search-bg);border:1px solid var(--border);color:var(--text);border-radius:8px;padding:.45rem .75rem;font-size:.8rem;outline:none;transition:border .15s}
.sidebar-search input:focus{border-color:var(--ox)}
.sidebar-search input::placeholder{color:var(--muted)}
nav{padding:.75rem 0 2rem}
.nav-section{font-size:.65rem;letter-spacing:.12em;text-transform:uppercase;font-weight:700;color:var(--muted);padding:.75rem 1.25rem .35rem;margin-top:.5rem}
.nav-link{display:block;padding:.38rem 1.25rem;color:var(--nav-link);font-size:.82rem;text-decoration:none;border-left:2px solid transparent;transition:all .15s}
.nav-link:hover,.nav-link.active{color:var(--nav-link-active);border-left-color:var(--ox);background:var(--ox-sf)}

/* ── Main (centered in remaining space) ────────────────────────────────────── */
#main{margin-left:var(--sidebar-w);flex:1;display:flex;justify-content:center;padding:3.5rem 2rem 6rem;min-height:100vh}
.doc-content{width:100%;max-width:760px}

/* ── Typography ────────────────────────────────────────────────────────────── */
h1{font-size:2.2rem;font-weight:800;letter-spacing:-.04em;color:var(--heading);line-height:1.2;margin-bottom:.5rem}
h2{font-size:1.35rem;font-weight:700;color:var(--heading);letter-spacing:-.02em;margin:3rem 0 1rem;padding-top:1rem;border-top:1px solid var(--border)}
h3{font-size:1.05rem;font-weight:600;color:var(--heading);margin:2rem 0 .75rem}
p{color:var(--text);margin-bottom:1rem}
strong{color:var(--heading)}
.lead{font-size:1.05rem;color:var(--muted);margin-bottom:1.5rem;line-height:1.85}
a{color:var(--ox);text-decoration:none}
a:hover{color:var(--ox-dk)}
ul,ol{color:var(--text)}

/* ── Hero ──────────────────────────────────────────────────────────────────── */
.hero{padding-bottom:2.5rem;margin-bottom:1rem;border-bottom:1px solid var(--border)}
.hero-badges{display:flex;flex-wrap:wrap;gap:.5rem;margin-bottom:1.5rem}
.badge-pill{font-size:.72rem;padding:.3rem .8rem;border-radius:50rem;font-weight:500;letter-spacing:.03em}
.badge-ox{background:var(--ox-sf);color:var(--ox)}
.badge-green{background:rgba(16,185,129,.12);color:#16a34a}
html[data-theme=dark] .badge-green{color:#34d399}
.badge-gray{background:var(--badge-gray-bg);color:var(--badge-gray-color)}

/* ── Install box ───────────────────────────────────────────────────────────── */
.install-box{background:var(--install-bg);border:1px solid var(--border);border-radius:12px;padding:1rem 1.25rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;margin:1.5rem 0;font-family:monospace;font-size:.88rem}
.install-box code{color:var(--ox)}
.copy-btn{background:transparent;border:1px solid var(--border);color:var(--muted);border-radius:6px;padding:.25rem .65rem;font-size:.75rem;cursor:pointer;transition:all .15s;white-space:nowrap}
.copy-btn:hover{border-color:var(--ox);color:var(--ox)}

/* ── Code blocks ───────────────────────────────────────────────────────────── */
pre[class*="language-"]{border-radius:10px;margin:0;font-size:.8rem;box-shadow:var(--shadow)}
.code-wrap{position:relative;margin:1rem 0}
.code-wrap .copy-btn{position:absolute;top:.6rem;right:.6rem;z-index:1}
:not(pre) code{background:var(--code-bg);border:1px solid var(--border);padding:.1rem .4rem;border-radius:5px;font-size:.82em;color:var(--code-color);font-family:monospace}

/* ── Steps ─────────────────────────────────────────────────────────────────── */
.steps{counter-reset:step;display:flex;flex-direction:column;gap:1.25rem;margin:1.25rem 0}
.step{display:flex;gap:1rem;align-items:flex-start}
.step-num{counter-increment:step;width:28px;height:28px;border-radius:50%;background:var(--ox-sf);color:var(--ox);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.78rem;flex-shrink:0;margin-top:.1rem}
.step-num::before{content:counter(step)}
.step-body{flex:1}

/* ── Feature grid ──────────────────────────────────────────────────────────── */
.feature-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:1rem;margin:1.25rem 0}
.feature-card{background:var(--feature-bg);border:1px solid var(--border);border-radius:12px;padding:1.25rem;transition:border-color .2s,box-shadow .2s}
.feature-card:hover{border-color:var(--ox);box-shadow:0 2px 12px var(--ox-sf)}
.feature-icon{width:36px;height:36px;border-radius:9px;background:var(--ox-sf);color:var(--ox);display:flex;align-items:center;justify-content:center;font-size:1rem;margin-bottom:.75rem}
.feature-card h4{font-size:.88rem;font-weight:600;color:var(--heading);margin-bottom:.35rem}
.feature-card p{font-size:.78rem;color:var(--muted);margin:0;line-height:1.6}

/* ── Tables ────────────────────────────────────────────────────────────────── */
table{width:100%;border-collapse:collapse;margin:1rem 0;font-size:.8rem}
thead tr{border-bottom:1px solid var(--border)}
th{text-align:left;color:var(--muted);padding:.6rem 1rem;font-weight:500;font-size:.72rem;letter-spacing:.06em;text-transform:uppercase}
td{padding:.6rem 1rem;border-bottom:1px solid var(--row-border);vertical-align:top;color:var(--text)}
td:first-child{color:var(--td-first-color);font-family:monospace;font-size:.8rem;white-space:nowrap}
td code{background:var(--code-bg);border:1px solid var(--border);padding:.1rem .4rem;border-radius:4px;font-size:.78rem;color:var(--code-color)}

/* ── Alert boxes ───────────────────────────────────────────────────────────── */
.alert-box{border-radius:10px;padding:1rem 1.25rem;margin:1.25rem 0;display:flex;gap:.75rem;font-size:.85rem}
.alert-box i{font-size:1.1rem;margin-top:.1rem;flex-shrink:0}
.alert-info{background:var(--alert-info-bg);border-left:3px solid var(--ox);color:var(--alert-info-color)}
.alert-warn{background:var(--alert-warn-bg);border-left:3px solid #eab308;color:var(--alert-warn-color)}
.alert-tip{background:var(--alert-tip-bg);border-left:3px solid #10b981;color:var(--alert-tip-color)}

/* ── Config rows ───────────────────────────────────────────────────────────── */
.config-row{display:flex;gap:1rem;padding:.75rem 0;border-bottom:1px solid var(--row-border);align-items:flex-start;flex-wrap:wrap}
.config-key{font-family:monospace;font-size:.78rem;color:var(--code-color);min-width:200px;flex-shrink:0}
.config-desc{font-size:.8rem;color:var(--text);flex:1}
.config-default{font-family:monospace;font-size:.72rem;color:var(--muted);white-space:nowrap}

/* ── Mobile ────────────────────────────────────────────────────────────────── */
#menu-toggle{display:none}
@media(max-width:768px){
  body{flex-direction:column}
  #sidebar{position:fixed;transform:translateX(-100%);transition:transform .25s;z-index:200}
  #sidebar.open{transform:translateX(0)}
  #main{margin-left:0;padding:1.5rem 1.25rem 4rem}
  #menu-toggle{display:flex;position:fixed;bottom:1.5rem;right:1.5rem;width:48px;height:48px;border-radius:50%;background:var(--ox);color:#fff;border:none;align-items:center;justify-content:center;font-size:1.25rem;z-index:300;cursor:pointer;box-shadow:0 4px 16px rgba(92,106,196,.4)}
}
</style>
</head>
<body>

<aside id="sidebar">
  <div class="sidebar-logo">
    <div>
      <div class="brand">JULIO <span>OXALIS</span></div>
    </div>
    <button class="theme-toggle" id="theme-btn" title="Toggle theme">
      <i class="bi bi-sun-fill" id="theme-icon"></i>
    </button>
  </div>
  <div class="sidebar-search">
    <input type="text" id="doc-search" placeholder="Search docs…" autocomplete="off">
  </div>
  <nav id="doc-nav">
    <div class="nav-section">Introduction</div>
    <a class="nav-link" href="#what-is-oxalis">What is Oxalis?</a>
    <a class="nav-link" href="#requirements">Requirements</a>

    <div class="nav-section">Getting Started</div>
    <a class="nav-link" href="#installation">Installation</a>
    <a class="nav-link" href="#configuration">Configuration</a>
    <a class="nav-link" href="#reconfiguring">Reconfiguring</a>
    <a class="nav-link" href="#dev-mode">Dev mode</a>
    <a class="nav-link" href="#updating">Updating</a>
    <a class="nav-link" href="#uninstalling">Uninstalling</a>
    <a class="nav-link" href="#env-reference">.env Reference</a>

    <div class="nav-section">Auth Methods</div>
    <a class="nav-link" href="#passkeys">Passkeys (WebAuthn)</a>
    <a class="nav-link" href="#email-otp">Email OTP</a>
    <a class="nav-link" href="#magic-link">Magic Link</a>
    <a class="nav-link" href="#totp">Authenticator (TOTP)</a>
    <a class="nav-link" href="#password">Password</a>
    <a class="nav-link" href="#social">Social Login</a>
    <a class="nav-link" href="#smart-dispatch">Smart Dispatch</a>

    <div class="nav-section">Developer API</div>
    <a class="nav-link" href="#routes">Routes Reference</a>
    <a class="nav-link" href="#components">Blade Components</a>
    <a class="nav-link" href="#events">Events</a>
    <a class="nav-link" href="#security">Security Features</a>
    <a class="nav-link" href="#account-features">Account features</a>
    <a class="nav-link" href="#user-command">oxalis:user command</a>
    <a class="nav-link" href="#customizing">Customizing</a>

    <div class="nav-section">Admin Panel</div>
    <a class="nav-link" href="#admin-setup">Setup &amp; login</a>
    <a class="nav-link" href="#admin-dashboard">Dashboard</a>
    <a class="nav-link" href="#admin-security">Admin security</a>

    <div class="nav-section">Analytics</div>
    <a class="nav-link" href="#app-stats">User Stats Dashboard</a>
  </nav>
</aside>

<main id="main">
<div class="doc-content">

  <!-- HERO -->
  <section class="hero" id="what-is-oxalis">
    <div class="hero-badges">
      <span class="badge-pill badge-ox"><i class="bi bi-fingerprint me-1"></i>WebAuthn</span>
      <span class="badge-pill badge-green"><i class="bi bi-shield-check me-1"></i>Rate limited</span>
      <span class="badge-pill badge-gray">PHP 8.2+</span>
      <span class="badge-pill badge-gray">Laravel</span>
      <span class="badge-pill badge-gray">MongoDB safe</span>
    </div>
    <h1>Julio Oxalis</h1>
    <p class="lead">Advanced, multi-method authentication for Laravel. Drop it in, run one Artisan command, and your app gets WebAuthn passkeys, magic links, OTP, TOTP, social login, step-up auth, and rate-limited security — with zero boilerplate.</p>
    <div class="install-box">
      <code id="install-cmd">composer require julio/oxalis</code>
      <button class="copy-btn" onclick="copy('install-cmd',this)"><i class="bi bi-clipboard"></i> Copy</button>
    </div>
    <div class="feature-grid">
      <div class="feature-card"><div class="feature-icon"><i class="bi bi-fingerprint"></i></div><h4>Passkeys (FIDO2)</h4><p>Fingerprint, Face ID, hardware keys — no password needed. Powered by webauthn-framework v5.3.</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="bi bi-envelope-paper"></i></div><h4>Magic Link</h4><p>One-click sign-in link delivered by email. Expires in 15 min, single use.</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="bi bi-123"></i></div><h4>Email OTP</h4><p>6-digit code via email, bcrypt-hashed in DB, 5-attempt lockout per challenge.</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="bi bi-phone"></i></div><h4>TOTP (2FA)</h4><p>Google Authenticator, Authy, 1Password — enforced as second factor for every login method.</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="bi bi-lock"></i></div><h4>Password</h4><p>Classic email+password with bcrypt, DB-level lockout, and forgot-password flow.</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="bi bi-google"></i></div><h4>Social Login</h4><p>Google and GitHub OAuth via Laravel Socialite. Enable per provider in .env.</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="bi bi-shield-lock"></i></div><h4>Step-up Auth</h4><p>Require TOTP or passkey verification before sensitive actions, with a 15-min grace window.</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="bi bi-lightning"></i></div><h4>Smart Dispatch</h4><p>One field. User types their email — Oxalis picks the fastest available method automatically.</p></div>
    </div>
  </section>

  <!-- REQUIREMENTS -->
  <section id="requirements">
    <h2>Requirements</h2>
    <ul style="padding-left:1.25rem">
      <li>PHP 8.2 or higher</li>
      <li>Laravel 11+</li>
      <li>A relational database (MySQL, PostgreSQL, or SQLite) — <strong>even if your app's default is MongoDB</strong>, set <code>OXALIS_DB_CONNECTION</code> to a separate SQL connection</li>
      <li>Mail configured for OTP / magic-link / notifications</li>
    </ul>
    <div class="alert-box alert-warn" style="margin-top:1rem">
      <i class="bi bi-exclamation-triangle-fill"></i>
      <div><strong>MongoDB apps:</strong> Set <code>OXALIS_DB_CONNECTION=mysql</code> (or any SQL connection) in <code>.env</code>. Oxalis stores auth data in SQL; your app data can stay on MongoDB.</div>
    </div>
  </section>

  <!-- INSTALLATION -->
  <section id="installation">
    <h2>Installation</h2>
    <div class="steps">
      <div class="step"><div class="step-num"></div><div class="step-body"><strong>Require the package</strong>
        <div class="code-wrap"><button class="copy-btn" onclick="copyPre(this)"><i class="bi bi-clipboard"></i></button>
        <pre class="language-bash"><code>composer require julio/oxalis</code></pre></div>
      </div></div>
      <div class="step"><div class="step-num"></div><div class="step-body"><strong>Run the interactive installer</strong>
        <div class="code-wrap"><button class="copy-btn" onclick="copyPre(this)"><i class="bi bi-clipboard"></i></button>
        <pre class="language-bash"><code>php artisan oxalis:install</code></pre></div>
        The wizard asks which auth methods to enable, whether to activate Smart Dispatch, social OAuth credentials, and where to redirect after login. It writes all needed <code>.env</code> variables and runs migrations automatically.
      </div></div>
      <div class="step"><div class="step-num"></div><div class="step-body"><strong>Done.</strong> Visit <code>/oxalis/login</code> — your login page is live. <code>/login</code> and <code>/register</code> now redirect there automatically.</div></div>
    </div>
    <div class="alert-box alert-tip">
      <i class="bi bi-lightbulb-fill"></i>
      <div><strong>Publishing views (optional)</strong> — run <code>php artisan vendor:publish --tag=oxalis-views</code> to copy all Blade views into <code>resources/views/vendor/oxalis/</code> for full customisation.</div>
    </div>
  </section>

  <!-- CONFIGURATION -->
  <section id="configuration">
    <h2>Configuration</h2>
    <p>Publish the config file to customise any setting:</p>
    <div class="code-wrap"><button class="copy-btn" onclick="copyPre(this)"><i class="bi bi-clipboard"></i></button>
    <pre class="language-bash"><code>php artisan vendor:publish --tag=oxalis-config</code></pre></div>
    <p>This creates <code>config/oxalis.php</code> in your application.</p>
  </section>

  <!-- RECONFIGURING -->
  <section id="reconfiguring">
    <h2>Reconfiguring after install</h2>
    <p>Forgot to enable an auth method during setup? No need to reinstall — edit <code>.env</code> directly and it takes effect immediately.</p>

    <h3>Toggle any method on or off</h3>
    <div class="code-wrap"><button class="copy-btn" onclick="copyPre(this)"><i class="bi bi-clipboard"></i></button>
    <pre class="language-bash"><code>OXALIS_ENABLE_PASSKEY=true
OXALIS_ENABLE_MAGIC_LINK=true
OXALIS_ENABLE_EMAIL_OTP=true
OXALIS_ENABLE_TOTP=true
OXALIS_ENABLE_PASSWORD=true
OXALIS_SMART_DISPATCH=false</code></pre></div>

    <h3>Enable social login later</h3>
    <div class="code-wrap"><button class="copy-btn" onclick="copyPre(this)"><i class="bi bi-clipboard"></i></button>
    <pre class="language-bash"><code>OXALIS_ENABLE_SOCIAL=true

# Google
OXALIS_GOOGLE_ENABLED=true
GOOGLE_CLIENT_ID=your-client-id
GOOGLE_CLIENT_SECRET=your-secret
GOOGLE_REDIRECT_URI=https://myapp.com/oxalis/social/google/callback

# GitHub
OXALIS_GITHUB_ENABLED=true
GITHUB_CLIENT_ID=your-client-id
GITHUB_CLIENT_SECRET=your-secret
GITHUB_REDIRECT_URI=https://myapp.com/oxalis/social/github/callback</code></pre></div>

    <p>Or re-run the installer at any time — it updates existing <code>.env</code> values in-place without overwriting anything else:</p>
    <div class="code-wrap"><button class="copy-btn" onclick="copyPre(this)"><i class="bi bi-clipboard"></i></button>
    <pre class="language-bash"><code>php artisan oxalis:install</code></pre></div>

    <div class="alert-box alert-tip">
      <i class="bi bi-lightbulb-fill"></i>
      <div>After changing <code>.env</code>, run <code>php artisan config:clear</code> if you have config caching enabled in production.</div>
    </div>
  </section>

  <!-- DEV MODE -->
  <section id="dev-mode">
    <h2>Dev mode</h2>
    <p>When <code>APP_ENV=local</code>, Oxalis shows sensitive values on-screen so you can test without a working mail server:</p>
    <table>
      <thead><tr><th>Feature</th><th>What shows in local</th><th>In production</th></tr></thead>
      <tbody>
        <tr><td>Email OTP</td><td>6-digit code shown in yellow box on verify page</td><td>Code sent by email only, never shown</td></tr>
        <tr><td>Magic link</td><td>Full sign-in URL shown on screen</td><td>Link sent by email only, never shown</td></tr>
        <tr><td>Registration OTP</td><td>6-digit code shown in yellow box on step 2</td><td>Code sent by email only, never shown</td></tr>
        <tr><td>Test hub</td><td>Available at <code>/oxalis/test</code></td><td>Returns 403 Forbidden</td></tr>
      </tbody>
    </table>
    <div class="alert-box alert-info">
      <i class="bi bi-info-circle-fill"></i>
      <div>All dev hints are gated behind <code>app()->isLocal()</code>. Setting <code>APP_ENV=production</code> in <code>.env</code> disables every one of them — no extra configuration needed.</div>
    </div>
  </section>

  <!-- UPDATING -->
  <section id="updating">
    <h2>Updating</h2>
    <div class="code-wrap"><button class="copy-btn" onclick="copyPre(this)"><i class="bi bi-clipboard"></i></button>
    <pre class="language-bash"><code>composer update julio/oxalis
php artisan migrate</code></pre></div>
    <p>Always run <code>php artisan migrate</code> after updating — new releases may include additional migrations.</p>
    <div class="alert-box alert-tip">
      <i class="bi bi-lightbulb-fill"></i>
      <div>If you published views with <code>vendor:publish --tag=oxalis-views</code>, re-publish after a major update to get the latest UI: <code>php artisan vendor:publish --tag=oxalis-views --force</code></div>
    </div>
  </section>

  <!-- UNINSTALLING -->
  <section id="uninstalling">
    <h2>Uninstalling</h2>
    <p>Run the removal wizard first, then remove the package from Composer:</p>
    <div class="steps">
      <div class="step"><div class="step-num"></div><div class="step-body"><strong>Run the removal wizard</strong>
        <div class="code-wrap"><button class="copy-btn" onclick="copyPre(this)"><i class="bi bi-clipboard"></i></button>
        <pre class="language-bash"><code>php artisan oxalis:remove</code></pre></div>
        The wizard will:
        <ul style="margin-top:.5rem;padding-left:1.25rem">
          <li>Remove all <code>OXALIS_*</code> variables from <code>.env</code></li>
          <li>Delete <code>config/oxalis.php</code></li>
          <li>Ask whether to delete published views in <code>resources/views/vendor/oxalis/</code></li>
          <li>Remove the Oxalis redirect block from <code>routes/web.php</code></li>
          <li>Ask whether to drop the database tables (deletes all auth data — use with caution)</li>
        </ul>
      </div></div>
      <div class="step"><div class="step-num"></div><div class="step-body"><strong>Remove the package</strong>
        <div class="code-wrap"><button class="copy-btn" onclick="copyPre(this)"><i class="bi bi-clipboard"></i></button>
        <pre class="language-bash"><code>composer remove julio/oxalis</code></pre></div>
      </div></div>
    </div>
    <div class="alert-box alert-warn">
      <i class="bi bi-exclamation-triangle-fill"></i>
      <div>Dropping tables permanently deletes all passkeys, TOTP secrets, OTP challenges, magic links, and auth event logs. Back up your database first if you need to keep that data.</div>
    </div>
  </section>

  <!-- ENV REFERENCE -->
  <section id="env-reference">
    <h2>.env Reference</h2>
    <div class="config-row"><div class="config-key">OXALIS_RP_ID</div><div class="config-desc">WebAuthn Relying Party ID — must be the hostname (e.g. <code>myapp.com</code>). Defaults to your <code>APP_URL</code> host.</div><div class="config-default">auto</div></div>
    <div class="config-row"><div class="config-key">OXALIS_RP_NAME</div><div class="config-desc">Friendly name shown in browser passkey dialogs.</div><div class="config-default">APP_NAME</div></div>
    <div class="config-row"><div class="config-key">OXALIS_ORIGINS</div><div class="config-desc">Comma-separated allowed WebAuthn origins. e.g. <code>https://myapp.com,https://www.myapp.com</code></div><div class="config-default">APP_URL</div></div>
    <div class="config-row"><div class="config-key">OXALIS_PREFIX</div><div class="config-desc">URL prefix for all Oxalis routes. Change to <code>auth</code> for <code>/auth/login</code> instead of <code>/oxalis/login</code>.</div><div class="config-default">oxalis</div></div>
    <div class="config-row"><div class="config-key">OXALIS_HOME</div><div class="config-desc">Where to redirect users after a successful login.</div><div class="config-default">/dashboard</div></div>
    <div class="config-row"><div class="config-key">OXALIS_DB_CONNECTION</div><div class="config-desc">SQL DB connection for Oxalis tables. Leave blank to use app default.</div><div class="config-default">null</div></div>
    <div class="config-row"><div class="config-key">OXALIS_ENABLE_PASSKEY</div><div class="config-desc">Show the passkey sign-in button on the login page.</div><div class="config-default">true</div></div>
    <div class="config-row"><div class="config-key">OXALIS_ENABLE_MAGIC_LINK</div><div class="config-desc">Show the magic-link send form.</div><div class="config-default">true</div></div>
    <div class="config-row"><div class="config-key">OXALIS_ENABLE_EMAIL_OTP</div><div class="config-desc">Show the one-time code send form.</div><div class="config-default">true</div></div>
    <div class="config-row"><div class="config-key">OXALIS_ENABLE_TOTP</div><div class="config-desc">Allow users to set up authenticator app 2FA.</div><div class="config-default">true</div></div>
    <div class="config-row"><div class="config-key">OXALIS_ENABLE_PASSWORD</div><div class="config-desc">Show the password login link.</div><div class="config-default">true</div></div>
    <div class="config-row"><div class="config-key">OXALIS_ENABLE_SOCIAL</div><div class="config-desc">Enable social login (requires provider config below).</div><div class="config-default">false</div></div>
    <div class="config-row"><div class="config-key">OXALIS_SMART_DISPATCH</div><div class="config-desc">Show the one-field "Smart sign-in" link on the login page.</div><div class="config-default">false</div></div>
    <div class="config-row"><div class="config-key">OXALIS_LOGIN_NOTIFICATION</div><div class="config-desc">Send an email to the user on every new sign-in.</div><div class="config-default">false</div></div>
    <div class="config-row"><div class="config-key">OXALIS_LOCKOUT_ATTEMPTS</div><div class="config-desc">Failed attempts before IP lockout kicks in.</div><div class="config-default">5</div></div>
    <div class="config-row"><div class="config-key">OXALIS_LOCKOUT_MINUTES</div><div class="config-desc">Minutes the lockout lasts.</div><div class="config-default">15</div></div>
    <div class="config-row"><div class="config-key">OXALIS_GOOGLE_ENABLED</div><div class="config-desc">Enable Google OAuth.</div><div class="config-default">false</div></div>
    <div class="config-row"><div class="config-key">GOOGLE_CLIENT_ID / SECRET</div><div class="config-desc">Google OAuth app credentials from console.cloud.google.com</div><div class="config-default">—</div></div>
    <div class="config-row"><div class="config-key">OXALIS_GITHUB_ENABLED</div><div class="config-desc">Enable GitHub OAuth.</div><div class="config-default">false</div></div>
    <div class="config-row"><div class="config-key">GITHUB_CLIENT_ID / SECRET</div><div class="config-desc">GitHub OAuth app credentials from github.com/settings/apps</div><div class="config-default">—</div></div>
  </section>

  <!-- PASSKEYS -->
  <section id="passkeys">
    <h2>Passkeys (WebAuthn / FIDO2)</h2>
    <p>No passwords. Users authenticate with biometrics (Touch ID, Face ID, Windows Hello) or hardware keys (YubiKey). Works cross-device via QR scan.</p>
    <h3>How It Works</h3>
    <ol style="padding-left:1.25rem">
      <li>User registers a passkey at <code>/oxalis/passkeys/enroll</code> — browser generates a key pair, public key stored in DB.</li>
      <li>On login, user enters their email → Oxalis issues a WebAuthn challenge → browser signs it with the private key → signature verified server-side.</li>
      <li>No shared secret ever leaves the device.</li>
    </ol>
    <h3>Key settings</h3>
    <div class="code-wrap"><button class="copy-btn" onclick="copyPre(this)"><i class="bi bi-clipboard"></i></button>
    <pre class="language-bash"><code>OXALIS_RP_ID=myapp.com
OXALIS_ORIGINS=https://myapp.com
OXALIS_REQUIRE_ATTESTATION=false   # true = enterprise devices only</code></pre></div>
    <div class="alert-box alert-info">
      <i class="bi bi-info-circle-fill"></i>
      <div><strong>Local dev:</strong> <code>OXALIS_RP_ID=localhost</code> and <code>OXALIS_ORIGINS=http://localhost:8000</code>. The RP ID must exactly match the browser hostname — no port, no scheme.</div>
    </div>
    <h3>Conditional UI autofill</h3>
    <p>On the login page, the email field has <code>autocomplete="username webauthn"</code>. When the page loads, Oxalis silently starts a discoverable credential request in the background. When the user clicks the email field, their browser shows registered passkeys in the autofill dropdown — no button click, no email required.</p>
    <p>Works on Chrome 108+, Edge 108+, Safari 16+. Falls back gracefully to the regular button flow on older browsers.</p>
  </section>

  <!-- EMAIL OTP -->
  <section id="email-otp">
    <h2>Email OTP</h2>
    <p>User enters their email → receives a 6-digit code → enters it on the verify page. Codes are bcrypt-hashed in the database and expire in 5 minutes.</p>
    <div class="alert-box alert-tip">
      <i class="bi bi-lightbulb-fill"></i>
      <div><strong>Local dev:</strong> The code is shown directly on-screen in a yellow dev hint box — no email needed to test.</div>
    </div>
    <div class="code-wrap"><button class="copy-btn" onclick="copyPre(this)"><i class="bi bi-clipboard"></i></button>
    <pre class="language-bash"><code>OXALIS_ENABLE_EMAIL_OTP=true
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com</code></pre></div>
  </section>

  <!-- MAGIC LINK -->
  <section id="magic-link">
    <h2>Magic Link</h2>
    <p>User enters email → receives a signed link → clicks it → logged in. Links are single-use, expire in 15 minutes, and are invalidated on use.</p>
    <div class="alert-box alert-tip">
      <i class="bi bi-lightbulb-fill"></i>
      <div><strong>Local dev:</strong> The magic link URL is shown on-screen as a clickable link — no email setup needed to test.</div>
    </div>
  </section>

  <!-- TOTP -->
  <section id="totp">
    <h2>Authenticator App (TOTP)</h2>
    <p>Users set up TOTP once at <code>/oxalis/totp/setup</code> by scanning a QR code with Google Authenticator, Authy, or 1Password. After setup, <strong>every login via any method</strong> automatically requires the TOTP code as a second factor — no configuration needed.</p>
    <h3>Setup flow</h3>
    <ol style="padding-left:1.25rem">
      <li>User navigates to <code>/oxalis/totp/setup</code> (must be logged in).</li>
      <li>Scans QR code with their authenticator app.</li>
      <li>Confirms by entering the first 6-digit code.</li>
      <li>Shown 8 one-time recovery codes to save.</li>
    </ol>
    <p>From now on, after any successful auth method, <code>LoginHandler</code> checks TOTP and redirects to the TOTP verify page if enabled.</p>
    <h3>Recovery codes</h3>
    <p>8 random codes generated at TOTP setup. Each is bcrypt-hashed and consumed on use. Users can regenerate them at <code>/oxalis/account</code>.</p>
    <h3>Remember this device</h3>
    <p>On the TOTP verify page, users can tick <strong>"Remember this device for 30 days"</strong>. A signed token is set as an <code>httpOnly</code> cookie. On the next login, if the cookie matches a valid record in <code>oxalis_totp_trusted_devices</code>, TOTP is skipped entirely for that device.</p>
    <div class="code-wrap"><button class="copy-btn" onclick="copyPre(this)"><i class="bi bi-clipboard"></i></button>
    <pre class="language-bash"><code>OXALIS_TOTP_TRUST=true       # enable/disable the feature (default true)
OXALIS_TOTP_TRUST_DAYS=30    # how long the device is trusted</code></pre></div>
  </section>

  <!-- PASSWORD -->
  <section id="password">
    <h2>Password Login</h2>
    <p>Classic email + password at <code>/oxalis/password</code>. Includes a forgot-password flow with signed reset links and DB-level lockout (not cache-based, so MongoDB-safe).</p>
  </section>

  <!-- SOCIAL -->
  <section id="social">
    <h2>Social Login</h2>
    <p>Google and GitHub OAuth via Laravel Socialite. A new user who signs in with OAuth gets an account created automatically. Existing users are matched by email.</p>
    <div class="code-wrap"><button class="copy-btn" onclick="copyPre(this)"><i class="bi bi-clipboard"></i></button>
    <pre class="language-bash"><code>OXALIS_ENABLE_SOCIAL=true
OXALIS_GOOGLE_ENABLED=true
GOOGLE_CLIENT_ID=your-client-id
GOOGLE_CLIENT_SECRET=your-secret
GOOGLE_REDIRECT_URI=https://myapp.com/oxalis/social/google/callback</code></pre></div>
  </section>

  <!-- SMART DISPATCH -->
  <section id="smart-dispatch">
    <h2>Smart Dispatch (One-Field Auth)</h2>
    <p>User visits <code>/oxalis/start</code> and types their email. Oxalis decides the best available sign-in method: if they have a passkey → passkey; if TOTP is enabled → TOTP; otherwise magic link or OTP. Enable with:</p>
    <div class="code-wrap"><button class="copy-btn" onclick="copyPre(this)"><i class="bi bi-clipboard"></i></button>
    <pre class="language-bash"><code>OXALIS_SMART_DISPATCH=true</code></pre></div>
  </section>

  <!-- ROUTES -->
  <section id="routes">
    <h2>Routes Reference</h2>
    <p>All routes are prefixed with <code>/oxalis/</code> by default. Change the prefix via <code>config('oxalis.routes.prefix')</code>.</p>
    <table>
      <thead><tr><th>URL</th><th>Name</th><th>Notes</th></tr></thead>
      <tbody>
        <tr><td>GET /oxalis/login</td><td><code>oxalis.login</code></td><td>Main login page</td></tr>
        <tr><td>GET /oxalis/register</td><td><code>oxalis.register</code></td><td>Step 1: name + email</td></tr>
        <tr><td>GET /oxalis/register/verify</td><td><code>oxalis.register.verify.show</code></td><td>Step 2: verify OTP</td></tr>
        <tr><td>GET /oxalis/register/password</td><td><code>oxalis.register.password.show</code></td><td>Step 3: set password</td></tr>
        <tr><td>GET /oxalis/start</td><td><code>oxalis.dispatch</code></td><td>Smart Dispatch</td></tr>
        <tr><td>POST /oxalis/otp/send</td><td><code>oxalis.otp.send</code></td><td>Send OTP email (5/5min)</td></tr>
        <tr><td>POST /oxalis/otp/verify</td><td><code>oxalis.otp.verify</code></td><td>Verify OTP code</td></tr>
        <tr><td>POST /oxalis/magic-link/send</td><td><code>oxalis.magic-link.send</code></td><td>Send magic link (5/5min)</td></tr>
        <tr><td>GET /oxalis/magic-link/verify/{token}</td><td><code>oxalis.magic-link.verify</code></td><td>Magic link target</td></tr>
        <tr><td>POST /oxalis/passkeys/login/begin</td><td><code>oxalis.passkeys.login.begin</code></td><td>WebAuthn assertion start</td></tr>
        <tr><td>POST /oxalis/passkeys/login/finish</td><td><code>oxalis.passkeys.login.finish</code></td><td>WebAuthn assertion verify</td></tr>
        <tr><td>GET /oxalis/password</td><td><code>oxalis.password.login.show</code></td><td>Password login form</td></tr>
        <tr><td>GET /oxalis/forgot-password</td><td><code>oxalis.password.forgot</code></td><td>Request reset link</td></tr>
        <tr><td>GET /oxalis/totp/verify</td><td><code>oxalis.totp.verify.show</code></td><td>TOTP 2FA step</td></tr>
        <tr><td>GET /oxalis/totp/setup</td><td><code>oxalis.totp.setup</code></td><td>QR setup <span style="color:var(--ox)">(auth)</span></td></tr>
        <tr><td>GET /oxalis/passkeys/enroll</td><td><code>oxalis.passkeys.enroll</code></td><td>Register passkey <span style="color:var(--ox)">(auth)</span></td></tr>
        <tr><td>GET /oxalis/account</td><td><code>oxalis.account</code></td><td>Account settings <span style="color:var(--ox)">(auth)</span></td></tr>
        <tr><td>GET /oxalis/step-up</td><td><code>oxalis.step-up.prompt</code></td><td>Step-up prompt <span style="color:var(--ox)">(auth)</span></td></tr>
        <tr><td>POST /oxalis/logout</td><td><code>oxalis.logout</code></td><td>Sign out <span style="color:var(--ox)">(auth)</span></td></tr>
        <tr><td>GET /oxalis/stats</td><td><code>oxalis.stats</code></td><td>Usage dashboard <span style="color:var(--ox)">(auth)</span></td></tr>
        <tr><td>GET /oxalis/account/email</td><td><code>oxalis.account.email.show</code></td><td>Change email <span style="color:var(--ox)">(auth)</span></td></tr>
        <tr><td>POST /oxalis/account/delete</td><td><code>oxalis.account.delete</code></td><td>Delete account <span style="color:var(--ox)">(auth)</span></td></tr>
        <tr><td>POST /oxalis/passkeys/login/autofill-begin</td><td><code>oxalis.passkeys.login.autofill</code></td><td>Conditional passkey begin</td></tr>
        <tr><td>GET /oxalis/admin</td><td><code>oxalis.admin</code></td><td>Admin dashboard <span style="color:#ef4444">(admin)</span></td></tr>
        <tr><td>GET /oxalis/admin/setup</td><td><code>oxalis.admin.setup</code></td><td>First-time admin setup</td></tr>
        <tr><td>GET /oxalis/admin/login</td><td><code>oxalis.admin.login</code></td><td>Admin login</td></tr>
        <tr><td>GET /oxalis/admin/change-password</td><td><code>oxalis.admin.password</code></td><td>Change admin password <span style="color:#ef4444">(admin)</span></td></tr>
        <tr><td>GET /oxalis/docs</td><td><code>oxalis.docs</code></td><td>This page</td></tr>
        <tr><td>GET /login</td><td><code>login</code></td><td>Redirects → /oxalis/login</td></tr>
        <tr><td>GET /register</td><td>—</td><td>Redirects → /oxalis/register</td></tr>
      </tbody>
    </table>
  </section>

  <!-- COMPONENTS -->
  <section id="components">
    <h2>Blade Components</h2>
    <h3>&lt;x-oxalis-user-menu /&gt;</h3>
    <p>Drop a fully-styled user avatar dropdown into any layout. Shows user initials, name, a link to account settings, and a sign-out button.</p>
    <div class="code-wrap"><button class="copy-btn" onclick="copyPre(this)"><i class="bi bi-clipboard"></i></button>
    <pre class="language-html"><code>&lt;!-- In your app layout's nav --&gt;
@auth
  &lt;x-oxalis-user-menu /&gt;
@endauth</code></pre></div>
    <p style="margin-top:.75rem">Requires Bootstrap 5.3 JS in the layout — uses standard <code>data-bs-toggle="dropdown"</code>.</p>
    <h3>Account settings page</h3>
    <p>Link users here — shows only the auth methods the developer enabled in config.</p>
    <div class="code-wrap"><button class="copy-btn" onclick="copyPre(this)"><i class="bi bi-clipboard"></i></button>
    <pre class="language-html"><code>&lt;a href="{{ route('oxalis.account') }}"&gt;Account settings&lt;/a&gt;</code></pre></div>
    <h3>Step-up auth middleware</h3>
    <p>Protect sensitive routes by requiring fresh TOTP or passkey verification:</p>
    <div class="code-wrap"><button class="copy-btn" onclick="copyPre(this)"><i class="bi bi-clipboard"></i></button>
    <pre class="language-php"><code>Route::middleware(['auth', 'oxalis.step-up'])->group(function () {
    Route::get('/billing', BillingController::class);
});</code></pre></div>
  </section>

  <!-- EVENTS -->
  <section id="events">
    <h2>Events</h2>
    <p>Oxalis fires standard Laravel events you can listen to in your <code>EventServiceProvider</code> or via <code>#[AsEventListener]</code>.</p>
    <table>
      <thead><tr><th>Event class</th><th>Fired when</th><th>Payload</th></tr></thead>
      <tbody>
        <tr><td><code>Oxalis\Events\UserLoggedIn</code></td><td>Every successful login</td><td><code>$user, $method, $ip, $userAgent</code></td></tr>
        <tr><td><code>Oxalis\Events\TotpEnabled</code></td><td>User confirms TOTP setup</td><td><code>$user</code></td></tr>
      </tbody>
    </table>
    <div class="code-wrap"><button class="copy-btn" onclick="copyPre(this)"><i class="bi bi-clipboard"></i></button>
    <pre class="language-php"><code>use Oxalis\Events\UserLoggedIn;

class EventServiceProvider
{
    protected $listen = [
        UserLoggedIn::class => [
            SendWelcomeEmail::class,
            TrackLoginAnalytics::class,
        ],
    ];
}</code></pre></div>
  </section>

  <!-- SECURITY -->
  <section id="security">
    <h2>Security Features</h2>
    <div class="feature-grid">
      <div class="feature-card"><div class="feature-icon"><i class="bi bi-shield-fill-check"></i></div><h4>IP Rate Limiting</h4><p>All POST auth endpoints rate-limited per IP via a DB-backed <code>Lockout</code> model — works with MongoDB, no cache needed.</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="bi bi-lock-fill"></i></div><h4>TOTP 2FA Enforcement</h4><p>Once enabled, <em>every</em> login method requires the TOTP step. No bypass possible.</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="bi bi-fingerprint"></i></div><h4>Passkey Sessions</h4><p>After passkey login, the credential ID is stamped on the session for audit by <code>ValidatePasskeySession</code>.</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="bi bi-key-fill"></i></div><h4>Recovery Codes</h4><p>8 one-time recovery codes, each bcrypt-hashed. Consumed on use, regeneratable from account settings.</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="bi bi-bell-fill"></i></div><h4>Login Notifications</h4><p>Optional email on every new sign-in, showing method, IP, and device. Opt-in via <code>OXALIS_LOGIN_NOTIFICATION=true</code>.</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="bi bi-graph-up"></i></div><h4>Auth Event Log</h4><p>Every attempt recorded in <code>oxalis_auth_events</code> with method, IP, and outcome. View at <code>/oxalis/stats</code>.</p></div>
    </div>
  </section>

  <!-- CUSTOMIZING -->
  <section id="customizing">
    <h2>Customizing</h2>
    <h3>Override views</h3>
    <div class="code-wrap"><button class="copy-btn" onclick="copyPre(this)"><i class="bi bi-clipboard"></i></button>
    <pre class="language-bash"><code>php artisan vendor:publish --tag=oxalis-views</code></pre></div>
    <p>All Blade views are copied to <code>resources/views/vendor/oxalis/</code>. Laravel always loads published views first, so your edits take effect immediately.</p>
    <h3>Override config</h3>
    <div class="code-wrap"><button class="copy-btn" onclick="copyPre(this)"><i class="bi bi-clipboard"></i></button>
    <pre class="language-bash"><code>php artisan vendor:publish --tag=oxalis-config</code></pre></div>
    <h3>Theme / colours</h3>
    <p>Edit <code>resources/views/vendor/oxalis/layouts/oxalis.blade.php</code> — change the CSS variables at the top:</p>
    <div class="code-wrap"><button class="copy-btn" onclick="copyPre(this)"><i class="bi bi-clipboard"></i></button>
    <pre class="language-css"><code>:root {
  --ox: #5c6ac4;      /* primary accent */
  --ox-dk: #4959b8;   /* hover/dark variant */
  --ox-sf: rgba(92,106,196,.12);  /* soft fill */
  --ox-r: 14px;       /* border radius */
}</code></pre></div>
    <p>The account page and email templates follow the same <code>--ox</code> variable — change it once, updates everywhere.</p>
  </section>

  <!-- ACCOUNT FEATURES -->
  <section id="account-features">
    <h2>Account features</h2>
    <p>All user-facing account management lives at <code>/oxalis/account</code>. Features shown are filtered by which methods the developer enabled in config.</p>

    <h3>Email change</h3>
    <p>A <strong>Change</strong> badge next to the email address opens a 2-step flow: enter new email → receive OTP → verify → email updated with <code>email_verified_at = now()</code>. Works the same as registration verification.</p>

    <h3>Account deletion</h3>
    <p>A collapsible <strong>Danger zone</strong> at the bottom of the account page. User must type their email to confirm. Deletes all Oxalis records for that user (passkeys, TOTP, OTP challenges, magic links, auth events, trusted devices). Configurable:</p>
    <div class="code-wrap"><button class="copy-btn" onclick="copyPre(this)"><i class="bi bi-clipboard"></i></button>
    <pre class="language-bash"><code>OXALIS_ACCOUNT_DELETION=true       # show/hide the danger zone
OXALIS_DELETE_USER_MODEL=true      # also delete the user row from your users table</code></pre></div>
    <div class="alert-box alert-warn">
      <i class="bi bi-exclamation-triangle-fill"></i>
      <div>Set <code>OXALIS_DELETE_USER_MODEL=false</code> if you want to keep the user record but wipe all authentication data — useful for soft-deletes or compliance workflows.</div>
    </div>
  </section>

  <!-- USER COMMAND -->
  <section id="user-command">
    <h2>oxalis:user command</h2>
    <p>Manage users directly from the CLI — useful for seeding, CI pipelines, or support tasks.</p>
    <div class="code-wrap"><button class="copy-btn" onclick="copyPre(this)"><i class="bi bi-clipboard"></i></button>
    <pre class="language-bash"><code>php artisan oxalis:user              # interactive menu
php artisan oxalis:user create       # create a user (prompts name, email, password)
php artisan oxalis:user list         # table of all users
php artisan oxalis:user delete       # delete user + all their Oxalis data</code></pre></div>
    <p><code>oxalis:user delete</code> removes the user from the users table AND all associated Oxalis records — passkeys, TOTP secrets, OTP challenges, magic links, auth events, and trusted devices.</p>
  </section>

  <!-- ADMIN SETUP -->
  <section id="admin-setup">
    <h2>Admin panel — Setup &amp; login</h2>
    <p>The admin panel is disabled by default. Enable it with:</p>
    <div class="code-wrap"><button class="copy-btn" onclick="copyPre(this)"><i class="bi bi-clipboard"></i></button>
    <pre class="language-bash"><code>OXALIS_ADMIN=true
OXALIS_ADMIN_GATE=admin    # optional — ties to a Laravel Gate for extra protection</code></pre></div>

    <h3>First visit — setup wizard</h3>
    <p>The first time anyone visits <code>/oxalis/admin</code>, they are redirected to a one-time setup page at <code>/oxalis/admin/setup</code>:</p>
    <ol style="padding-left:1.25rem">
      <li>Set an admin password — the form enforces 12+ chars, uppercase, number, and symbol via a live strength meter. The submit button stays disabled until all requirements pass.</li>
      <li>Optionally enable TOTP — scan a QR code with Google Authenticator or Authy, confirm with the first 6-digit code. Adds a second factor to every future admin login.</li>
    </ol>
    <p>Once set up, the setup page is permanently inaccessible.</p>

    <h3>Login</h3>
    <p>Every visit to <code>/oxalis/admin</code> requires signing in at <code>/oxalis/admin/login</code>. The login page shows the last login time and IP so you can immediately spot unauthorized access. After <strong>5 failed attempts</strong>, the IP is locked out for <strong>30 minutes</strong>.</p>
    <p>After sign-in, the session lasts <strong>2 hours of inactivity</strong> (sliding window — refreshed on every request). The sidebar has a manual <strong>Sign out</strong> button.</p>

    <h3>Change password</h3>
    <p><code>/oxalis/admin/change-password</code> requires the current password to confirm. On success, <code>session_version</code> rotates — <strong>all other active admin sessions are immediately invalidated</strong>. Only the session that made the change stays valid.</p>
  </section>

  <!-- ADMIN DASHBOARD -->
  <section id="admin-dashboard">
    <h2>Admin panel — Dashboard</h2>
    <p>The dashboard at <code>/oxalis/admin</code> gives a full view of authentication activity across your app.</p>
    <table>
      <thead><tr><th>Feature</th><th>Details</th></tr></thead>
      <tbody>
        <tr><td>KPI cards</td><td>Total users, passkeys, successful logins, failed attempts, IPs currently locked</td></tr>
        <tr><td>User table</td><td>Paginated (50/page), search by name or email, filter by "Has passkey" or "TOTP on"</td></tr>
        <tr><td>Per-user stats</td><td>Passkey count, TOTP status, last sign-in time — cached for 5 minutes (safe for 100k+ users)</td></tr>
        <tr><td>Auth events</td><td>15 most recent attempts with method, IP, and pass/fail indicator</td></tr>
        <tr><td>Lockouts</td><td>Active lockouts with attempt count and expiry time</td></tr>
      </tbody>
    </table>
  </section>

  <!-- ADMIN SECURITY -->
  <section id="admin-security">
    <h2>Admin panel — Security model</h2>
    <div class="feature-grid">
      <div class="feature-card"><div class="feature-icon"><i class="bi bi-key-fill"></i></div><h4>Separate credentials</h4><p>Admin password is stored in <code>oxalis_admin_credentials</code> — completely separate from your app's user table. No user account needed.</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="bi bi-phone-fill"></i></div><h4>Optional TOTP</h4><p>Enable 2FA for admin login during setup. Every sign-in requires password + authenticator code.</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="bi bi-shield-fill-exclamation"></i></div><h4>Rate limiting</h4><p>5 failed login attempts → 30-minute IP lockout. Uses the same DB-backed Lockout model as user auth.</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="bi bi-clock-history"></i></div><h4>Session timeout</h4><p>2-hour sliding window. Every request refreshes the timer. Idle for 2 hours → auto-logout.</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="bi bi-arrow-repeat"></i></div><h4>Session versioning</h4><p>Changing the admin password rotates <code>session_version</code> and invalidates all other active sessions instantly.</p></div>
      <div class="feature-card"><div class="feature-icon"><i class="bi bi-geo-alt-fill"></i></div><h4>Login audit</h4><p>Every successful login records timestamp and IP. Shown on the login page and in the sidebar so you always see the last access.</p></div>
    </div>
  </section>

  <!-- APP STATS -->
  <section id="app-stats">
    <h2>User Stats Dashboard</h2>
    <p>Visit <code>/oxalis/stats</code> while logged in to see real-time auth analytics — total sign-ins, method breakdown, success rate, and recent activity.</p>
    <p>The data comes from <code>oxalis_auth_events</code>. You can also query it directly:</p>
    <div class="code-wrap"><button class="copy-btn" onclick="copyPre(this)"><i class="bi bi-clipboard"></i></button>
    <pre class="language-php"><code>use Oxalis\Models\AuthEvent;

// Total logins this month
AuthEvent::where('event', 'login')
    ->where('status', 'success')
    ->whereMonth('created_at', now()->month)
    ->count();

// Most used method
AuthEvent::selectRaw('method, count(*) as total')
    ->groupBy('method')
    ->orderByDesc('total')
    ->get();</code></pre></div>
  </section>


</div><!-- /.doc-content -->
</main>

<button id="menu-toggle" onclick="document.getElementById('sidebar').classList.toggle('open')">
  <i class="bi bi-list"></i>
</button>

<script src="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/components/prism-core.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/plugins/autoloader/prism-autoloader.min.js"></script>
<script>
// Copy helpers
function copy(id,btn){navigator.clipboard.writeText(document.getElementById(id).textContent);btn.innerHTML='<i class="bi bi-check2"></i> Copied';setTimeout(()=>btn.innerHTML='<i class="bi bi-clipboard"></i> Copy',1800);}
function copyPre(btn){var p=btn.nextElementSibling;if(p)navigator.clipboard.writeText(p.textContent.trim());btn.innerHTML='<i class="bi bi-check2"></i>';setTimeout(()=>btn.innerHTML='<i class="bi bi-clipboard"></i>',1800);}

// Theme toggle
var icon=document.getElementById('theme-icon');
function applyTheme(t){
  document.documentElement.setAttribute('data-theme',t);
  localStorage.setItem('ox-docs-theme',t);
  document.getElementById('prism-theme').href=t==='dark'
    ?'https://cdn.jsdelivr.net/npm/prismjs@1.29.0/themes/prism-tomorrow.min.css'
    :'https://cdn.jsdelivr.net/npm/prismjs@1.29.0/themes/prism.min.css';
  icon.className=t==='dark'?'bi bi-moon-fill':'bi bi-sun-fill';
}
// Set icon on load
(function(){var t=document.documentElement.getAttribute('data-theme')||'light';icon.className=t==='dark'?'bi bi-moon-fill':'bi bi-sun-fill';})();
document.getElementById('theme-btn').addEventListener('click',function(){
  var cur=document.documentElement.getAttribute('data-theme')||'light';
  applyTheme(cur==='dark'?'light':'dark');
});

// Active nav on scroll
var sections=document.querySelectorAll('section[id]');
var links=document.querySelectorAll('.nav-link');
window.addEventListener('scroll',function(){
  var pos=window.scrollY+120;
  sections.forEach(function(s){
    if(s.offsetTop<=pos&&s.offsetTop+s.offsetHeight>pos){
      links.forEach(function(l){l.classList.remove('active');});
      var a=document.querySelector('.nav-link[href="#'+s.id+'"]');
      if(a)a.classList.add('active');
    }
  });
},{passive:true});

// Sidebar search
document.getElementById('doc-search').addEventListener('input',function(){
  var q=this.value.toLowerCase();
  links.forEach(function(l){l.style.display=(!q||l.textContent.toLowerCase().includes(q))?'':'none';});
});

// Smooth scroll + close sidebar on mobile
document.querySelectorAll('.nav-link').forEach(function(a){
  a.addEventListener('click',function(e){
    var id=this.getAttribute('href').slice(1);
    var el=document.getElementById(id);
    if(el){e.preventDefault();el.scrollIntoView({behavior:'smooth'});}
    document.getElementById('sidebar').classList.remove('open');
  });
});
</script>
</body>
</html>
