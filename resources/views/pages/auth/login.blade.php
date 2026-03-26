{{-- resources/views/auth/login.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Login — {{ config('app.name', 'Hallienz Home Builder') }}</title>

  <meta name="csrf-token" content="{{ csrf_token() }}"/>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="{{ asset('/assets/css/common/main.css') }}"/>

  <style>
    html, body{
      min-height:100%;
      height:auto;
    }

    body.lx-auth-body{
      min-height:100vh;
      overflow-x:hidden;
      overflow-y:auto;
      background:var(--bg-body);
      color:var(--text-color);
      font-family:var(--font-sans);
    }

    .lx-grid{
      min-height:100vh;
      display:grid;
      grid-template-columns:minmax(420px,560px) 1fr;
      align-items:stretch;
    }

    @media (max-width: 992px){
      .lx-grid{
        min-height:auto;
        grid-template-columns:1fr;
      }
    }

    .lx-left{
      min-height:100vh;
      display:flex;
      flex-direction:column;
      justify-content:center;
      align-items:center;
      padding:clamp(20px,5vw,48px);
      position:relative;
      isolation:isolate;
    }

    .lx-left::before,
    .lx-left::after{
      content:"";
      position:absolute;
      z-index:0;
      pointer-events:none;
      border-radius:50%;
      filter: blur(26px);
      opacity:.25;
      display:none;
    }

    .lx-left::before{
      width:320px;
      height:320px;
      left:-80px;
      top:10%;
      background: radial-gradient(closest-side, var(--primary-light), transparent 70%);
      animation: lx-floatA 9s ease-in-out infinite;
    }

    .lx-left::after{
      width:280px;
      height:280px;
      right:-60px;
      bottom:14%;
      background: radial-gradient(closest-side, var(--accent-color), transparent 70%);
      animation: lx-floatB 11s ease-in-out infinite;
    }

    @media (max-width: 992px){
      .lx-left{
        min-height:auto;
        justify-content:flex-start;
        padding:18px 14px 28px;
      }

      .lx-left::before,
      .lx-left::after{
        display:block;
      }
    }

    .lx-brand{
      display:grid;
      place-items:center;
      margin-bottom:18px;
      position:relative;
      z-index:1;
    }

    .lx-brand img{
      height:60px;
      max-width:100%;
    }

    .lx-title{
      font-family:var(--font-head);
      font-weight:700;
      color:var(--ink);
      text-align:center;
      font-size:clamp(1.6rem, 2.6vw, 2.2rem);
      margin:.35rem 0 .25rem;
      position:relative;
      z-index:1;
    }

    .lx-sub{
      text-align:center;
      color:var(--muted-color);
      margin-bottom:18px;
      position:relative;
      z-index:1;
      max-width:420px;
    }

    .lx-card{
      position:relative;
      z-index:1;
      background:var(--surface);
      border:1px solid var(--line-strong);
      border-radius:18px;
      padding:22px 18px 18px;
      box-shadow:var(--shadow-2);
      width:100%;
      max-width:430px;
      overflow:visible;
    }

    @media (max-width: 576px){
      .lx-card{
        padding:18px 14px 16px;
        border-radius:16px;
      }

      .lx-title{
        font-size:1.45rem;
      }

      .lx-sub{
        font-size:.94rem;
        margin-bottom:14px;
      }
    }

    .lx-card::before,
    .lx-card::after{
      content:"";
      position:absolute;
      border-radius:50%;
      filter: blur(18px);
      opacity:.25;
      pointer-events:none;
    }

    .lx-card::before{
      width:160px;
      height:160px;
      left:-40px;
      top:-40px;
      background: radial-gradient(closest-side, var(--accent-color), transparent 65%);
      animation: lx-orbitA 12s linear infinite;
    }

    .lx-card::after{
      width:140px;
      height:140px;
      right:-30px;
      bottom:-30px;
      background: radial-gradient(closest-side, var(--primary-color), transparent 65%);
      animation: lx-orbitB 14s linear infinite reverse;
    }

    .lx-float-chip{
      position:absolute;
      top:12px;
      right:12px;
      z-index:1;
      padding:6px 10px;
      border-radius:999px;
      font-size:.78rem;
      background:rgba(0,0,0,.04);
      color:var(--text-color);
      border:1px solid var(--line-strong);
      backdrop-filter: blur(4px);
      animation: lx-chip 7s ease-in-out infinite;
    }

    .lx-label{
      font-weight:600;
      color:var(--ink);
    }

    .lx-input-wrap{
      position:relative;
    }

    .lx-control{
      height:48px;
      border-radius:12px;
      padding-right:48px;
    }

    .lx-control::placeholder{
      color:#aab2c2;
    }

    .lx-control:disabled{
      background:#f6f7fb;
      cursor:not-allowed;
      opacity:.9;
    }

    .lx-eye{
      position:absolute;
      top:50%;
      right:10px;
      transform:translateY(-50%);
      width:36px;
      height:36px;
      border:none;
      background:transparent;
      color:#8892a6;
      display:grid;
      place-items:center;
      cursor:pointer;
      border-radius:8px;
    }

    .lx-eye:focus-visible{
      outline:none;
      box-shadow:var(--ring);
    }

    .lx-eye:disabled{
      opacity:.45;
      cursor:not-allowed;
    }

    .lx-row{
      display:flex;
      justify-content:space-between;
      align-items:center;
      gap:12px;
    }

    @media (max-width: 420px){
      .lx-row{
        align-items:flex-start;
        flex-direction:column;
      }
    }

    .lx-login,
    .lx-otp-btn{
      width:100%;
      height:48px;
      border:none;
      border-radius:12px;
      font-weight:700;
      color:#fff;
      background:linear-gradient(
        180deg,
        color-mix(in oklab, var(--primary-color) 92%, #fff 8%),
        var(--primary-color)
      );
      box-shadow:0 10px 22px rgba(158,54,58,.26);
      transition:var(--transition);
    }

    .lx-login:hover,
    .lx-otp-btn:hover{
      filter:brightness(.98);
      transform:translateY(-1px);
    }

    .lx-login:disabled,
    .lx-otp-btn:disabled{
      opacity:.65;
      cursor:not-allowed;
      transform:none;
      box-shadow:none;
    }

    .lx-otp-wrap{
      border:1px dashed var(--line-strong);
      background:color-mix(in srgb, var(--surface) 92%, var(--primary-light) 8%);
      border-radius:14px;
      padding:14px;
      margin-bottom:14px;
      position:relative;
      z-index:1;
    }

    .lx-otp-row{
      display:grid;
      grid-template-columns:1fr;
      gap:10px;
    }

    .lx-meta{
      font-size:.86rem;
      color:var(--muted-color);
      margin-top:6px;
    }

    .lx-badge{
      display:inline-flex;
      align-items:center;
      gap:6px;
      padding:6px 10px;
      border-radius:999px;
      font-size:.78rem;
      font-weight:700;
      border:1px solid transparent;
    }

    .lx-badge.pending{
      color:#9a6700;
      background:#fff7d6;
      border-color:#f0d98c;
    }

    .lx-badge.success{
      color:#0a6b33;
      background:#dcfce7;
      border-color:#86efac;
    }

    .lx-badge.info{
      color:#0f4c81;
      background:#dbeafe;
      border-color:#93c5fd;
    }

    .lx-captcha{
      margin-bottom:14px;
      position:relative;
      z-index:1;
      border:1px solid var(--line-strong);
      border-radius:14px;
      padding:12px;
      background:linear-gradient(
        180deg,
        color-mix(in srgb, var(--surface) 92%, white 8%),
        color-mix(in srgb, var(--surface) 84%, var(--primary-light) 16%)
      );
      box-shadow: inset 0 1px 0 rgba(255,255,255,.75);
    }

    .lx-captcha-head{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:8px;
      margin-bottom:8px;
      flex-wrap:wrap;
    }

    .lx-captcha-title{
      display:flex;
      align-items:center;
      gap:8px;
      font-weight:700;
      color:var(--ink);
      font-size:.95rem;
    }

    .lx-captcha-title i{
      color:var(--primary-color);
    }

    .lx-captcha-tip{
      font-size:.78rem;
      color:var(--muted-color);
      font-weight:600;
    }

    .lx-captcha-row{
      --bs-gutter-x:.5rem;
      --bs-gutter-y:.5rem;
    }

    .lx-captcha-canvas-wrap{
      width:100%;
      height:48px;
      min-height:48px;
      border:1px solid var(--line-strong);
      border-radius:12px;
      background:#fff;
      display:flex;
      align-items:center;
      justify-content:center;
      overflow:hidden;
      box-shadow: inset 0 1px 0 rgba(255,255,255,.75);
      cursor:pointer;
    }

    .lx-captcha-canvas{
      display:block;
      width:100%;
      height:48px;
      max-width:100%;
      cursor:pointer;
      user-select:none;
    }

    .lx-captcha-input{
      height:48px;
      border-radius:12px;
      font-weight:600;
      font-size:1rem;
      letter-spacing:.08em;
      text-transform:uppercase;
      padding:0 14px;
      background:#fff;
      border:1px solid var(--line-strong);
    }

    .lx-captcha-input::placeholder{
      letter-spacing:normal;
      text-transform:none;
      font-weight:400;
      font-size:1rem;
    }

    .lx-captcha-meta{
      margin-top:10px;
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:10px;
      flex-wrap:wrap;
    }

    .lx-captcha-status{
      display:inline-flex;
      align-items:center;
      gap:6px;
      padding:6px 10px;
      border-radius:999px;
      font-size:.78rem;
      font-weight:700;
      border:1px solid transparent;
    }

    .lx-captcha-status.info{
      color:#0f4c81;
      background:#dbeafe;
      border-color:#93c5fd;
    }

    .lx-captcha-status.success{
      color:#0a6b33;
      background:#dcfce7;
      border-color:#86efac;
    }

    .lx-captcha-status.error{
      color:#991b1b;
      background:#fee2e2;
      border-color:#fca5a5;
    }

    .lx-captcha-hint{
      font-size:.82rem;
      color:var(--muted-color);
    }

    @media (max-width: 767.98px){
      .lx-captcha-canvas-wrap{
        width:50%;
      }
      .lx-captcha-input{
        width:100%;
      }
    }

    .d-none-force{
      display:none !important;
    }

    .lx-right{
      position:relative;
      min-height:100vh;
      display:grid;
      place-items:center;
      background:
        radial-gradient(120% 100% at 10% 10%, rgba(201,75,80,.16) 0%, rgba(7,13,42,0) 55%),
        linear-gradient(180deg,#061220,#08142a);
      padding:clamp(24px, 4vw, 60px);
      isolation:isolate;
      overflow:hidden;
    }

    @media (max-width: 992px){
      .lx-right{
        display:none;
      }
    }

    .lx-arc{
      position:absolute;
      inset:-18% -10% auto auto;
      width:120%;
      height:140%;
      background:radial-gradient(110% 110% at 80% 20%,
        rgba(201,75,80,.18) 0%,
        rgba(158,54,58,.16) 35%,
        rgba(7,13,42,0) 62%
      );
      border-bottom-left-radius:48% 44%;
      pointer-events:none;
      animation: lx-drift 16s ease-in-out infinite;
    }

    .lx-ring{
      position:absolute;
      inset:auto -120px -80px auto;
      width:420px;
      height:420px;
      border-radius:50%;
      background:
        radial-gradient(closest-side, rgba(255,255,255,.18), rgba(255,255,255,0) 70%),
        conic-gradient(from 0deg,
          rgba(158,54,58,.25),
          rgba(0,210,196,.20),
          rgba(158,54,58,.25)
        );
      filter:blur(18px);
      opacity:.18;
      pointer-events:none;
      animation: lx-spin 24s linear infinite;
    }

    .lx-hero{
      position:relative;
      width:min(680px, 96%);
      aspect-ratio:3/4;
      animation: lx-pop .7s ease-out both;
    }

    .lx-hero-frame{
      position:relative;
      width:100%;
      height:100%;
      padding:20px;
      border-radius:36px;
      background:linear-gradient(180deg, rgba(255,255,255,.08), rgba(255,255,255,.02));
      box-shadow:0 24px 54px rgba(0,0,0,.35), 0 0 0 1px rgba(255,255,255,.06) inset;
      transition:transform .25s ease, box-shadow .25s ease;
      will-change:transform;
    }

    .lx-hero-img{
      width:100%;
      height:100%;
      border-radius:24px;
      overflow:hidden;
      position:relative;
      box-shadow:0 18px 40px rgba(0,0,0,.35);
    }

    .lx-hero-img img{
      width:100%;
      height:100%;
      object-fit:cover;
      display:block;
      transform:translateZ(0);
      animation: lx-zoom 26s ease-in-out infinite alternate;
      will-change:transform;
    }

    .lx-particles{
      position:absolute;
      inset:0;
      pointer-events:none;
      opacity:.28;
      background:
        radial-gradient(#ffffff 1px, transparent 2px) 0 0/22px 22px,
        radial-gradient(#ffffff 1px, transparent 2px) 11px 11px/22px 22px;
      mix-blend-mode: overlay;
      animation: lx-twinkle 12s linear infinite;
    }

    .lx-obj{
      position:absolute;
      z-index:3;
      opacity:.9;
      filter:drop-shadow(0 8px 18px rgba(0,0,0,.28));
      user-select:none;
      pointer-events:none;
    }

    .lx-books{
      top:clamp(18px, 3vw, 36px);
      left:clamp(12px, 2vw, 28px);
      display:grid;
      gap:6px;
    }

    .lx-book{
      width:110px;
      height:22px;
      border-radius:5px;
      background:linear-gradient(90deg, var(--primary-color), var(--accent-color));
      transform:rotate(-6deg);
    }

    .lx-book:nth-child(2){
      width:124px;
      height:24px;
      background:linear-gradient(90deg, #0ea5e9, #22c55e);
      transform:rotate(-4deg) translateX(8px);
    }

    .lx-book:nth-child(3){
      width:132px;
      height:24px;
      background:linear-gradient(90deg, #f97316, #facc15);
      transform:rotate(-2deg) translateX(14px);
    }

    .lx-cup{
      right:clamp(16px, 3vw, 36px);
      bottom:clamp(18px, 3vw, 36px);
      width:90px;
      height:110px;
    }

    .lx-cup-body{
      position:absolute;
      left:0;
      bottom:0;
      width:90px;
      height:64px;
      border-radius:12px 12px 18px 18px;
      background:linear-gradient(180deg, #1b2a55, #0f1a3a);
      border:1px solid rgba(255,255,255,.10);
    }

    .lx-pencil{
      position:absolute;
      bottom:40px;
      width:10px;
      height:78px;
      border-radius:6px;
      background:linear-gradient(180deg, #facc15, #eab308);
      box-shadow:inset 0 0 0 1px rgba(0,0,0,.08);
      transform-origin:bottom center;
      animation: lx-sway 5s ease-in-out infinite;
    }

    .lx-pencil:nth-child(2){
      left:24px;
      transform:rotate(-8deg);
      background:linear-gradient(180deg, #22c55e, #16a34a);
      animation-delay:.6s;
    }

    .lx-pencil:nth-child(3){
      left:46px;
      transform:rotate(6deg);
      background:linear-gradient(180deg, #3b82f6, #2563eb);
      animation-delay:1.2s;
    }

    .lx-pencil:nth-child(4){
      left:64px;
      transform:rotate(-2deg);
      background:linear-gradient(180deg, var(--accent-color), var(--primary-color));
      animation-delay:1.8s;
    }

    @keyframes lx-pop{ from{opacity:0; transform:translateY(10px) scale(.98);} to{opacity:1; transform:none;} }
    @keyframes lx-zoom{ from{transform:scale(1);} to{transform:scale(1.06);} }
    @keyframes lx-drift{ 0%,100%{transform:translate3d(0,0,0);} 50%{transform:translate3d(-2%,2%,0);} }
    @keyframes lx-spin{ 0%{transform:rotate(0deg);} 100%{transform:rotate(360deg);} }
    @keyframes lx-sway{ 0%,100%{transform:rotate(0deg);} 50%{transform:rotate(4deg);} }
    @keyframes lx-floatA{ 0%,100%{transform:translate(0,0);} 50%{transform:translate(10px, -14px);} }
    @keyframes lx-floatB{ 0%,100%{transform:translate(0,0);} 50%{transform:translate(-12px, 10px);} }
    @keyframes lx-orbitA{ 0%{transform:translate(0,0);} 50%{transform:translate(6px, -6px);} 100%{transform:translate(0,0);} }
    @keyframes lx-orbitB{ 0%{transform:translate(0,0);} 50%{transform:translate(-6px, 6px);} 100%{transform:translate(0,0);} }
    @keyframes lx-chip{ 0%,100%{transform:translateY(0);} 50%{transform:translateY(-6px);} }
    @keyframes lx-twinkle{ 0%{opacity:.22;} 50%{opacity:.34;} 100%{opacity:.22;} }
  </style>
</head>
<body class="lx-auth-body">

<div class="lx-grid">
  <section class="lx-left">
    <div class="lx-brand">
      <img src="{{ asset('/assets/media/images/web/logo.png') }}" alt="{{ config('app.name', 'Hallienz Home Builder') }}">
    </div>

    <h1 class="lx-title">Sign in to {{ config('app.name', 'Hallienz Home Builder') }}</h1>
    <p class="lx-sub">
      Use your institute credentials to manage departments, faculty profiles, notices, and more —
      all from a single dashboard.
    </p>

    <form class="lx-card" id="lx_form" action="/login" method="post" novalidate>
      <span class="lx-float-chip">
        <i class="fa-solid fa-shield-halved me-1"></i> Secure token-based access
      </span>
      @csrf

      <div id="lx_alert" class="alert d-none mb-3" role="alert"></div>

      <div class="mb-2">
        <label class="lx-label form-label" for="lx_id_or_email">Institute Email</label>
        <div class="lx-input-wrap">
          <input id="lx_id_or_email" type="email" class="lx-control form-control" name="identifier"
                 placeholder="you@Hallienz.edu.in" autocomplete="username" required>
        </div>
        <div class="lx-meta" id="lx_email_meta">Enter your email first. Password will unlock only after email verification.</div>
      </div>

      <div class="mb-3 d-none-force" id="lx_badge_wrap">
        <span id="lx_verify_badge" class="lx-badge pending">
          <i class="fa-solid fa-circle-exclamation"></i>
          <span>Status</span>
        </span>
      </div>

      <div id="lx_otp_wrap" class="lx-otp-wrap d-none-force">
        <div class="d-flex justify-content-between align-items-center gap-2 mb-2 flex-wrap">
          <div class="fw-semibold text-dark">
            <i class="fa-solid fa-envelope-circle-check me-1"></i> Verify your email
          </div>
          <button type="button" class="lx-otp-btn" id="lx_sendOtpBtn" style="max-width:170px; height:40px;" disabled>
            <i class="fa-solid fa-paper-plane me-1"></i> Send OTP
          </button>
        </div>

        <div class="lx-otp-row">
          <input id="lx_otp" type="text" class="lx-control form-control" maxlength="6" inputmode="numeric"
                 placeholder="Enter 6-digit OTP" disabled>
        </div>

        <div class="lx-meta" id="lx_otp_meta">After OTP is sent, enter all 6 digits. Verification will happen automatically.</div>
      </div>

      <div class="mb-3">
        <label class="lx-label form-label" for="lx_pw">Password</label>
        <div class="lx-input-wrap">
          <input id="lx_pw" type="password" class="lx-control form-control" name="password"
                 placeholder="Verify email first to unlock password"
                 minlength="8" autocomplete="current-password" required disabled>
          <button type="button" class="lx-eye" id="lx_togglePw" aria-label="Toggle password visibility" disabled>
            <i class="fa-regular fa-eye-slash" aria-hidden="true"></i>
          </button>
        </div>
      </div>

      <div class="lx-captcha" id="lx_captcha_wrap">
        <div class="lx-captcha-head">
          <div class="lx-captcha-title">
            <i class="fa-solid fa-shield-check"></i>
            <span>Security check</span>
          </div>
          <div class="lx-captcha-tip">Click the captcha image to refresh</div>
        </div>

        <div class="row lx-captcha-row align-items-stretch">
          <div class="col-12 col-md-4">
            <div class="lx-captcha-canvas-wrap" id="lx_captcha_canvas_wrap" title="Click to refresh captcha">
              <canvas id="lx_captcha_canvas" class="lx-captcha-canvas" aria-label="Captcha image"></canvas>
            </div>
          </div>

          <div class="col-12 col-md-8">
            <input id="lx_captcha_input"
                   type="text"
                   class="form-control lx-captcha-input"
                   maxlength="6"
                   autocomplete="off"
                   autocapitalize="characters"
                   spellcheck="false"
                   placeholder="Enter code">
          </div>
        </div>

        <div class="lx-captcha-meta">
          <span id="lx_captcha_status" class="lx-captcha-status info" aria-live="polite">
            <i class="fa-solid fa-circle-info"></i>
            <span>Captcha required</span>
          </span>
          <span id="lx_captcha_hint" class="lx-captcha-hint" aria-live="polite">
            Type the characters shown above to continue.
          </span>
        </div>
      </div>

      <div class="lx-row mb-3">
        <div class="form-check m-0">
          <input class="form-check-input" type="checkbox" id="lx_keep" disabled>
          <label class="form-check-label" for="lx_keep">Keep me logged in</label>
        </div>
        {{-- <a class="text-decoration-none" href="/forgot-password">Forgot password?</a> --}}
      </div>

      <button class="lx-login" id="lx_btn" type="submit" disabled>
        <span class="me-2"><i class="fa-solid fa-lock"></i></span> Verify email to continue
      </button>
    </form>
  </section>

  <aside class="lx-right" id="lx_visual" aria-hidden="true">
    <span class="lx-arc"></span>
    <span class="lx-ring"></span>

    <div class="lx-obj lx-books">
      <div class="lx-book"></div>
      <div class="lx-book"></div>
      <div class="lx-book"></div>
    </div>

    <div class="lx-obj lx-cup">
      <div class="lx-cup-body"></div>
      <div class="lx-pencil" style="left:8px;"></div>
      <div class="lx-pencil"></div>
      <div class="lx-pencil"></div>
      <div class="lx-pencil"></div>
    </div>

    <div class="lx-hero" id="lx_hero">
      <div class="lx-hero-frame">
        <div class="lx-hero-img">
          <img src="{{ asset('/assets/media/images/web/login_hero.png') }}" alt="Indian boys studying together">
          <div class="lx-particles"></div>
        </div>
      </div>
    </div>
  </aside>
</div>

<script>
(function () {
  const LOGIN_API       = "/api/auth/login";
  const CHECK_API       = "/api/auth/check";
  const EMAIL_CHECK_API = "/api/check-email-verification";
  const SEND_OTP_API    = "/api/send-email-otp";
  const VERIFY_OTP_API  = "/api/verify-email-otp";

  const OTP_RESEND_SECONDS = 60;
  const CAPTCHA_LENGTH = 6;

  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

  const form       = document.getElementById('lx_form');
  const emailIn    = document.getElementById('lx_id_or_email');
  const pwIn       = document.getElementById('lx_pw');
  const keepCb     = document.getElementById('lx_keep');
  const btn        = document.getElementById('lx_btn');
  const alertEl    = document.getElementById('lx_alert');
  const toggle     = document.getElementById('lx_togglePw');

  const badgeWrap  = document.getElementById('lx_badge_wrap');
  const badge      = document.getElementById('lx_verify_badge');

  const otpWrap    = document.getElementById('lx_otp_wrap');
  const sendOtpBtn = document.getElementById('lx_sendOtpBtn');
  const otpIn      = document.getElementById('lx_otp');
  const otpMeta    = document.getElementById('lx_otp_meta');
  const emailMeta  = document.getElementById('lx_email_meta');

  const captchaCanvas      = document.getElementById('lx_captcha_canvas');
  const captchaCanvasWrap  = document.getElementById('lx_captcha_canvas_wrap');
  const captchaInput       = document.getElementById('lx_captcha_input');
  const captchaStatus      = document.getElementById('lx_captcha_status');
  const captchaHint        = document.getElementById('lx_captcha_hint');

  const state = {
    emailChecked: false,
    emailExists: false,
    emailVerified: false,
    otpSent: false,
    checkingEmail: false,
    sendingOtp: false,
    verifyingOtp: false,
    lastCheckedEmail: '',
    checkRequestId: 0,
    resendSeconds: 0,
    resendTimer: null,
    captchaText: '',
    captchaSolved: false,
    loginBusy: false,
  };

  function authHeaders() {
    return {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrf
    };
  }

  function normalizeEmail(v) {
    return (v || '').trim().toLowerCase();
  }

  function validEmail(v) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);
  }

  function showAlert(kind, msg) {
    alertEl.classList.remove('d-none', 'alert-danger', 'alert-success', 'alert-warning');
    alertEl.classList.add('alert', kind === 'error' ? 'alert-danger' : (kind === 'warn' ? 'alert-warning' : 'alert-success'));
    alertEl.textContent = msg;
  }

  function clearAlert() {
    alertEl.classList.add('d-none');
    alertEl.textContent = '';
  }

  function showBadge(type, text) {
    badgeWrap.classList.remove('d-none-force');
    badge.className = 'lx-badge ' + type;
    badge.innerHTML = '';

    const icon = document.createElement('i');
    icon.className =
      type === 'success' ? 'fa-solid fa-circle-check' :
      type === 'info' ? 'fa-solid fa-circle-info' :
      'fa-solid fa-circle-exclamation';

    const span = document.createElement('span');
    span.textContent = text;

    badge.appendChild(icon);
    badge.appendChild(span);
  }

  function hideBadge() {
    badgeWrap.classList.add('d-none-force');
  }

  function setCaptchaStatus(type, text) {
    captchaStatus.className = 'lx-captcha-status ' + type;
    captchaStatus.innerHTML =
      type === 'success'
        ? '<i class="fa-solid fa-circle-check"></i><span>' + text + '</span>'
        : type === 'error'
          ? '<i class="fa-solid fa-circle-xmark"></i><span>' + text + '</span>'
          : '<i class="fa-solid fa-circle-info"></i><span>' + text + '</span>';
  }

  function setBusy(busy) {
    state.loginBusy = !!busy;
    btn.disabled = state.loginBusy || !state.emailVerified || !state.captchaSolved;

    if (state.loginBusy) {
      btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Signing you in…';
      return;
    }

    if (!state.emailVerified) {
      btn.innerHTML = '<span class="me-2"><i class="fa-solid fa-lock"></i></span> Verify email to continue';
      return;
    }

    if (!state.captchaSolved) {
      btn.innerHTML = '<span class="me-2"><i class="fa-solid fa-shield-check"></i></span> Solve captcha to continue';
      return;
    }

    btn.innerHTML = '<span class="me-2"><i class="fa-solid fa-right-to-bracket"></i></span> Login';
  }

  function lockPasswordSection(resetPassword = true) {
    state.emailVerified = false;
    pwIn.disabled = true;
    toggle.disabled = true;
    keepCb.disabled = true;
    pwIn.placeholder = 'Verify email first to unlock password';
    if (resetPassword) pwIn.value = '';
    setBusy(false);
  }

  function unlockPasswordSection() {
    state.emailVerified = true;
    pwIn.disabled = false;
    toggle.disabled = false;
    keepCb.disabled = false;
    pwIn.placeholder = 'Enter at least 8+ characters';
    setBusy(false);
  }

  function stopOtpCooldown() {
    if (state.resendTimer) {
      clearInterval(state.resendTimer);
      state.resendTimer = null;
    }
    state.resendSeconds = 0;
  }

  function refreshOtpButton() {
    if (state.emailVerified) {
      sendOtpBtn.disabled = true;
      sendOtpBtn.innerHTML = '<i class="fa-solid fa-circle-check me-1"></i> Verified';
      return;
    }

    if (!state.emailChecked || !state.emailExists || state.checkingEmail || state.sendingOtp) {
      sendOtpBtn.disabled = true;
      sendOtpBtn.innerHTML = '<i class="fa-solid fa-paper-plane me-1"></i> Send OTP';
      return;
    }

    if (state.resendSeconds > 0) {
      sendOtpBtn.disabled = true;
      sendOtpBtn.innerHTML = `<i class="fa-regular fa-clock me-1"></i> Resend in ${state.resendSeconds}s`;
      return;
    }

    sendOtpBtn.disabled = false;
    sendOtpBtn.innerHTML = state.otpSent
      ? '<i class="fa-solid fa-rotate-right me-1"></i> Resend OTP'
      : '<i class="fa-solid fa-paper-plane me-1"></i> Send OTP';
  }

  function startOtpCooldown(seconds = OTP_RESEND_SECONDS) {
    stopOtpCooldown();
    state.resendSeconds = Math.max(0, Number(seconds) || 0);
    refreshOtpButton();

    if (state.resendSeconds <= 0) return;

    state.resendTimer = setInterval(() => {
      state.resendSeconds -= 1;
      if (state.resendSeconds <= 0) {
        stopOtpCooldown();
      }
      refreshOtpButton();
    }, 1000);
  }

  function resetOtpSection(full = true) {
    state.otpSent = false;
    state.verifyingOtp = false;
    stopOtpCooldown();

    otpIn.value = '';
    otpIn.disabled = true;
    otpIn.placeholder = 'Enter 6-digit OTP';
    otpMeta.textContent = 'After OTP is sent, enter all 6 digits. Verification will happen automatically.';
    refreshOtpButton();

    if (full) {
      otpWrap.classList.add('d-none-force');
    }
  }

  function showOtpSection() {
    otpWrap.classList.remove('d-none-force');
    refreshOtpButton();
  }

  function generateCaptchaText(length = CAPTCHA_LENGTH) {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    let out = '';
    for (let i = 0; i < length; i += 1) {
      out += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return out;
  }

  function drawCaptcha(text) {
    if (!captchaCanvas) return;

    const wrapWidth = Math.max(120, Math.floor(captchaCanvasWrap?.clientWidth || 140));
    const width = wrapWidth;
    const height = 48;
    const dpr = Math.max(1, window.devicePixelRatio || 1);

    captchaCanvas.width = width * dpr;
    captchaCanvas.height = height * dpr;
    captchaCanvas.style.width = '100%';
    captchaCanvas.style.height = height + 'px';

    const ctx = captchaCanvas.getContext('2d');
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    ctx.clearRect(0, 0, width, height);

    const bg = ctx.createLinearGradient(0, 0, width, height);
    bg.addColorStop(0, '#fff9fa');
    bg.addColorStop(1, '#fbe7e8');
    ctx.fillStyle = bg;
    ctx.fillRect(0, 0, width, height);

    ctx.strokeStyle = 'rgba(158,54,58,.18)';
    ctx.lineWidth = 1;
    ctx.strokeRect(0.5, 0.5, width - 1, height - 1);

    for (let i = 0; i < 5; i += 1) {
      ctx.beginPath();
      ctx.moveTo(Math.random() * width, Math.random() * height);
      ctx.bezierCurveTo(
        Math.random() * width, Math.random() * height,
        Math.random() * width, Math.random() * height,
        Math.random() * width, Math.random() * height
      );
      ctx.strokeStyle = i % 2 === 0 ? 'rgba(158,54,58,.14)' : 'rgba(201,75,80,.12)';
      ctx.lineWidth = 1;
      ctx.stroke();
    }

    for (let i = 0; i < 14; i += 1) {
      ctx.beginPath();
      ctx.arc(Math.random() * width, Math.random() * height, 1 + Math.random(), 0, Math.PI * 2);
      ctx.fillStyle = i % 2 === 0 ? 'rgba(107,37,40,.10)' : 'rgba(201,75,80,.10)';
      ctx.fill();
    }

    const chars = String(text || '').split('');
    const startX = 10;
    const usableWidth = Math.max(80, width - 20);
    const gap = usableWidth / Math.max(chars.length, 1);
    const fontSize = Math.max(18, Math.min(22, Math.floor(width / 7.2)));

    chars.forEach((ch, idx) => {
      const x = startX + idx * gap + 2;
      const y = 31 + (idx % 2 === 0 ? -2 : 2);
      const angle = (Math.random() * 0.34) - 0.17;

      ctx.save();
      ctx.translate(x, y);
      ctx.rotate(angle);
      ctx.font = `700 ${fontSize}px Inter, Arial, sans-serif`;
      ctx.fillStyle = idx % 2 === 0 ? '#6B2528' : '#9E363A';
      ctx.fillText(ch, 0, 0);
      ctx.restore();
    });
  }

  function resetCaptcha(message = 'Type the characters shown above to continue.') {
    state.captchaSolved = false;
    state.captchaText = generateCaptchaText();
    captchaInput.value = '';
    drawCaptcha(state.captchaText);
    setCaptchaStatus('info', 'Captcha required');
    captchaHint.textContent = message;
    setBusy(false);
  }

  function validateCaptchaInput() {
    const value = (captchaInput.value || '').replace(/\s+/g, '').toUpperCase().slice(0, CAPTCHA_LENGTH);
    captchaInput.value = value;

    if (!value) {
      state.captchaSolved = false;
      setCaptchaStatus('info', 'Captcha required');
      captchaHint.textContent = 'Type the characters shown above to continue.';
      setBusy(false);
      return;
    }

    if (value === state.captchaText) {
      state.captchaSolved = true;
      setCaptchaStatus('success', 'Captcha verified');
      captchaHint.textContent = state.emailVerified
        ? 'Security check passed. You can now log in.'
        : 'Security check passed. Finish email verification to continue.';
      setBusy(false);
      return;
    }

    state.captchaSolved = false;
    if (value.length >= CAPTCHA_LENGTH) {
      setCaptchaStatus('error', 'Captcha does not match');
      captchaHint.textContent = 'Please try again by clicking the captcha image.';
    } else {
      setCaptchaStatus('info', 'Keep typing');
      captchaHint.textContent = 'Match all characters exactly as shown.';
    }
    setBusy(false);
  }

  function resetVerificationStateFromEmailEdit() {
    state.emailChecked = false;
    state.emailExists = false;
    state.emailVerified = false;
    state.otpSent = false;
    state.checkingEmail = false;
    state.sendingOtp = false;
    state.verifyingOtp = false;
    state.lastCheckedEmail = '';

    stopOtpCooldown();
    lockPasswordSection(true);
    resetOtpSection(true);
    clearAlert();
    hideBadge();

    const email = normalizeEmail(emailIn.value);
    if (!email) {
      emailMeta.textContent = 'Enter your email first. Password will unlock only after email verification.';
    } else if (!validEmail(email)) {
      emailMeta.textContent = 'Enter a valid registered email to continue.';
    } else {
      emailMeta.textContent = 'Checking email verification status...';
    }
  }

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
    clear() {
      sessionStorage.removeItem('token');
      sessionStorage.removeItem('role');
      localStorage.removeItem('token');
      localStorage.removeItem('role');
    },
    getLocal() {
      return {
        token: localStorage.getItem('token'),
        role: localStorage.getItem('role')
      };
    }
  };

  function rolePath(role) {
    const r = (role || '').toString().trim().toLowerCase();
    if (!r) return '/dashboard';
    return '/dashboard';
  }

  toggle?.addEventListener('click', () => {
    if (toggle.disabled || pwIn.disabled) return;

    const show = pwIn.type === 'password';
    pwIn.type = show ? 'text' : 'password';
    toggle.innerHTML = show
      ? '<i class="fa-regular fa-eye" aria-hidden="true"></i>'
      : '<i class="fa-regular fa-eye-slash" aria-hidden="true"></i>';
  });

  async function tryAutoLoginFromLocal() {
    const { token, role } = authStore.getLocal();
    if (!token) return;

    try {
      const res = await fetch(CHECK_API, {
        headers: { 'Authorization': 'Bearer ' + token }
      });
      const data = await res.json().catch(() => ({}));
      if (res.ok && data && data.user) {
        const resolvedRole = (data.user.role || role || '').toString().toLowerCase();
        authStore.set(token, resolvedRole, true);
        window.location.replace(rolePath(resolvedRole));
      } else {
        authStore.clear();
      }
    } catch (e) {}
  }

  async function checkEmailStatus(force = false) {
    const email = normalizeEmail(emailIn.value);

    if (!validEmail(email)) {
      state.emailChecked = false;
      state.emailExists = false;
      state.lastCheckedEmail = '';
      lockPasswordSection(true);
      resetOtpSection(true);
      hideBadge();
      emailMeta.textContent = email ? 'Enter a valid registered email to continue.' : 'Enter your email first. Password will unlock only after email verification.';
      return;
    }

    if (!force && state.lastCheckedEmail === email && state.emailChecked) {
      return;
    }

    const requestId = ++state.checkRequestId;

    state.checkingEmail = true;
    state.lastCheckedEmail = email;

    clearAlert();
    lockPasswordSection(true);
    resetOtpSection(true);
    showBadge('info', 'Checking email status...');
    emailMeta.textContent = 'Checking whether this email is already verified...';
    refreshOtpButton();

    try {
      const res = await fetch(EMAIL_CHECK_API, {
        method: 'POST',
        headers: authHeaders(),
        body: JSON.stringify({ email })
      });

      if (requestId !== state.checkRequestId) return;

      const data = await res.json().catch(() => ({}));
      state.emailChecked = true;
      state.emailExists = !!data?.exists;

      if (!res.ok) {
        state.emailExists = false;
        showBadge('pending', 'Unable to check email');
        emailMeta.textContent = data?.message || 'Unable to check email right now.';
        showAlert('error', data?.message || 'Unable to check email.');
        refreshOtpButton();
        return;
      }

      if (!data?.exists) {
        lockPasswordSection(true);
        resetOtpSection(true);
        showBadge('pending', 'Email not found');
        emailMeta.textContent = data?.message || 'No account found with this email.';
        showAlert('error', data?.message || 'No account found with this email.');
        refreshOtpButton();
        return;
      }

      if (data?.is_verified) {
        resetOtpSection(true);
        unlockPasswordSection();
        showBadge('success', 'Email already verified');
        emailMeta.textContent = 'Email is already verified. You can now enter your password and log in.';
        refreshOtpButton();
        return;
      }

      lockPasswordSection(true);
      showOtpSection();
      showBadge('pending', 'Email not verified yet');
      emailMeta.textContent = 'This email is registered but not verified. Send OTP to continue.';
      otpMeta.textContent = 'Click Send OTP. After that, type 6 digits and verification will happen automatically.';
      refreshOtpButton();
    } catch (err) {
      if (requestId !== state.checkRequestId) return;

      lockPasswordSection(true);
      resetOtpSection(true);
      showBadge('pending', 'Check failed');
      emailMeta.textContent = 'Could not check email right now. Please try again.';
      showAlert('error', 'Network error while checking email.');
      refreshOtpButton();
    } finally {
      if (requestId === state.checkRequestId) {
        state.checkingEmail = false;
        refreshOtpButton();
      }
    }
  }

  async function sendOtp() {
    const email = normalizeEmail(emailIn.value);

    if (!validEmail(email)) {
      showAlert('error', 'Please enter a valid email first.');
      return;
    }

    if (!state.emailChecked || !state.emailExists) {
      showAlert('error', 'Please wait until email status is checked.');
      return;
    }

    if (state.emailVerified) {
      showAlert('success', 'Email is already verified.');
      return;
    }

    if (state.resendSeconds > 0) {
      return;
    }

    clearAlert();
    state.sendingOtp = true;
    refreshOtpButton();
    otpMeta.textContent = 'Sending OTP...';

    try {
      const res = await fetch(SEND_OTP_API, {
        method: 'POST',
        headers: authHeaders(),
        body: JSON.stringify({ email })
      });

      const data = await res.json().catch(() => ({}));

      if (!res.ok) {
        state.sendingOtp = false;
        refreshOtpButton();
        showAlert('error', data?.message || 'Failed to send OTP.');
        otpMeta.textContent = data?.message || 'Failed to send OTP.';
        return;
      }

      if (data?.already_verified) {
        unlockPasswordSection();
        resetOtpSection(true);
        showBadge('success', 'Email already verified');
        emailMeta.textContent = 'Email is already verified. You can now enter your password and log in.';
        showAlert('success', data?.message || 'Email is already verified.');
        state.sendingOtp = false;
        refreshOtpButton();
        return;
      }

      state.otpSent = true;
      state.sendingOtp = false;
      otpIn.disabled = false;
      otpIn.value = '';
      otpMeta.textContent = data?.message || 'OTP sent successfully. Enter 6 digits to verify automatically.';
      startOtpCooldown(OTP_RESEND_SECONDS);
      showAlert('success', data?.message || 'OTP sent successfully.');
      otpIn.focus();
    } catch (err) {
      state.sendingOtp = false;
      refreshOtpButton();
      otpMeta.textContent = 'Network error while sending OTP.';
      showAlert('error', 'Network error while sending OTP.');
    }
  }

  async function verifyOtpAuto() {
    const email = normalizeEmail(emailIn.value);
    const otp = (otpIn.value || '').trim();

    if (!validEmail(email) || otp.length !== 6 || state.verifyingOtp || !state.otpSent) {
      return;
    }

    clearAlert();
    state.verifyingOtp = true;
    otpIn.disabled = true;
    otpMeta.textContent = 'Verifying OTP...';

    try {
      const res = await fetch(VERIFY_OTP_API, {
        method: 'POST',
        headers: authHeaders(),
        body: JSON.stringify({ email, otp })
      });

      const data = await res.json().catch(() => ({}));

      if (!res.ok) {
        state.verifyingOtp = false;
        otpIn.disabled = false;
        otpMeta.textContent = data?.message || 'OTP verification failed.';
        showAlert('error', data?.message || 'OTP verification failed.');
        otpIn.focus();
        return;
      }

      unlockPasswordSection();
      state.verifyingOtp = false;
      state.emailVerified = true;
      otpIn.disabled = true;
      sendOtpBtn.disabled = true;
      sendOtpBtn.innerHTML = '<i class="fa-solid fa-circle-check me-1"></i> Verified';
      showBadge('success', 'Email verified successfully');
      emailMeta.textContent = 'Your email is verified. You can now enter your password and log in.';
      otpMeta.textContent = data?.message || 'Email verified successfully.';
      showAlert('success', data?.message || 'Email verified successfully.');
      pwIn.focus();
    } catch (err) {
      state.verifyingOtp = false;
      otpIn.disabled = false;
      otpMeta.textContent = 'Network error while verifying OTP.';
      showAlert('error', 'Network error while verifying OTP.');
      otpIn.focus();
    } finally {
      setBusy(false);
    }
  }

  let emailCheckTimer = null;

  emailIn?.addEventListener('input', () => {
    resetVerificationStateFromEmailEdit();

    const email = normalizeEmail(emailIn.value);
    if (!validEmail(email)) return;

    clearTimeout(emailCheckTimer);
    emailCheckTimer = setTimeout(() => {
      checkEmailStatus(true);
    }, 600);
  });

  emailIn?.addEventListener('blur', () => {
    const email = normalizeEmail(emailIn.value);
    if (validEmail(email) && !state.emailChecked && !state.checkingEmail) {
      checkEmailStatus(true);
    }
  });

  sendOtpBtn?.addEventListener('click', sendOtp);

  otpIn?.addEventListener('input', () => {
    otpIn.value = (otpIn.value || '').replace(/\D/g, '').slice(0, 6);

    if (otpIn.value.length === 6) {
      verifyOtpAuto();
    }
  });

  otpIn?.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
      e.preventDefault();
      verifyOtpAuto();
    }
  });

  captchaInput?.addEventListener('input', validateCaptchaInput);

  captchaInput?.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
      e.preventDefault();
      validateCaptchaInput();
    }
  });

  captchaCanvas?.addEventListener('click', () => {
    resetCaptcha('A new captcha has been generated.');
    captchaInput.focus();
  });

  captchaCanvasWrap?.addEventListener('click', () => {
    resetCaptcha('A new captcha has been generated.');
    captchaInput.focus();
  });

  window.addEventListener('resize', () => {
    if (state.captchaText) {
      drawCaptcha(state.captchaText);
    }
  });

  document.addEventListener('DOMContentLoaded', () => {
    tryAutoLoginFromLocal();
    lockPasswordSection(true);
    resetOtpSection(true);
    hideBadge();
    resetCaptcha();
    emailMeta.textContent = 'Enter your email first. Password will unlock only after email verification.';
  });

  form?.addEventListener('submit', async (e) => {
    e.preventDefault();
    clearAlert();

    const identifier = normalizeEmail(emailIn.value);
    const password = pwIn.value || '';
    const keep = !!keepCb.checked;

    if (!identifier) {
      showAlert('error', 'Please enter your email.');
      return;
    }

    if (!state.emailVerified) {
      showAlert('error', 'Please verify your email first.');
      return;
    }

    if (!state.captchaSolved) {
      showAlert('error', 'Please complete the captcha correctly.');
      captchaInput.focus();
      return;
    }

    if (!password) {
      showAlert('error', 'Please enter your password.');
      return;
    }

    setBusy(true);

    try {
      const res = await fetch(LOGIN_API, {
        method: 'POST',
        headers: authHeaders(),
        body: JSON.stringify({ email: identifier, password, remember: keep })
      });

      const data = await res.json().catch(() => ({}));

      if (!res.ok) {
        const msg = data?.message || data?.error ||
          (data?.errors ? Object.values(data.errors).flat().join(', ') : 'Unable to log in.');
        showAlert('error', msg);
        resetCaptcha('Please solve the new captcha and try again.');
        setBusy(false);
        return;
      }

      const token = data?.access_token || data?.token || '';
      const role = (data?.user?.role || localStorage.getItem('role') || 'student').toLowerCase();

      if (!token) {
        showAlert('error', 'No token received from server.');
        resetCaptcha('Please solve the new captcha and try again.');
        setBusy(false);
        return;
      }

      authStore.set(token, role, keep);
      showAlert('success', 'Login successful. Redirecting…');

      setTimeout(() => {
        window.location.assign(rolePath(role));
      }, 500);

    } catch (err) {
      showAlert('error', 'Network error. Please try again.');
      resetCaptcha('Please solve the new captcha and try again.');
    } finally {
      setBusy(false);
    }
  });

  (function(){
    const stage  = document.getElementById('lx_visual');
    const hero   = document.getElementById('lx_hero');
    const frame  = document.querySelector('.lx-hero-frame');
    const img    = document.querySelector('.lx-hero-img img');
    if (!stage || !frame || !img || !hero) return;

    const mq = window.matchMedia('(max-width: 992px)');
    let targetTX = 0, targetTY = 0, targetRX = 0, targetRY = 0;
    let currTX = 0, currTY = 0, currRX = 0, currRY = 0;
    let rafId = null;

    const MAX_T = 18, MAX_RX = 6, MAX_RY = 8, LERP = 0.12;

    function onMove(e){
      const rect = stage.getBoundingClientRect();
      const cx = rect.left + rect.width / 2;
      const cy = rect.top + rect.height / 2;
      const dx = (e.clientX - cx) / (rect.width / 2);
      const dy = (e.clientY - cy) / (rect.height / 2);
      const ndx = Math.max(-1, Math.min(1, dx));
      const ndy = Math.max(-1, Math.min(1, dy));

      targetTX = ndx * MAX_T;
      targetTY = ndy * MAX_T;
      targetRY = ndx * MAX_RY;
      targetRX = -ndy * MAX_RX;

      if (!hero.classList.contains('is-tracking')) {
        hero.classList.add('is-tracking');
        tick();
      }
    }

    function onLeave() {
      targetTX = targetTY = targetRX = targetRY = 0;
    }

    function tick(){
      currTX += (targetTX - currTX) * LERP;
      currTY += (targetTY - currTY) * LERP;
      currRX += (targetRX - currRX) * LERP;
      currRY += (targetRY - currRY) * LERP;

      frame.style.transform =
        `translate3d(${currTX.toFixed(2)}px, ${currTY.toFixed(2)}px, 0)
         rotateX(${currRX.toFixed(2)}deg)
         rotateY(${currRY.toFixed(2)}deg)`;

      const ix = (-currTX * 0.6).toFixed(2);
      const iy = (-currTY * 0.6).toFixed(2);
      img.style.transform = `translate3d(${ix}px, ${iy}px, 0) scale(1.05)`;

      const nearZero =
        Math.abs(currTX) < 0.15 && Math.abs(currTY) < 0.15 &&
        Math.abs(currRX) < 0.08 && Math.abs(currRY) < 0.08 &&
        Math.abs(targetTX) < 0.15 && Math.abs(targetTY) < 0.15 &&
        Math.abs(targetRX) < 0.08 && Math.abs(targetRY) < 0.08;

      if (!nearZero) {
        rafId = requestAnimationFrame(tick);
      } else {
        frame.style.transform = 'translate3d(0,0,0) rotateX(0) rotateY(0)';
        img.style.transform = 'translate3d(0,0,0) scale(1)';
        hero.classList.remove('is-tracking');
        rafId && cancelAnimationFrame(rafId);
        rafId = null;
      }
    }

    function attach() {
      if (mq.matches) return;
      stage.addEventListener('mousemove', onMove);
      stage.addEventListener('mouseleave', onLeave);
    }

    function detach() {
      stage.removeEventListener('mousemove', onMove);
      stage.removeEventListener('mouseleave', onLeave);
      onLeave();
    }

    attach();
    mq.addEventListener('change', () => { detach(); attach(); });
    window.addEventListener('blur', onLeave);
  })();
})();
</script>
</body>
</html>