{{-- resources/views/pages/auth/loginDevBiswakarma.blade.php --}}
{{-- ⚠️  DEV / TESTING ONLY — This view is never served in production.         --}}
{{-- The route that renders it aborts with 404 when APP_ENV is not local/testing --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Dev Login · {{ config('app.name', 'App') }}</title>
  <meta name="csrf-token" content="{{ csrf_token() }}"/>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="{{ asset('/assets/css/common/main.css') }}"/>

  <style>
    /* ── base ──────────────────────────────────────────────────── */
    html, body { min-height: 100%; }

    body.dev-body {
      min-height: 100vh;
      margin: 0;
      overflow-x: hidden;
      background:
        radial-gradient(circle at top left,  rgba(234,179,8,.08),  transparent 32%),
        radial-gradient(circle at bottom right, rgba(234,179,8,.06), transparent 28%),
        linear-gradient(180deg, #fffdf0 0%, #fff 55%, #fffef5 100%);
      color: var(--text-color);
      font-family: var(--font-sans);
    }

    /* ── dev banner ────────────────────────────────────────────── */
    .dev-banner {
      position: fixed;
      top: 0; left: 0; right: 0;
      z-index: 9999;
      background: repeating-linear-gradient(
        -45deg,
        #fef08a 0px, #fef08a 12px,
        #fbbf24 12px, #fbbf24 24px
      );
      color: #78350f;
      font-weight: 800;
      font-size: .78rem;
      letter-spacing: .06em;
      text-transform: uppercase;
      text-align: center;
      padding: 5px 8px;
      border-bottom: 2px solid #f59e0b;
      user-select: none;
    }

    .dev-banner i { margin-right: 6px; }

    /* ── page layout ───────────────────────────────────────────── */
    .dev-page {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 72px 16px 32px;  /* top-pad clears the fixed banner */
      position: relative;
      z-index: 1;
    }

    .dev-shell {
      width: 100%;
      max-width: 480px;
      margin: auto;
    }

    /* ── brand block ───────────────────────────────────────────── */
    .dev-brand-block {
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
      margin-bottom: 20px;
    }

    .dev-brand-logo {
      width: 64px;
      height: 64px;
      border-radius: 18px;
      display: grid;
      place-items: center;
      background: #fff;
      border: 1px solid rgba(234,179,8,.22);
      box-shadow: 0 12px 28px rgba(234,179,8,.14);
      margin-bottom: 12px;
      overflow: hidden;
    }

    .dev-brand-logo img { height: 44px; max-width: 100%; }

    .dev-title {
      font-family: var(--font-head, inherit);
      font-weight: 700;
      font-size: clamp(1.45rem, 3vw, 1.9rem);
      color: var(--ink, #111);
      margin: 0 0 4px;
    }

    .dev-subtitle {
      color: var(--muted-color, #6b7280);
      font-size: .93rem;
    }

    /* ── env pill ──────────────────────────────────────────────── */
    .dev-env-pill {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 5px 12px;
      border-radius: 999px;
      font-size: .76rem;
      font-weight: 700;
      letter-spacing: .04em;
      background: #fef9c3;
      color: #92400e;
      border: 1px solid #fde68a;
      margin-bottom: 18px;
    }

    /* ── card ──────────────────────────────────────────────────── */
    .dev-card {
      background: var(--surface, #fff);
      border: 2px dashed #fcd34d;
      border-radius: 20px;
      padding: 28px 24px 22px;
      box-shadow: 0 8px 28px rgba(234,179,8,.10);
      position: relative;
    }

    .dev-card-badge {
      position: absolute;
      top: -14px;
      left: 50%;
      transform: translateX(-50%);
      background: #fbbf24;
      color: #78350f;
      font-weight: 800;
      font-size: .72rem;
      letter-spacing: .07em;
      text-transform: uppercase;
      padding: 4px 14px;
      border-radius: 999px;
      white-space: nowrap;
      box-shadow: 0 2px 8px rgba(234,179,8,.30);
    }

    /* ── form controls ─────────────────────────────────────────── */
    .dev-label {
      font-weight: 600;
      color: var(--ink, #111);
      margin-bottom: 5px;
    }

    .dev-control {
      height: 48px;
      border-radius: 12px;
      border: 1px solid var(--line-strong, #e5e7eb);
      padding-right: 48px;
      font-size: 1rem;
      transition: border-color .15s, box-shadow .15s;
    }

    .dev-control:focus {
      border-color: #fbbf24;
      box-shadow: 0 0 0 3px rgba(251,191,36,.20);
      outline: none;
    }

    .dev-control::placeholder { color: #aab2c2; }

    .dev-input-wrap { position: relative; }

    .dev-eye {
      position: absolute;
      top: 50%; right: 10px;
      transform: translateY(-50%);
      width: 36px; height: 36px;
      border: none; background: transparent;
      color: #8892a6;
      display: grid; place-items: center;
      cursor: pointer; border-radius: 8px;
    }

    /* ── submit button ─────────────────────────────────────────── */
    .dev-btn {
      width: 100%;
      height: 48px;
      border: none;
      border-radius: 12px;
      font-weight: 700;
      color: #78350f;
      background: linear-gradient(180deg, #fde68a, #fbbf24);
      box-shadow: 0 8px 20px rgba(251,191,36,.32);
      transition: filter .15s, transform .15s, box-shadow .15s;
      font-size: 1rem;
    }

    .dev-btn:hover:not(:disabled) {
      filter: brightness(.97);
      transform: translateY(-1px);
    }

    .dev-btn:disabled {
      opacity: .65;
      cursor: not-allowed;
      transform: none;
      box-shadow: none;
    }

    /* ── footer note ───────────────────────────────────────────── */
    .dev-footer-note {
      text-align: center;
      font-size: .78rem;
      color: var(--muted-color, #9ca3af);
      margin-top: 18px;
      line-height: 1.5;
    }

    .dev-footer-note a {
      color: var(--primary-color, #9E363A);
      text-decoration: none;
      font-weight: 600;
    }

    .dev-footer-note a:hover { text-decoration: underline; }
  </style>
</head>

<body class="dev-body">

  {{-- ⚠️ Fixed hazard-stripe dev banner ─────────────────────────── --}}
  <div class="dev-banner" aria-hidden="true">
    <i class="fa-solid fa-triangle-exclamation"></i>
    Development / Testing Environment — Password Login Only — Not for Production Use
    <i class="fa-solid fa-triangle-exclamation"></i>
  </div>

  <div class="dev-page">
    <div class="dev-shell">

      {{-- Brand ──────────────────────────────────────────────────── --}}
      <div class="dev-brand-block">
        <div class="dev-brand-logo">
          <img src="{{ asset('/assets/media/images/web/logo.png') }}"
               alt="{{ config('app.name', 'App') }}">
        </div>
        <h1 class="dev-title">{{ config('app.name', 'App') }}</h1>
        <p class="dev-subtitle">Developer sign-in — bypass OTP &amp; captcha</p>

        <span class="dev-env-pill">
          <i class="fa-solid fa-flask-vial"></i>
          ENV: {{ strtoupper(app()->environment()) }}
        </span>
      </div>

      {{-- Login card ──────────────────────────────────────────────── --}}
      <div class="dev-card">
        <span class="dev-card-badge">
          <i class="fa-solid fa-code me-1"></i> Dev Only · /login-dev-biswakarma
        </span>

        <div id="dev_alert" class="alert d-none mb-3" role="alert"></div>

        <form id="dev_form" novalidate>
          @csrf

          {{-- Email ──────────────────────────────────────────────── --}}
          <div class="mb-3">
            <label class="dev-label form-label" for="dev_email">Email</label>
            <input id="dev_email"
                   type="email"
                   class="dev-control form-control"
                   name="email"
                   placeholder="you@example.com"
                   autocomplete="username"
                   required>
          </div>

          {{-- Password ────────────────────────────────────────────── --}}
          <div class="mb-4">
            <label class="dev-label form-label" for="dev_pw">Password</label>
            <div class="dev-input-wrap">
              <input id="dev_pw"
                     type="password"
                     class="dev-control form-control"
                     name="password"
                     placeholder="Enter your password"
                     autocomplete="current-password"
                     required>
              <button type="button" class="dev-eye" id="dev_togglePw"
                      aria-label="Toggle password visibility">
                <i class="fa-regular fa-eye-slash" aria-hidden="true"></i>
              </button>
            </div>
          </div>

          {{-- Remember me ──────────────────────────────────────────── --}}
          <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" id="dev_keep" name="remember">
            <label class="form-check-label" for="dev_keep">Keep me logged in</label>
          </div>

          <button class="dev-btn" id="dev_btn" type="submit">
            <i class="fa-solid fa-right-to-bracket me-2"></i> Sign In (Dev)
          </button>
        </form>
      </div>

      {{-- Footer note ─────────────────────────────────────────────── --}}
      <p class="dev-footer-note">
        <i class="fa-solid fa-lock me-1"></i>
        This page is only accessible when
        <code>APP_ENV</code> is <code>local</code> or <code>testing</code>.<br>
        For the production login flow, visit
        <a href="/">the main login page</a>.
      </p>

    </div>
  </div>

<script>
(function () {
  'use strict';

  const LOGIN_API = '/api/auth/login';
  const CHECK_API = '/api/auth/check';

  const csrf   = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
  const form   = document.getElementById('dev_form');
  const emailIn = document.getElementById('dev_email');
  const pwIn   = document.getElementById('dev_pw');
  const keepCb = document.getElementById('dev_keep');
  const btn    = document.getElementById('dev_btn');
  const alert  = document.getElementById('dev_alert');
  const toggle = document.getElementById('dev_togglePw');

  /* ── helpers ──────────────────────────────────────────────────── */
  function showAlert(kind, msg) {
    alert.className = 'alert mb-3 ' + (
      kind === 'error'   ? 'alert-danger'  :
      kind === 'warn'    ? 'alert-warning' : 'alert-success'
    );
    alert.textContent = msg;
    alert.classList.remove('d-none');
  }

  function clearAlert() {
    alert.classList.add('d-none');
    alert.textContent = '';
  }

  function setBusy(busy) {
    btn.disabled = !!busy;
    btn.innerHTML = busy
      ? '<i class="fa-solid fa-spinner fa-spin me-2"></i> Signing in…'
      : '<i class="fa-solid fa-right-to-bracket me-2"></i> Sign In (Dev)';
  }

  /* ── token storage (mirrors main login.blade.php) ─────────────── */
  const authStore = {
    set(token, role, keep) {
      sessionStorage.setItem('token', token);
      sessionStorage.setItem('role', role);
      if (keep) {
        localStorage.setItem('token', token);
        localStorage.setItem('role', role);
      } else {
        localStorage.removeItem('token');
        localStorage.removeItem('role');
      }
    },
    getLocal() {
      return {
        token: localStorage.getItem('token'),
        role:  localStorage.getItem('role'),
      };
    },
    clear() {
      ['token', 'role'].forEach(k => {
        sessionStorage.removeItem(k);
        localStorage.removeItem(k);
      });
    },
  };

  function rolePath() { return '/dashboard'; }

  /* ── auto-login if a valid token already exists ───────────────── */
  async function tryAutoLogin() {
    const { token, role } = authStore.getLocal();
    if (!token) return;
    try {
      const res  = await fetch(CHECK_API, { headers: { Authorization: 'Bearer ' + token } });
      const data = await res.json().catch(() => ({}));
      if (res.ok && data?.user) {
        window.location.replace(rolePath());
      } else {
        authStore.clear();
      }
    } catch (_) {}
  }

  /* ── password toggle ──────────────────────────────────────────── */
  toggle?.addEventListener('click', () => {
    const show = pwIn.type === 'password';
    pwIn.type = show ? 'text' : 'password';
    toggle.innerHTML = show
      ? '<i class="fa-regular fa-eye" aria-hidden="true"></i>'
      : '<i class="fa-regular fa-eye-slash" aria-hidden="true"></i>';
  });

  /* ── form submit ──────────────────────────────────────────────── */
  form?.addEventListener('submit', async (e) => {
    e.preventDefault();
    clearAlert();

    const email    = (emailIn.value ?? '').trim().toLowerCase();
    const password = pwIn.value ?? '';
    const remember = keepCb?.checked ?? false;

    if (!email) {
      showAlert('error', 'Please enter your email.');
      emailIn.focus();
      return;
    }

    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      showAlert('error', 'Please enter a valid email address.');
      emailIn.focus();
      return;
    }

    if (!password) {
      showAlert('error', 'Please enter your password.');
      pwIn.focus();
      return;
    }

    setBusy(true);

    try {
      const res = await fetch(LOGIN_API, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrf,
        },
        body: JSON.stringify({ email, password, remember }),
      });

      const data = await res.json().catch(() => ({}));

      if (!res.ok) {
        const msg =
          data?.message ||
          data?.error ||
          (data?.errors ? Object.values(data.errors).flat().join(', ') : null) ||
          'Invalid credentials.';
        showAlert('error', msg);
        setBusy(false);
        return;
      }

      const token = data?.access_token ?? data?.token ?? '';
      const role  = (data?.user?.role ?? localStorage.getItem('role') ?? 'student').toLowerCase();

      if (!token) {
        showAlert('error', 'Server did not return an access token.');
        setBusy(false);
        return;
      }

      authStore.set(token, role, remember);
      showAlert('success', 'Login successful — redirecting…');

      setTimeout(() => window.location.assign(rolePath()), 500);

    } catch (err) {
      showAlert('error', 'Network error. Please try again.');
      setBusy(false);
    }
  });

  /* ── init ─────────────────────────────────────────────────────── */
  document.addEventListener('DOMContentLoaded', tryAutoLogin);

})();
</script>

</body>
</html>
