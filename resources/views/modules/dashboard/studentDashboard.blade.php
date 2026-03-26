{{-- resources/views/modules/dashboard/studentDashboard.blade.php --}}
{{-- @extends('layouts.msit.structure') --}}

@section('title','Student Dashboard')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

<style>
/* =========================
 * Student Dashboard
 * Static / No API
 * Minimal + colorful + graphic
 * ========================= */

.ad-wrap{
  max-width:1200px;
  margin:18px auto 42px;
  padding:0 12px;
  overflow:visible;
}

/* HERO */
.ad-hero{
  position:relative;
  overflow:hidden;
  border-radius:24px;
  padding:22px 22px;
  color:#fff;
  background:
    radial-gradient(circle at 15% 20%, rgba(255,255,255,.16) 0, transparent 24%),
    radial-gradient(circle at 85% 25%, rgba(255,255,255,.12) 0, transparent 22%),
    linear-gradient(135deg, #9E363A 0%, #C94B50 52%, #7b2cbf 100%);
  border:1px solid rgba(255,255,255,.14);
  box-shadow:0 18px 40px rgba(107,37,40,.18);
}

.ad-hero::before,
.ad-hero::after{
  content:"";
  position:absolute;
  border-radius:50%;
  pointer-events:none;
}

.ad-hero::before{
  right:-72px;
  top:-72px;
  width:220px;
  height:220px;
  background:radial-gradient(circle, rgba(255,255,255,.16), rgba(255,255,255,0) 68%);
}

.ad-hero::after{
  left:-52px;
  bottom:-52px;
  width:170px;
  height:170px;
  background:radial-gradient(circle, rgba(255,255,255,.10), rgba(255,255,255,0) 70%);
}

.ad-hero-inner{
  position:relative;
  z-index:1;
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:18px;
  flex-wrap:wrap;
}

.ad-hero-left{
  flex:1;
  min-width:260px;
  display:flex;
  align-items:center;
  gap:14px;
}

.sd-logo{
  width:60px;
  height:60px;
  border-radius:16px;
  display:flex;
  align-items:center;
  justify-content:center;
  background:rgba(255,255,255,.14);
  border:1px solid rgba(255,255,255,.2);
  box-shadow:0 10px 20px rgba(0,0,0,.10);
  overflow:hidden;
  flex:0 0 auto;
}

.sd-logo img{
  width:100%;
  height:100%;
  object-fit:contain;
  padding:10px;
}

.ad-hero-title{
  margin:0;
  font-size:28px;
  line-height:1.1;
  font-weight:900;
  font-family:var(--font-head);
  letter-spacing:-.3px;
}

.ad-hero-sub{
  margin:6px 0 0;
  font-size:14px;
  opacity:.94;
  max-width:650px;
}

.ad-hero-tags{
  display:flex;
  flex-wrap:wrap;
  gap:10px;
  margin-top:12px;
}

.ad-hero-tag{
  display:inline-flex;
  align-items:center;
  gap:8px;
  padding:8px 12px;
  border-radius:999px;
  background:rgba(255,255,255,.14);
  border:1px solid rgba(255,255,255,.18);
  font-size:12.5px;
  font-weight:800;
}

.ad-hero-graphic{
  width:210px;
  height:130px;
  position:relative;
  flex:0 0 auto;
}

.hg-ring,
.hg-card{
  position:absolute;
}

.hg-ring{
  border:1px dashed rgba(255,255,255,.28);
  border-radius:50%;
  animation:spinSlow 16s linear infinite;
}

.hg-ring.r1{width:120px;height:120px;left:44px;top:4px;}
.hg-ring.r2{width:76px;height:76px;left:66px;top:26px;animation-direction:reverse;}

.hg-card{
  display:flex;
  align-items:center;
  justify-content:center;
  color:#fff;
  background:rgba(255,255,255,.14);
  border:1px solid rgba(255,255,255,.2);
  box-shadow:0 18px 30px rgba(0,0,0,.08);
  backdrop-filter:blur(6px);
  border-radius:18px;
  animation:floatY 4.8s ease-in-out infinite;
}

.hg-card i{font-size:20px}

.hg-card.c1{width:64px;height:64px;left:26px;top:6px;animation-delay:0s;}
.hg-card.c2{width:56px;height:56px;right:18px;top:24px;animation-delay:.8s;}
.hg-card.c3{width:60px;height:60px;left:76px;bottom:0;animation-delay:1.4s;}

/* GRID */
.ad-grid{
  margin-top:16px;
  display:grid;
  grid-template-columns:repeat(12,minmax(0,1fr));
  gap:14px;
  align-items:stretch;
}

.ad-col-12{grid-column:span 12;}
.ad-col-7{grid-column:span 7;}
.ad-col-5{grid-column:span 5;}

.ad-card{
  position:relative;
  background:var(--surface);
  border:1px solid var(--line-strong);
  border-radius:20px;
  box-shadow:var(--shadow-2);
  overflow:hidden;
  height:100%;
}

.ad-card-head{
  padding:14px 16px 12px;
  border-bottom:1px solid var(--line-soft);
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:10px;
}

.ad-card-title{
  display:flex;
  align-items:center;
  gap:10px;
  font-family:var(--font-head);
  color:var(--ink);
  font-weight:900;
}

.ad-card-title i{color:var(--primary-color);}
.ad-card-sub{
  font-size:12px;
  color:var(--muted-color);
  margin-top:3px;
}

.ad-card-body{padding:16px;}

/* WELCOME */
.sd-welcome{
  position:relative;
  min-height:100%;
  border-radius:18px;
  padding:18px;
  overflow:hidden;
  border:1px dashed var(--line-strong);
  background:
    radial-gradient(circle at top right, rgba(201,75,80,.08), transparent 28%),
    linear-gradient(180deg, #fff, #fffafa);
}

.sd-welcome::before{
  content:"";
  position:absolute;
  right:-34px;
  bottom:-34px;
  width:130px;
  height:130px;
  border-radius:50%;
  background:radial-gradient(circle, rgba(158,54,58,.09), rgba(158,54,58,0) 70%);
}

.sd-welcome-top{
  position:relative;
  z-index:1;
  display:flex;
  align-items:flex-start;
  justify-content:space-between;
  gap:16px;
  flex-wrap:wrap;
}

.sd-welcome-left{flex:1;min-width:220px;}

.sd-hello{
  margin:0 0 8px;
  font-size:24px;
  line-height:1.15;
  font-weight:900;
  color:var(--ink);
  font-family:var(--font-head);
}

.sd-hello span{color:var(--primary-color);}

.sd-welcome-text{
  margin:0;
  color:var(--muted-color);
  font-size:14px;
  line-height:1.72;
  max-width:560px;
}

.sd-badges{
  display:flex;
  flex-wrap:wrap;
  gap:10px;
  margin-top:16px;
}

.sd-badge{
  display:inline-flex;
  align-items:center;
  gap:8px;
  padding:9px 12px;
  border-radius:999px;
  background:#fff;
  border:1px solid var(--line-soft);
  font-size:12.5px;
  font-weight:800;
  color:var(--text-color);
  box-shadow:0 8px 18px rgba(0,0,0,.03);
}

.sd-badge i{color:var(--primary-color);}

.sd-welcome-visual{
  width:165px;
  min-width:165px;
  height:150px;
  position:relative;
}

.wv-blob{
  position:absolute;
  inset:12px 16px;
  border-radius:28px;
  background:linear-gradient(135deg, rgba(158,54,58,.14), rgba(201,75,80,.08));
  border:1px solid rgba(158,54,58,.12);
}

.wv-icon{
  position:absolute;
  display:flex;
  align-items:center;
  justify-content:center;
  border-radius:16px;
  color:#fff;
  box-shadow:0 18px 30px rgba(0,0,0,.10);
  animation:floatY 5s ease-in-out infinite;
}

.wv-icon i{font-size:18px}
.wv-icon.i1{
  width:54px;height:54px;left:10px;top:16px;
  background:linear-gradient(135deg,#9E363A,#C94B50);
}
.wv-icon.i2{
  width:48px;height:48px;right:14px;top:30px;
  background:linear-gradient(135deg,#7b2cbf,#9d4edd);
  animation-delay:.8s;
}
.wv-icon.i3{
  width:58px;height:58px;left:56px;bottom:6px;
  background:linear-gradient(135deg,#0f766e,#14b8a6);
  animation-delay:1.4s;
}

/* QUICK LINKS COLUMN */
.sd-quick-col{
  height:100%;
  display:flex;
  flex-direction:column;
  gap:14px;
}

.sd-quick{
  position:relative;
  display:flex;
  flex-direction:column;
  justify-content:space-between;
  flex:1 1 0;
  border-radius:20px;
  padding:18px;
  text-decoration:none;
  color:inherit;
  overflow:hidden;
  border:1px solid var(--line-strong);
  box-shadow:var(--shadow-1);
  transition:transform .18s ease, box-shadow .18s ease, border-color .18s ease;
  min-height:0;
}

.sd-quick:hover{
  transform:translateY(-3px);
  box-shadow:var(--shadow-2);
  border-color:rgba(158,54,58,.30);
  text-decoration:none;
  color:inherit;
}

.sd-quick::before{
  content:"";
  position:absolute;
  right:-26px;
  top:-26px;
  width:100px;
  height:100px;
  border-radius:50%;
  opacity:.75;
}

.sd-quick.profile{
  background:linear-gradient(180deg,#fff,#fff7f8);
}
.sd-quick.profile::before{
  background:radial-gradient(circle, rgba(158,54,58,.14), rgba(158,54,58,0) 70%);
}

.sd-quick.feedback{
  background:linear-gradient(180deg,#fff,#faf7ff);
}
.sd-quick.feedback::before{
  background:radial-gradient(circle, rgba(123,44,191,.14), rgba(123,44,191,0) 70%);
}

.sd-quick-top{
  position:relative;
  z-index:1;
  display:flex;
  align-items:flex-start;
  justify-content:space-between;
  gap:12px;
}

.sd-quick-icon{
  width:58px;
  height:58px;
  border-radius:18px;
  display:flex;
  align-items:center;
  justify-content:center;
  color:#fff;
  box-shadow:0 16px 26px rgba(0,0,0,.10);
  animation:pulseSoft 3.2s ease-in-out infinite;
}

.sd-quick.profile .sd-quick-icon{
  background:linear-gradient(135deg,#9E363A,#C94B50);
}
.sd-quick.feedback .sd-quick-icon{
  background:linear-gradient(135deg,#7b2cbf,#9d4edd);
}

.sd-quick-icon i{font-size:22px;}

.sd-quick-mini{
  width:34px;
  height:34px;
  border-radius:12px;
  display:flex;
  align-items:center;
  justify-content:center;
  background:#fff;
  border:1px solid var(--line-soft);
  color:var(--primary-color);
  box-shadow:0 8px 16px rgba(0,0,0,.04);
}

.sd-quick-mid{
  position:relative;
  z-index:1;
  margin-top:16px;
}

.sd-quick-title{
  margin:0;
  font-size:18px;
  font-weight:900;
  color:var(--ink);
  font-family:var(--font-head);
}

.sd-quick-text{
  margin:6px 0 0;
  font-size:13px;
  color:var(--muted-color);
  line-height:1.55;
}

.sd-quick-foot{
  position:relative;
  z-index:1;
  margin-top:18px;
  display:flex;
  align-items:center;
  gap:8px;
  font-size:13px;
  font-weight:800;
  color:var(--primary-color);
}

/* ANIMATION */
@keyframes floatY{
  0%,100%{transform:translateY(0)}
  50%{transform:translateY(-8px)}
}

@keyframes pulseSoft{
  0%,100%{transform:scale(1)}
  50%{transform:scale(1.06)}
}

@keyframes spinSlow{
  from{transform:rotate(0deg)}
  to{transform:rotate(360deg)}
}

/* RESPONSIVE */
@media (max-width: 992px){
  .ad-col-7,
  .ad-col-5{
    grid-column:span 12;
  }

  .ad-hero{
    padding:18px 16px;
    border-radius:20px;
  }

  .ad-hero-title{font-size:24px}
  .ad-hero-graphic{
    width:180px;
    height:118px;
    margin-inline:auto;
  }

  .sd-welcome-top{
    flex-direction:column;
  }

  .sd-welcome-visual{
    width:100%;
    min-width:0;
    height:120px;
  }

  .sd-quick-col{
    height:auto;
  }

  .sd-quick{
    min-height:180px;
  }
}

@media (max-width: 640px){
  .ad-wrap{
    margin:12px auto 34px;
    padding:0 8px;
  }

  .ad-card-head,
  .ad-card-body{
    padding:14px;
  }

  .ad-hero-left{
    align-items:flex-start;
  }

  .sd-logo{
    width:54px;
    height:54px;
  }

  .sd-hello{
    font-size:20px;
  }

  .ad-hero-tags{
    gap:8px;
  }

  .ad-hero-tag,
  .sd-badge{
    font-size:12px;
  }

  .sd-quick{
    min-height:160px;
  }
}
</style>
@endpush

@section('content')
<div class="ad-wrap">

  {{-- HERO --}}
  <div class="ad-hero">
    <div class="ad-hero-inner">
      <div class="ad-hero-left">
        <div class="sd-logo">
          <img src="{{ asset('assets/images/logo.png') }}"
               alt="Logo"
               onerror="this.style.display='none'; this.parentElement.innerHTML='<i class=&quot;fa-solid fa-graduation-cap&quot; style=&quot;font-size:22px;opacity:.95&quot;></i>';">
        </div>

        <div>
          <h1 class="ad-hero-title">Student Dashboard</h1>
          <div class="ad-hero-sub">
            A clean, colorful and simple student space.
          </div>

          <div class="ad-hero-tags">
            <span class="ad-hero-tag"><i class="fa-solid fa-bolt"></i> Quick</span>
            <span class="ad-hero-tag"><i class="fa-solid fa-shield-heart"></i> Secure</span>
            <span class="ad-hero-tag"><i class="fa-solid fa-sparkles"></i> Smooth</span>
          </div>
        </div>
      </div>

      <div class="ad-hero-graphic">
        <div class="hg-ring r1"></div>
        <div class="hg-ring r2"></div>

        <div class="hg-card c1"><i class="fa-solid fa-user"></i></div>
        <div class="hg-card c2"><i class="fa-solid fa-comment-dots"></i></div>
        <div class="hg-card c3"><i class="fa-solid fa-graduation-cap"></i></div>
      </div>
    </div>
  </div>

  <div class="ad-grid">

    {{-- LEFT : WELCOME --}}
    <div class="ad-col-7">
      <div class="ad-card">
        <div class="ad-card-head">
          <div>
            <div class="ad-card-title">
              <i class="fa-solid fa-hand-sparkles"></i>
              <span>Welcome</span>
            </div>
            <div class="ad-card-sub">Student home</div>
          </div>
        </div>

        <div class="ad-card-body">
          <div class="sd-welcome">
            <div class="sd-welcome-top">
              <div class="sd-welcome-left">
                <h3 class="sd-hello">Hey <span>Student</span> 👋</h3>
                <p class="sd-welcome-text">
                  Manage your profile, share feedback, and enjoy a clean dashboard with easy access.
                </p>

                <div class="sd-badges">
                  <span class="sd-badge"><i class="fa-solid fa-id-card"></i> Profile</span>
                  <span class="sd-badge"><i class="fa-solid fa-message"></i> Feedback</span>
                  <span class="sd-badge"><i class="fa-solid fa-circle-check"></i> Simple UI</span>
                </div>
              </div>

              <div class="sd-welcome-visual">
                <div class="wv-blob"></div>
                <div class="wv-icon i1"><i class="fa-solid fa-user-pen"></i></div>
                <div class="wv-icon i2"><i class="fa-solid fa-comment-dots"></i></div>
                <div class="wv-icon i3"><i class="fa-solid fa-star"></i></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- RIGHT : PROFILE + FEEDBACK COLUMN WISE SAME TOTAL HEIGHT --}}
    <div class="ad-col-5">
      <div class="sd-quick-col">

        <a href="/user/basic-information/manage" class="sd-quick profile">
          <div class="sd-quick-top">
            <div class="sd-quick-icon">
              <i class="fa-solid fa-user"></i>
            </div>
            <div class="sd-quick-mini">
              <i class="fa-solid fa-arrow-right"></i>
            </div>
          </div>

          <div class="sd-quick-mid">
            <h3 class="sd-quick-title">Profile</h3>
            <p class="sd-quick-text">View and update basic information.</p>
          </div>

          <div class="sd-quick-foot">
            <span>Open</span>
            <i class="fa-solid fa-arrow-right"></i>
          </div>
        </a>

        <a href="/feedback/submit" class="sd-quick feedback">
          <div class="sd-quick-top">
            <div class="sd-quick-icon">
              <i class="fa-solid fa-comment-dots"></i>
            </div>
            <div class="sd-quick-mini">
              <i class="fa-solid fa-arrow-right"></i>
            </div>
          </div>

          <div class="sd-quick-mid">
            <h3 class="sd-quick-title">Feedback</h3>
            <p class="sd-quick-text">Share your response quickly.</p>
          </div>

          <div class="sd-quick-foot">
            <span>Submit</span>
            <i class="fa-solid fa-arrow-right"></i>
          </div>
        </a>

      </div>
    </div>

  </div>
</div>
@endsection