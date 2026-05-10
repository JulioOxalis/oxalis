# Publishing oxalis to GitHub and Packagist

This guide covers getting oxalis onto GitHub, listed on Packagist, and installable by anyone with `composer require julio/oxalis`.

---

## Step 1 — Create the GitHub repository

1. Go to [github.com/new](https://github.com/new)
2. Repository name: `oxalis`
3. Visibility: **Public** (required for free Packagist)
4. Do **not** initialise with README (you already have one)
5. Click **Create repository**

---

## Step 2 — Push the package code

The package lives at `packages/oxalis/` in its own standalone repository.

Open a terminal in a **new location** (not inside your main project):

```bash
# Windows
cd C:\laragon\www
mkdir oxalis-repo
cd oxalis-repo

# Copy the package files
xcopy /E /I ".\packages\oxalis\*" "."

# Initialise git
git init
git add .
git commit -m "feat: initial release v1.0.0"

# Connect to GitHub (replace YOUR_USERNAME)
git remote add origin https://github.com/YOUR_USERNAME/oxalis.git
git branch -M main
git push -u origin main
```

---

## Step 3 — Tag a release

Packagist reads versions from git tags. Create one:

```bash
git tag v1.0.0
git push origin v1.0.0
```

After this, Packagist will expose version `1.0.0` and users can `composer require julio/oxalis:^1.0`.

---

## Step 4 — Register on Packagist

1. Go to [packagist.org](https://packagist.org)
2. Click **Sign in** → connect with GitHub
3. Click **Submit** (top right)
4. Paste your GitHub repo URL: `https://github.com/YOUR_USERNAME/oxalis`
5. Click **Check** → **Submit**

Packagist reads your `composer.json`, confirms the package name is `julio/oxalis`, and publishes it.

---

## Step 5 — Set up auto-update webhook

Without this, Packagist won't know when you release new versions.

**On Packagist:**
1. Go to your package page
2. Click the profile icon → **Profile** → find your API token, or go directly to the package and look for the webhook URL

**On GitHub:**
1. Go to your `oxalis` repository → **Settings** → **Webhooks** → **Add webhook**
2. Payload URL: `https://packagist.org/api/github?username=YOUR_PACKAGIST_USERNAME`
3. Content type: `application/json`
4. Secret: your Packagist API token
5. Events: **Just the push event**
6. Click **Add webhook**

Now every `git push` + `git tag` automatically updates Packagist.

---

## Releasing a new version

```bash
# Make your changes, commit them
git add .
git commit -m "fix: enforce TOTP on all login paths"

# Tag the new version
git tag v1.0.1
git push origin main
git push origin v1.0.1
```

Packagist updates within seconds. Users who run `composer update julio/oxalis` get the new version.

---

## Versioning guide

Follow [Semantic Versioning](https://semver.org):

| Change | Version bump | Example |
|---|---|---|
| Bug fix, no API change | Patch | `1.0.0` → `1.0.1` |
| New feature, backwards compatible | Minor | `1.0.0` → `1.1.0` |
| Breaking change | Major | `1.0.0` → `2.0.0` |

---

## After publishing — what users do

```bash
composer require julio/oxalis
php artisan oxalis:install
```

That's it. The interactive wizard handles everything else.

---

## Keeping the local development version linked

In your project, the path repository still works:

```json
"repositories": [
    {
        "type": "path",
        "url": "./packages/oxalis",
        "options": { "symlink": true }
    }
]
```

When you're ready to use the published Packagist version instead, remove that block and run:

```bash
composer require julio/oxalis:^1.0
```

---

## Optional — add a license file

Packagist and GitHub both show the license. Create `packages/oxalis/LICENSE`:

```
MIT License

Copyright (c) 2025 YOUR_NAME

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```
