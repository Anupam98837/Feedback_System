{{-- resources/views/modules/feedbacks/manageFeedbackQuestions.blade.php --}}
@section('title','Feedback Results')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

<style>

.fq-wrap{padding:14px 4px}

/* Toolbar panel */
.fq-toolbar.panel{background:var(--surface);border:1px solid var(--line-strong);border-radius:16px;box-shadow:var(--shadow-2);padding:12px;}

/* Table Card */
.table-wrap.card{position:relative;border:1px solid var(--line-strong);border-radius:16px;background:var(--surface);box-shadow:var(--shadow-2);overflow:visible;}
.table-wrap .card-body{overflow:visible}
.table{--bs-table-bg:transparent}
.table thead th{font-weight:600;color:var(--muted-color);font-size:13px;border-bottom:1px solid var(--line-strong);background:var(--surface)}
.table thead.sticky-top{z-index:3}
.table tbody tr{border-top:1px solid var(--line-soft)}
.table tbody tr:hover{background:var(--page-hover)}
td .fw-semibold{color:var(--ink)}
.small{font-size:12.5px}
.fr-skel-line{height:12px;border-radius:999px;background:linear-gradient(90deg,#00000010,#00000006,#00000010);}
.fr-skel-line + .fr-skel-line{margin-top:8px;}

.table-responsive > .table{ width:max-content; min-width:980px; }
.table-responsive th, .table-responsive td{ white-space:nowrap; }
.fr-nowrap{ white-space:nowrap; }

/* Tabs */
.nav.nav-tabs{border-color:var(--line-strong)}
.nav-tabs .nav-link{color:var(--ink)}
.nav-tabs .nav-link.active{background:var(--surface);border-color:var(--line-strong) var(--line-strong) var(--surface)}

/* Empty */
.empty{color:var(--muted-color)}
.pill{display:inline-flex;align-items:center;gap:8px;padding:6px 10px;border-radius:999px;background:color-mix(in oklab, var(--primary-color) 10%, transparent);color:var(--primary-color);border:1px solid color-mix(in oklab, var(--primary-color) 18%, var(--line-soft));font-size:12px;font-weight:700;}
.pill i{opacity:.85}

/* Clickable row */
.tr-click{cursor:pointer}
.tr-click:active{transform:translateY(.5px)}

/* Loading overlay */
#globalLoading.loading-overlay{ display:none !important; }
#globalLoading.loading-overlay.is-show{ display:flex !important; }

/* Detail modal head */
.detail-head{display:flex; align-items:flex-start; justify-content:space-between;gap:14px;}
.detail-meta{display:flex; flex-wrap:wrap; gap:8px;}
.detail-meta .chip{display:inline-flex; align-items:center; gap:8px;padding:6px 10px; border-radius:999px;border:1px solid var(--line-strong);background:color-mix(in oklab, var(--surface) 92%, transparent);font-size:12px;color:var(--ink);}
.detail-meta .chip i{opacity:.75}

/* Key-value info */
.kv{display:grid;grid-template-columns: 160px 1fr;gap:6px 12px;font-size:13px;}
.kv .k{color:var(--muted-color)}
.kv .v{color:var(--ink); font-weight:700}

/* Faculty tabs (inside detail modal) */
.fac-tabsbar{display:flex;gap:8px;flex-wrap:wrap;padding:10px;border:1px solid var(--line-strong);background:var(--surface);border-radius:14px;box-shadow:var(--shadow-2);}
.fac-tabbtn{display:inline-flex;align-items:center;gap:8px;padding:8px 10px;border-radius:999px;border:1px solid var(--line-strong);background:color-mix(in oklab, var(--surface) 92%, transparent);color:var(--ink);font-weight:800;font-size:12.5px;cursor:pointer;transition:transform .08s ease, background .12s ease, border-color .12s ease;user-select:none;max-width: 100%;}
.fac-tabbtn:active{transform:translateY(.5px)}
.fac-tabbtn i{opacity:.85}
.fac-tabbtn .nm{display:inline-block;max-width: 240px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.fac-tabbtn.active{background:color-mix(in oklab, var(--primary-color) 12%, transparent);border-color:color-mix(in oklab, var(--primary-color) 30%, var(--line-strong));color:var(--primary-color);}

/* Screenshot-like matrix */
.matrix-wrap{border:1px solid var(--line-strong);border-radius:14px;overflow:auto;background:var(--surface);box-shadow:var(--shadow-2);}
.matrix{width:max-content;min-width:100%;border-collapse:collapse;}
.matrix th, .matrix td{border:1px solid var(--line-soft);padding:10px 10px;font-size:13px;vertical-align:top;}
.matrix thead th{background:color-mix(in oklab, var(--surface) 90%, var(--page-hover));font-weight:800;color:var(--ink);text-align:center;white-space:nowrap;}
.matrix .qcol{min-width:520px;max-width:720px;text-align:left;}
.matrix td{text-align:center;font-weight:800;}
.matrix td.qtext{text-align:left;font-weight:700;color:var(--ink);}
.matrix .avgrow td{background:color-mix(in oklab, var(--primary-color) 6%, transparent);}
/* ✅ ensure avg-row single cell truly behaves like full-width content */
.matrix .avgrow td.qtext{width:100%;text-align:center;white-space:normal;}
.matrix .submeta{display:block;margin-top:6px;font-size:12px;color:var(--muted-color);font-weight:600;}

/* ✅ Column colors (5..1) */
:root{
  --rate-5: #1f8a3b;      /* green */
  --rate-4: #2f7bbf;      /* blue */
  --rate-3: #c08a00;      /* amber */
  --rate-2: #c45a1a;      /* orange */
  --rate-1: #b3262e;      /* red */
}

.matrix thead th.col5{ background:color-mix(in oklab, var(--rate-5) 16%, var(--surface)); }
.matrix thead th.col4{ background:color-mix(in oklab, var(--rate-4) 16%, var(--surface)); }
.matrix thead th.col3{ background:color-mix(in oklab, var(--rate-3) 16%, var(--surface)); }
.matrix thead th.col2{ background:color-mix(in oklab, var(--rate-2) 16%, var(--surface)); }
.matrix thead th.col1{ background:color-mix(in oklab, var(--rate-1) 16%, var(--surface)); }

.matrix td.col5{ background:color-mix(in oklab, var(--rate-5) 10%, transparent); }
.matrix td.col4{ background:color-mix(in oklab, var(--rate-4) 10%, transparent); }
.matrix td.col3{ background:color-mix(in oklab, var(--rate-3) 10%, transparent); }
.matrix td.col2{ background:color-mix(in oklab, var(--rate-2) 10%, transparent); }
.matrix td.col1{ background:color-mix(in oklab, var(--rate-1) 10%, transparent); }

/* keep average row tint, but still allow subtle column colors */
.matrix .avgrow td.col5{ background:color-mix(in oklab, var(--rate-5) 12%, color-mix(in oklab, var(--primary-color) 6%, transparent)); }
.matrix .avgrow td.col4{ background:color-mix(in oklab, var(--rate-4) 12%, color-mix(in oklab, var(--primary-color) 6%, transparent)); }
.matrix .avgrow td.col3{ background:color-mix(in oklab, var(--rate-3) 12%, color-mix(in oklab, var(--primary-color) 6%, transparent)); }
.matrix .avgrow td.col2{ background:color-mix(in oklab, var(--rate-2) 12%, color-mix(in oklab, var(--primary-color) 6%, transparent)); }
.matrix .avgrow td.col1{ background:color-mix(in oklab, var(--rate-1) 12%, color-mix(in oklab, var(--primary-color) 6%, transparent)); }

/* Export modal helpers */
.export-pills{display:flex; flex-wrap:wrap; gap:8px;padding:10px;border:1px dashed var(--line-soft);border-radius:14px;background:color-mix(in oklab, var(--surface) 92%, transparent);}
.export-pill{display:inline-flex; align-items:center; gap:8px;padding:8px 10px;border:1px solid var(--line-strong);border-radius:999px;background:var(--surface);font-weight:800;font-size:12.5px;color:var(--ink);}
.export-pill input{ transform:translateY(1px); }
.export-pill i{opacity:.85}



/* =========================
 * Grid Drill-down Explorer
 * ========================= */
.fr-page-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:14px;padding:16px;border:1px solid var(--line-strong);border-radius:18px;background:linear-gradient(135deg,color-mix(in oklab,var(--primary-color) 10%,var(--surface)),var(--surface));box-shadow:var(--shadow-2);}
.fr-page-head h4{margin:0;color:var(--ink);font-weight:900;letter-spacing:-.02em;}
.fr-page-head p{margin:4px 0 0;color:var(--muted-color);font-size:13px;}
.fr-head-icon{width:48px;height:48px;border-radius:16px;display:grid;place-items:center;background:color-mix(in oklab,var(--primary-color) 14%,transparent);color:var(--primary-color);border:1px solid color-mix(in oklab,var(--primary-color) 24%,var(--line-soft));font-size:20px;}
.fr-summary-strip{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-bottom:14px;}
.fr-mini-stat{padding:12px;border:1px solid var(--line-strong);border-radius:16px;background:var(--surface);box-shadow:var(--shadow-2);}
.fr-mini-stat .lbl{font-size:12px;color:var(--muted-color);font-weight:800;text-transform:uppercase;letter-spacing:.04em;}
.fr-mini-stat .val{margin-top:4px;font-size:22px;font-weight:900;color:var(--ink);line-height:1;}
.fr-mini-stat .hint{margin-top:4px;font-size:12px;color:var(--muted-color);}
.fr-grid-shell{position:relative;border:1px solid var(--line-strong);border-radius:18px;background:var(--surface);box-shadow:var(--shadow-2);overflow:hidden;}
.fr-grid-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;padding:14px;border-bottom:1px solid var(--line-soft);background:color-mix(in oklab,var(--surface) 88%,var(--page-hover));}
.fr-grid-title{font-weight:900;color:var(--ink);font-size:16px;}
.fr-grid-subtitle{color:var(--muted-color);font-size:12.5px;margin-top:2px;}
.fr-breadcrumb{display:flex;align-items:center;flex-wrap:wrap;gap:7px;padding:12px 14px;border-bottom:1px solid var(--line-soft);}
.fr-bc-btn{border:1px solid var(--line-strong);background:var(--surface);color:var(--ink);border-radius:999px;padding:7px 10px;font-size:12px;font-weight:800;display:inline-flex;align-items:center;gap:7px;cursor:pointer;transition:.14s ease;}
.fr-bc-btn:hover{transform:translateY(-1px);border-color:color-mix(in oklab,var(--primary-color) 36%,var(--line-strong));color:var(--primary-color);}
.fr-bc-sep{color:var(--muted-color);font-size:11px;opacity:.75;}
.fr-grid-body{padding:14px;}
.fr-card-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(235px,1fr));gap:12px;}
.fr-drill-card{position:relative;min-height:150px;border:1px solid var(--line-strong);border-radius:18px;background:linear-gradient(180deg,color-mix(in oklab,var(--surface) 96%,white),var(--surface));box-shadow:var(--shadow-2);padding:14px;cursor:pointer;overflow:hidden;transition:transform .14s ease,box-shadow .14s ease,border-color .14s ease;background .14s ease;}
.fr-drill-card:before{content:"";position:absolute;inset:0 auto 0 0;width:4px;background:linear-gradient(180deg,var(--primary-color),color-mix(in oklab,var(--primary-color) 45%,transparent));opacity:.9;}
.fr-drill-card:hover{transform:translateY(-3px);box-shadow:0 14px 34px rgba(15,23,42,.12);border-color:color-mix(in oklab,var(--primary-color) 38%,var(--line-strong));}
.fr-drill-card:after{content:"";position:absolute;width:110px;height:110px;border-radius:999px;right:-54px;top:-54px;background:color-mix(in oklab,var(--primary-color) 8%,transparent);pointer-events:none;}
.fr-card-top{display:flex;align-items:flex-start;gap:10px;}
.fr-card-icon{width:40px;height:40px;flex:0 0 40px;border-radius:14px;display:grid;place-items:center;background:color-mix(in oklab,var(--primary-color) 12%,transparent);color:var(--primary-color);border:1px solid color-mix(in oklab,var(--primary-color) 18%,var(--line-soft));}
.fr-card-title{font-weight:900;color:var(--ink);line-height:1.18;min-width:0;word-break:break-word;}
.fr-card-meta{margin-top:4px;color:var(--muted-color);font-size:12px;line-height:1.35;}
.fr-card-stats{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px;margin-top:12px;}
.fr-stat-pill{border:1px solid var(--line-soft);border-radius:14px;padding:9px;background:color-mix(in oklab,var(--surface) 94%,var(--page-hover));text-align:center;}
.fr-stat-pill b{display:block;color:var(--ink);font-size:16px;line-height:1;}
.fr-stat-pill span{display:block;margin-top:4px;color:var(--muted-color);font-size:10.5px;font-weight:800;text-transform:uppercase;letter-spacing:.03em;}
.fr-progress-line{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-top:12px;font-size:12px;color:var(--muted-color);font-weight:800;}
.fr-progress{height:8px;border-radius:999px;background:color-mix(in oklab,var(--line-soft) 65%,transparent);overflow:hidden;margin-top:7px;}
.fr-progress > i{display:block;height:100%;width:0;background:linear-gradient(90deg,var(--primary-color),color-mix(in oklab,var(--primary-color) 65%,#22c55e));border-radius:999px;}
.fr-card-foot{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-top:12px;color:var(--muted-color);font-size:12px;}
.fr-next{display:inline-flex;align-items:center;gap:6px;color:var(--primary-color);font-weight:900;}
.fr-card-countline{display:inline-flex;align-items:center;gap:7px;color:var(--ink);font-weight:900;font-size:12.5px;}
.fr-card-countline i{color:var(--primary-color);}
.fr-group-section{grid-column:1/-1;margin-bottom:8px;}
.fr-group-title{display:flex;align-items:center;gap:12px;margin:4px 0 12px;color:var(--ink);font-weight:950;letter-spacing:-.01em;}
.fr-group-title:after{content:"";height:1px;flex:1;background:linear-gradient(90deg,color-mix(in oklab,var(--primary-color) 42%,var(--line-soft)),transparent);}
.fr-group-title .bubble{width:34px;height:34px;border-radius:12px;display:grid;place-items:center;background:color-mix(in oklab,var(--primary-color) 12%,transparent);color:var(--primary-color);border:1px solid color-mix(in oklab,var(--primary-color) 22%,var(--line-soft));}
.fr-group-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(245px,1fr));gap:12px;}
.fr-group-empty{border:1px dashed var(--line-soft);border-radius:16px;padding:18px;text-align:center;color:var(--muted-color);background:color-mix(in oklab,var(--surface) 96%,var(--page-hover));}
.fr-card-muted{opacity:.86;}
.fr-card-muted:before{background:var(--muted-color);opacity:.45;}
.fr-card-facility:before{background:linear-gradient(180deg,#7c3aed,#06b6d4);}
.fr-card-facility .fr-card-icon{background:color-mix(in oklab,#7c3aed 12%,transparent);color:#7c3aed;border-color:color-mix(in oklab,#7c3aed 20%,var(--line-soft));}
.fr-final-wrap{display:grid;grid-template-columns:minmax(260px,.85fr) minmax(0,1.5fr);gap:14px;align-items:start;}
.fr-overview-card{border:1px solid var(--line-strong);border-radius:18px;background:linear-gradient(135deg,color-mix(in oklab,var(--primary-color) 9%,var(--surface)),var(--surface));box-shadow:var(--shadow-2);padding:16px;}
.fr-overview-card h5{margin:0;font-weight:900;color:var(--ink);}
.fr-overview-list{display:grid;gap:9px;margin-top:14px;}
.fr-overview-row{display:flex;align-items:center;justify-content:space-between;gap:10px;border:1px solid var(--line-soft);background:var(--surface);border-radius:13px;padding:10px 12px;}
.fr-overview-row span{font-size:12px;color:var(--muted-color);font-weight:800;}
.fr-overview-row b{font-size:14px;color:var(--ink);}
.fr-post-list{display:grid;gap:10px;}
.fr-post-card{border:1px solid var(--line-strong);border-radius:16px;background:var(--surface);box-shadow:var(--shadow-2);padding:14px;display:flex;align-items:flex-start;justify-content:space-between;gap:12px;cursor:pointer;transition:.14s ease;}
.fr-post-card:hover{transform:translateY(-2px);border-color:color-mix(in oklab,var(--primary-color) 35%,var(--line-strong));}
.fr-post-title{font-weight:900;color:var(--ink);}
.fr-post-meta{display:flex;flex-wrap:wrap;gap:7px;margin-top:8px;}
.fr-post-meta .chip{display:inline-flex;align-items:center;gap:6px;border:1px solid var(--line-soft);border-radius:999px;padding:5px 8px;font-size:11.5px;color:var(--muted-color);background:color-mix(in oklab,var(--surface) 94%,var(--page-hover));}
.fr-empty-state{border:1px dashed var(--line-soft);border-radius:18px;padding:34px 16px;text-align:center;color:var(--muted-color);background:color-mix(in oklab,var(--surface) 96%,var(--page-hover));}
.fr-empty-state i{font-size:34px;opacity:.7;margin-bottom:8px;display:block;}
.fr-skeleton-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(235px,1fr));gap:12px;}
.fr-skeleton-card{height:150px;border-radius:18px;border:1px solid var(--line-strong);background:var(--surface);padding:14px;}
.fr-skeleton-card .fr-skel-line:first-child{height:18px;width:65%;}
.fr-skeleton-card .fr-skel-line:nth-child(2){width:90%;}
.fr-skeleton-card .fr-skel-line:nth-child(3){width:55%;}
html.theme-dark .fr-drill-card,html.theme-dark .fr-post-card,html.theme-dark .fr-overview-card,html.theme-dark .fr-mini-stat{box-shadow:none;}
@media (max-width: 992px){.fr-summary-strip{grid-template-columns:repeat(2,minmax(0,1fr));}.fr-final-wrap{grid-template-columns:1fr;}.fr-page-head{flex-direction:column;}.fr-grid-head{flex-direction:column;}}
@media (max-width: 576px){.fr-card-grid{grid-template-columns:1fr;}.fr-summary-strip{grid-template-columns:1fr;}.fr-post-card{flex-direction:column;}.fr-grid-body{padding:10px;}.fr-breadcrumb{padding:10px;}.fr-page-head{padding:14px;}}


/* =========================
 * Search-only minimal UI tweaks
 * ========================= */
.fr-search-panel{padding:12px;border-radius:16px;}
.fr-search-wrap{max-width:760px;margin:0 auto;}
.fr-search-row{display:flex;align-items:center;justify-content:center;gap:10px;}
.fr-search-box{width:min(100%,560px);}
.fr-search-box .form-control{border-radius:12px;}
.fr-search-btn{border-radius:12px;padding-left:16px;padding-right:16px;}
.fr-drill-card:after{display:none!important;content:none!important;}
@media (max-width:768px){.fr-search-row{flex-direction:column;align-items:stretch}.fr-search-box{width:100%;}.fr-search-btn{width:100%;}}

/* Responsive toolbar */
@media (max-width: 768px){
  .fq-toolbar .d-flex{flex-direction:column;gap:12px !important}
  .fq-toolbar .position-relative{min-width:100% !important}
  .toolbar-buttons{display:flex;gap:8px;flex-wrap:wrap}
  .toolbar-buttons .btn{flex:1;min-width:120px}
  .kv{grid-template-columns: 1fr;}
  .fac-tabbtn .nm{max-width: 180px;}
}
</style>
@endpush

@section('content')
<div class="fq-wrap">

  {{-- Loading Overlay --}}
  <div id="globalLoading" class="loading-overlay" style="display:none;">
    @include('partials.overlay')
  </div>


  {{-- Tabs --}}
  <ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item">
      <a class="nav-link active" data-bs-toggle="tab" href="#tab-posts" role="tab" aria-selected="true">
        <i class="fa-solid fa-chart-simple me-2"></i>Feedback Result
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#tab-help" role="tab" aria-selected="false">
        <i class="fa-solid fa-circle-question me-2"></i>Help
      </a>
    </li>
  </ul>

  <div class="tab-content mb-3">

    {{-- POSTS TAB --}}
    <div class="tab-pane fade show active" id="tab-posts" role="tabpanel">

      {{-- Search Toolbar --}}
      <div class="fq-toolbar panel fr-search-panel mb-3">
        <div class="fr-search-wrap">
          <div class="fr-search-row">
            <div class="position-relative fr-search-box">
              <input id="searchInput" type="search" class="form-control ps-5" placeholder="Search course, year, semester, section, subject or post…">
              <i class="fa fa-search position-absolute" style="left:12px;top:50%;transform:translateY(-50%);opacity:.58;"></i>
            </div>

            <button id="btnSearch" class="btn btn-primary fr-search-btn" type="button">
              <i class="fa fa-search me-1"></i>Search
            </button>
          </div>
        </div>

        {{-- Hidden filters kept only for existing JS/API compatibility. Not shown in UI. --}}
        <div class="d-none" aria-hidden="true">
          <select id="f_department"><option value="">All</option></select>
          <select id="f_course"><option value="">All</option></select>
          <select id="f_semester"><option value="">All</option></select>
          <select id="f_subject"><option value="">All</option></select>
          <select id="f_section"><option value="">All</option></select>
          <select id="f_academic_year"><option value="">All</option></select>
          <select id="f_year"><option value="">All</option></select>
        </div>
      </div>

      {{-- Grid Explorer --}}
      <div class="fr-grid-shell">
        <div class="fr-grid-head">
          <div>
            <div class="fr-grid-title" id="gridStageTitle">Courses</div>
            <div class="fr-grid-subtitle" id="gridStageSubtitle">Select a course to view academic years.</div>
          </div>
          <div class="text-muted small" id="resultsInfo-posts">—</div>
        </div>

        <div id="gridBreadcrumb" class="fr-breadcrumb"></div>

        <div class="fr-grid-body">
          <div id="gridCards" class="fr-card-grid">
            <div class="fr-skeleton-grid" style="grid-column:1/-1;">
              <div class="fr-skeleton-card"><div class="fr-skel-line"></div><div class="fr-skel-line"></div><div class="fr-skel-line"></div></div>
              <div class="fr-skeleton-card"><div class="fr-skel-line"></div><div class="fr-skel-line"></div><div class="fr-skel-line"></div></div>
              <div class="fr-skeleton-card"><div class="fr-skel-line"></div><div class="fr-skel-line"></div><div class="fr-skel-line"></div></div>
            </div>
          </div>

          <div id="empty-posts" class="fr-empty-state" style="display:none;">
            <i class="fa-solid fa-chart-simple"></i>
            <div class="fw-bold">No feedback results found for the current filters.</div>
            <div class="small mt-1">If a course has no submitted feedback, the final card will show 0 values.</div>
          </div>
        </div>
      </div>

      {{-- Keep old ids available for old safe-null code paths --}}
      <select id="perPage" class="d-none"><option selected>20</option></select>
      <div id="tbody-posts" class="d-none"></div>
      <ul id="pager-posts" class="d-none"></ul>
    </div>

    {{-- HELP TAB --}}
    <div class="tab-pane fade" id="tab-help" role="tabpanel">
      <div class="card table-wrap">
        <div class="card-body">
          <div class="fw-bold mb-2"><i class="fa fa-circle-info me-2"></i>How this page works</div>
          <ul class="mb-0 text-muted">
            <li>First select a <b>Course</b>, then an <b>Academic Year</b>, then <b>Semester</b>.</li>
            <li>If sections are available, select a <b>Section</b>; then choose a <b>Subject</b> or the <b>Facility</b> card.</li>
            <li>Clicking a subject or Facility card opens the detailed result modal directly. No extra overview screen is shown.</li>
            <li>The detailed result modal/export layout is unchanged.</li>
          </ul>
        </div>
      </div>
    </div>

  </div>
</div>

{{-- Detail Modal --}}
<div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="detailTitle"><i class="fa fa-eye me-2"></i>Feedback Post Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">

        <div class="detail-head mb-3">
          <div>
            <div class="fw-bold" id="detailPostName">—</div>
            {{-- ✅ Removed UUID in details --}}
          </div>

          <div class="detail-meta">
            <span class="chip"><i class="fa fa-calendar"></i> Publish: <span id="detailPublish">—</span></span>
            {{-- ✅ Removed Expire chip --}}
            {{-- ✅ UPDATED: show X out of Y (Participated out of Eligible) --}}
            <span class="chip"><i class="fa fa-users"></i> Given: <b id="detailParticipated">0</b></span>
          </div>
        </div>

        <div class="kv mb-3">
          <div class="k">Department</div><div class="v" id="detailDept">—</div>
          <div class="k">Course</div><div class="v" id="detailCourse">—</div>
          <div class="k">Semester</div><div class="v" id="detailSem">—</div>
          <div class="k">Subject</div><div class="v" id="detailSub">—</div>
          <div class="k">Subject Code</div><div class="v" id="detailSubCode">—</div>
          <div class="k">Section</div><div class="v" id="detailSec">—</div>
          <div class="k">Academic Year</div><div class="v" id="detailAcadYear">—</div>
          <div class="k">Year</div><div class="v" id="detailYear">—</div>
        </div>

        <div class="mb-3" id="detailDescWrap" style="display:none;">
          <div class="fw-semibold mb-1"><i class="fa fa-align-left me-2"></i>Description</div>
          <div class="p-3" style="border:1px solid var(--line-strong);border-radius:14px;background:var(--surface);" id="detailDesc">—</div>
        </div>

        {{-- ✅ NEW: Attendance % filter (in modal, top of faculty/overall tabs) --}}
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
          <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="pill"><i class="fa-solid fa-filter"></i>Attendance Filter</span>
            <div class="input-group" style="max-width:260px;">
              <span class="input-group-text"><i class="fa-solid fa-percent"></i></span>
              <input id="attMin" type="number" class="form-control" min="0" max="100" step="1" placeholder="Min attendance (e.g. 75)">
              <button id="btnAttApply" class="btn btn-outline-primary" type="button" title="Apply">
                <i class="fa fa-check"></i>
              </button>
              <button id="btnAttClear" class="btn btn-light" type="button" title="Clear">
                <i class="fa fa-rotate-left"></i>
              </button>
            </div>
          </div>
          <div class="small text-muted">
            Only students with attendance <b>&ge;</b> this percentage will be included.
          </div>
        </div>

        {{-- Faculty Tabs (auto) --}}
        <div id="detailFacultyTabs" class="fac-tabsbar mb-2" style="display:none;"></div>

        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
          <div class="fw-semibold" id="detailMatrixTitle"><i class="fa fa-table me-2"></i>Question-wise Grade Distribution</div>
          <div class="d-flex align-items-center gap-2 flex-wrap">
            <button type="button" id="btnExport" class="btn btn-outline-primary">
              <i class="fa-solid fa-file-export me-1"></i>Export
            </button>
            <div class="position-relative" style="min-width:320px;">
              <input id="detailSearch" type="search" class="form-control ps-5" placeholder="Search question…">
              <i class="fa fa-search position-absolute" style="left:12px;top:50%;transform:translateY(-50%);opacity:.6;"></i>
            </div>
          </div>
        </div>

        <div id="detailQuestions">
          <div class="text-center text-muted" style="padding:22px;">—</div>
        </div>

      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>

{{-- Export Modal --}}
<div class="modal fade" id="exportModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title"><i class="fa-solid fa-file-export me-2"></i>Export Feedback Result</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="fw-bold mb-1" id="exportPostTitle">—</div>
        <div class="text-muted small mb-3" id="exportPostSub">—</div>

        <div class="fw-semibold mb-2"><i class="fa fa-square-check me-2"></i>Select what to export</div>
        <div id="exportTargets" class="export-pills">
          {{-- filled by JS --}}
        </div>

        <div class="alert alert-light mt-3 mb-0" style="border:1px dashed var(--line-soft);border-radius:14px;">
          <div class="small text-muted">
            <i class="fa fa-circle-info me-1"></i>
            ✅ CSV: Top academic details first, then blocks: <b>Overall</b>, then selected <b>Faculty</b> blocks (same format).<br>
            PDF is generated as pages: <b>Overall first</b>, then selected faculties.<br>
            <b>Note:</b> Exports use <b>grade counts</b> (NO percentages).
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="btnDoCsv" class="btn btn-outline-primary">
          <i class="fa-solid fa-file-csv me-1"></i>Export CSV
        </button>
        <button type="button" id="btnDoPdf" class="btn btn-primary">
          <i class="fa-solid fa-file-pdf me-1"></i>Export PDF
        </button>
      </div>

    </div>
  </div>
</div>

{{-- Toasts --}}
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:2000">
  <div id="toastSuccess" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body" id="toastSuccessText">Done</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
  <div id="toastError" class="toast align-items-center text-bg-danger border-0 mt-2" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body" id="toastErrorText">Something went wrong</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

{{-- PDF libs (client-side) --}}
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf-autotable@3.8.2/dist/jspdf.plugin.autotable.min.js"></script>

<script>
(() => {
  if (window.__FEEDBACK_RESULTS_MODULE_INIT__) return;
  window.__FEEDBACK_RESULTS_MODULE_INIT__ = true;

  const $ = (id) => document.getElementById(id);
  const debounce = (fn, ms=300) => { let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), ms); }; };

  const API = {
    results: (params) => `/api/feedback-results${params ? ('?' + params) : ''}`,
  };

  function esc(str){
    return (str ?? '').toString().replace(/[&<>"']/g, s => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    }[s]));
  }

  async function fetchWithTimeout(url, opts={}, ms=20000){
    const ctrl = new AbortController();
    const t = setTimeout(()=>ctrl.abort(), ms);
    try{ return await fetch(url, { ...opts, signal: ctrl.signal }); }
    finally { clearTimeout(t); }
  }

  function prettyDate(s){
    const v = (s ?? '').toString().trim();
    return v ? v : '—';
  }
  function safeText(s){ return (s ?? '').toString().trim(); }
  function toNum(v){ const n = Number(v); return Number.isFinite(n) ? n : 0; }
  function pct(part, total){
    const p = toNum(part), t = toNum(total);
    if (!t) return 0;
    return Math.max(0, Math.min(100, Math.round((p / t) * 100)));
  }
  function plural(n, one, many){ return `${n} ${Number(n) === 1 ? one : many}`; }

  function normalizeCountMap(counts){
    const c = counts || {};
    const get = (k) => {
      const v = (c[k] ?? c[String(k)] ?? c[Number(k)] ?? 0);
      const n = Number(v);
      return Number.isFinite(n) ? n : 0;
    };
    return { '5': get(5), '4': get(4), '3': get(3), '2': get(2), '1': get(1) };
  }

  function computeAvgGradeFromCounts(counts){
    const c = normalizeCountMap(counts || {});
    const total = (c['5'] + c['4'] + c['3'] + c['2'] + c['1']);
    if (!total) return { avg: null, total: 0 };
    const sum = (5*c['5']) + (4*c['4']) + (3*c['3']) + (2*c['2']) + (1*c['1']);
    return { avg: Math.round((sum/total) * 100) / 100, total };
  }

  function downloadBlob(filename, mime, content){
    const blob = new Blob([content], { type: mime });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    a.remove();
    URL.revokeObjectURL(url);
  }

  function csvEscape(v){
    const s = (v ?? '').toString();
    if (/[",\n\r]/.test(s)) return `"${s.replace(/"/g,'""')}"`;
    return s;
  }

  function nowStamp(){
    const d = new Date();
    const pad = (n)=> String(n).padStart(2,'0');
    return `${d.getFullYear()}${pad(d.getMonth()+1)}${pad(d.getDate())}_${pad(d.getHours())}${pad(d.getMinutes())}`;
  }

  function slugify(s){
    return (s||'')
      .toString()
      .toLowerCase()
      .replace(/[^a-z0-9]+/g,'-')
      .replace(/(^-|-$)/g,'')
      .slice(0,40) || 'feedback';
  }

  document.addEventListener('DOMContentLoaded', () => {
    const token = sessionStorage.getItem('token') || localStorage.getItem('token') || '';
    if (!token) { window.location.href = '/'; return; }

    const toastOkEl = $('toastSuccess');
    const toastErrEl = $('toastError');
    const toastOk = toastOkEl ? new bootstrap.Toast(toastOkEl) : null;
    const toastErr = toastErrEl ? new bootstrap.Toast(toastErrEl) : null;
    const ok = (m) => { const el=$('toastSuccessText'); if(el) el.textContent=m||'Done'; toastOk && toastOk.show(); };
    const err = (m) => { const el=$('toastErrorText'); if(el) el.textContent=m||'Something went wrong'; toastErr && toastErr.show(); };

    const authHeaders = () => ({
      'Authorization': 'Bearer ' + token,
      'Accept': 'application/json'
    });

    const searchInput = $('searchInput');
    const btnSearch = $('btnSearch');
    const gridSummary = $('gridSummary');
    const gridCards = $('gridCards');
    const gridBreadcrumb = $('gridBreadcrumb');
    const gridStageTitle = $('gridStageTitle');
    const gridStageSubtitle = $('gridStageSubtitle');
    const empty = $('empty-posts');
    const info = $('resultsInfo-posts');
    const tbody = $('tbody-posts');

    const fDept   = $('f_department');
    const fCourse = $('f_course');
    const fSem    = $('f_semester');
    const fSub    = $('f_subject');
    const fSec    = $('f_section');
    const fAcad   = $('f_academic_year');
    const fYear   = $('f_year');

    const detailModalEl = $('detailModal');
    const detailTitle = $('detailTitle');
    const detailPostName = $('detailPostName');
    const detailPublish = $('detailPublish');
    const detailParticipated = $('detailParticipated');
    const detailDept = $('detailDept');
    const detailCourse = $('detailCourse');
    const detailSem = $('detailSem');
    const detailSub = $('detailSub');
    const detailSubCode = $('detailSubCode');
    const detailSec = $('detailSec');
    const detailAcadYear = $('detailAcadYear');
    const detailYear = $('detailYear');
    const detailDescWrap = $('detailDescWrap');
    const detailDesc = $('detailDesc');
    const detailFacultyTabs = $('detailFacultyTabs');
    const detailMatrixTitle = $('detailMatrixTitle');
    const detailQuestions = $('detailQuestions');
    const detailSearch = $('detailSearch');
    const attMin = $('attMin');
    const btnAttApply = $('btnAttApply');
    const btnAttClear = $('btnAttClear');

    const btnExport = $('btnExport');
    const exportModalEl = $('exportModal');
    const exportPostTitle = $('exportPostTitle');
    const exportPostSub = $('exportPostSub');
    const exportTargets = $('exportTargets');
    const btnDoCsv = $('btnDoCsv');
    const btnDoPdf = $('btnDoPdf');

    function cleanupOrphanBackdrops(){
      if (document.querySelector('.modal.show')) return;
      document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
      document.body.classList.remove('modal-open');
      document.body.style.removeProperty('overflow');
      document.body.style.removeProperty('padding-right');
    }

    const detailModal = detailModalEl ? bootstrap.Modal.getOrCreateInstance(detailModalEl) : null;
    const exportModal = exportModalEl ? bootstrap.Modal.getOrCreateInstance(exportModalEl) : null;
    [detailModalEl, exportModalEl].forEach(elm => {
      if (!elm) return;
      elm.addEventListener('hidden.bs.modal', () => setTimeout(cleanupOrphanBackdrops, 0));
    });

    const NO_YEAR = '__no_academic_year__';
    const ALL_SECTIONS = '__all_sections__';
    const NO_SECTION = '__no_section__';
    const FACILITY = '__facility__';

    const state = {
      q: '',
      filters: {
        department_id: '',
        course_id: '',
        semester_id: '',
        subject_id: '',
        section_id: '',
        academic_year: '',
        year: '',
        min_attendance: ''
      },
      rawHierarchy: [],
      postIndex: new Map(),
      flatPosts: [],
      courses: [],
      courseMap: new Map(),
      optionCache: {
        deptMap: new Map(), courseMap: new Map(), semMap: new Map(),
        subMap: new Map(), subTitleMap: new Map(), secMap: new Map(),
        acadYearMap: new Map(), yearMap: new Map()
      },
      view: { stage: 'courses', courseKey: null, yearKey: null, semKey: null, sectionKey: null, groupKey: null },
      lastDetailPostKey: null,
      activeFacultyId: 0,
      activeFacultyName: 'Overall',
      availableFaculty: [],
      lastDetailCtx: null,
      lastDetailPost: null,
      lastDetailQuestions: [],
      pinnedDetailPostKey: null,
    };

    detailModalEl?.addEventListener('hidden.bs.modal', () => {
      state.pinnedDetailPostKey = null;
      state.lastDetailPostKey = null;
    });

    function setEmpty(show){ if (empty) empty.style.display = show ? '' : 'none'; }

    function setLoadingGrid(){
      setEmpty(false);
      if (!gridCards) return;
      gridCards.className = 'fr-card-grid';
      gridCards.innerHTML = `
        <div class="fr-skeleton-grid" style="grid-column:1/-1;">
          <div class="fr-skeleton-card"><div class="fr-skel-line"></div><div class="fr-skel-line"></div><div class="fr-skel-line"></div></div>
          <div class="fr-skeleton-card"><div class="fr-skel-line"></div><div class="fr-skel-line"></div><div class="fr-skel-line"></div></div>
          <div class="fr-skeleton-card"><div class="fr-skel-line"></div><div class="fr-skel-line"></div><div class="fr-skel-line"></div></div>
        </div>`;
      if (tbody) tbody.innerHTML = '';
    }

    function clampAttendance(v){
      const s = (v ?? '').toString().trim();
      if (s === '') return '';
      const n = Number(s);
      if (!Number.isFinite(n)) return '';
      return String(Math.max(0, Math.min(100, Math.round(n))));
    }

    function buildParams(){
      const p = new URLSearchParams();
      const f = state.filters;
      if (f.department_id) p.set('department_id', f.department_id);
      if (f.course_id) p.set('course_id', f.course_id);
      if (f.semester_id) p.set('semester_id', f.semester_id);
      if (f.subject_id) p.set('subject_id', f.subject_id);
      if (f.section_id) p.set('section_id', f.section_id);
      if (f.academic_year) p.set('academic_year', f.academic_year);
      if (f.year) p.set('year', f.year);
      if (f.min_attendance !== '') p.set('min_attendance', f.min_attendance);
      return p.toString();
    }

    function makeKey(prefix, ...parts){
      return `${prefix}:${parts.map(x => (x === null || x === undefined || x === '') ? 'none' : String(x)).join(':')}`;
    }

    function isFacilitySubject(sub){
      const name = safeText(sub?.subject_name);
      const code = safeText(sub?.subject_code);
      const id = safeText(sub?.subject_id);
      const type = safeText(sub?.type || sub?.subject_type || sub?.category || sub?.feedback_type).toLowerCase();
      return (!name && !code && !id) || type.includes('facility') || name.toLowerCase() === 'facility';
    }

    function postEligible(post){ return toNum(post?.eligible_students ?? post?.total_students ?? post?.assigned_students ?? post?.total_assigned ?? 0); }
    function postParticipated(post){ return toNum(post?.participated_students ?? post?.submitted_students ?? post?.feedback_given ?? post?.given_students ?? 0); }

    function numericIdList(v){
      if (!Array.isArray(v)) return [];
      return Array.from(new Set(v.map(x => Number(x)).filter(x => Number.isFinite(x) && x > 0)));
    }

    function postAssignedIds(post){
      return numericIdList(post?.assigned_student_ids || post?.student_ids || post?.assigned_students_ids || []);
    }

    function postGivenIds(post){
      return numericIdList(post?.given_student_ids || post?.participated_student_ids || post?.submitted_student_ids || []);
    }

    function uniquePosts(posts){
      const map = new Map();
      (posts || []).forEach(p => {
        const id = p?.feedback_post_id ?? p?.post_id ?? p?.feedback_post_uuid ?? JSON.stringify(p);
        if (!map.has(String(id))) map.set(String(id), p);
      });
      return Array.from(map.values());
    }

    function calcStats(posts, opts={}){
      const useUnique = !!opts.unique;
      // For section-scoped common posts, the same feedback_post_id can appear once per
      // section with different assigned_student_ids. In unique-student mode we must keep
      // those section clones so the progress bar is calculated from the real student union.
      const arr = useUnique ? (posts || []) : uniquePosts(posts || []);
      let participated = 0;
      let eligible = 0;

      if (useUnique){
        const assignedSet = new Set();
        const givenSet = new Set();
        arr.forEach(p => {
          postAssignedIds(p).forEach(id => assignedSet.add(String(id)));
          postGivenIds(p).forEach(id => givenSet.add(String(id)));
        });
        const fallbackParticipated = arr.reduce((s,p)=>s + postParticipated(p), 0);
        const fallbackEligible = arr.reduce((s,p)=>s + postEligible(p), 0);
        if (assignedSet.size && (givenSet.size || fallbackParticipated === 0)){
          eligible = assignedSet.size;
          participated = givenSet.size;
        } else if (assignedSet.size && givenSet.size){
          eligible = assignedSet.size;
          participated = givenSet.size;
        } else {
          participated = fallbackParticipated;
          eligible = fallbackEligible;
        }
      } else {
        participated = arr.reduce((s,p)=>s + postParticipated(p), 0);
        eligible = arr.reduce((s,p)=>s + postEligible(p), 0);
      }

      return {
        posts: arr.length,
        participated,
        eligible,
        percent: pct(participated, eligible),
        hasFeedback: participated > 0,
      };
    }

    function filterPostsByYear(posts, yearKey){
      if (!yearKey || yearKey === NO_YEAR) return (posts || []).filter(p => !safeText(p?.academic_year));
      return (posts || []).filter(p => safeText(p?.academic_year) === String(yearKey));
    }

    function pushPostEverywhere(post, nodes){
      nodes.forEach(node => {
        if (!node.posts) node.posts = [];
        node.posts.push(post);
      });
    }

    function rebuildFromHierarchy(){
      state.postIndex.clear();
      state.flatPosts = [];
      state.courses = [];
      state.courseMap.clear();

      const hierarchy = Array.isArray(state.rawHierarchy) ? state.rawHierarchy : [];
      const deptSet = new Map();
      const courseSet = new Map();
      const semSet = new Map();
      const subSet = new Map();
      const subTitleSet = new Map();
      const secSet = new Map();
      const acadYearSet = new Map();
      const yearSet = new Map();

      const getCourse = (dept, course) => {
        const cId = course?.course_id ?? course?.id ?? '';
        const cName = safeText(course?.course_name ?? course?.name ?? course?.title) || (cId ? `Course #${cId}` : 'Unnamed Course');
        const dId = dept?.department_id ?? dept?.id ?? '';
        const dName = safeText(dept?.department_name ?? dept?.name) || (dId ? `Dept #${dId}` : '—');
        const key = makeKey('course', cId || cName, dId || dName);
        if (!state.courseMap.has(key)){
          state.courseMap.set(key, {
            key, course_id: cId || null, course_name: cName,
            department_id: dId || null, department_name: dName,
            semesters: new Map(), posts: []
          });
        }
        return state.courseMap.get(key);
      };

      const getSemester = (courseNode, sem) => {
        const sId = sem?.semester_id ?? sem?.id ?? '';
        const sName = safeText(sem?.semester_name ?? sem?.name ?? sem?.title) || (sId ? `Semester #${sId}` : 'Semester');
        const key = makeKey('sem', courseNode.key, sId || sName);
        if (!courseNode.semesters.has(key)){
          courseNode.semesters.set(key, {
            key, semester_id: sId || null, semester_name: sName,
            courseKey: courseNode.key,
            sections: new Map(), groups: new Map(), posts: []
          });
        }
        return courseNode.semesters.get(key);
      };

      const getSection = (semNode, sec) => {
        const secId = sec?.section_id ?? sec?.id ?? '';
        const secName = safeText(sec?.section_name ?? sec?.name ?? sec?.title);
        const key = secId || secName ? makeKey('section', semNode.key, secId || secName) : makeKey('section', semNode.key, NO_SECTION);
        if (!semNode.sections.has(key)){
          semNode.sections.set(key, {
            key, section_id: secId || null,
            section_name: secName || 'No Section',
            semesterKey: semNode.key,
            groups: new Map(), posts: []
          });
        }
        return semNode.sections.get(key);
      };

      const getGroup = (container, sub, semKey, sectionKey) => {
        const facility = isFacilitySubject(sub);
        const subId = sub?.subject_id ?? '';
        const subName = safeText(sub?.subject_name);
        const subCode = safeText(sub?.subject_code);
        const label = facility ? 'Facility' : (subName || subCode || 'Unnamed Subject');
        const key = facility ? makeKey('group', semKey, sectionKey || ALL_SECTIONS, FACILITY) : makeKey('group', semKey, sectionKey || ALL_SECTIONS, subId || subCode || subName);
        if (!container.groups.has(key)){
          container.groups.set(key, {
            key,
            isFacility: facility,
            subject_id: facility ? null : (subId || null),
            subject_name: facility ? 'Facility' : label,
            subject_code: facility ? '' : subCode,
            posts: []
          });
        }
        return container.groups.get(key);
      };

      hierarchy.forEach(dept => {
        const dId = dept?.department_id ?? dept?.id ?? '';
        const dName = safeText(dept?.department_name ?? dept?.name) || (dId ? `Dept #${dId}` : '—');
        if (dId !== '') deptSet.set(String(dId), dName);

        (dept?.courses || []).forEach(course => {
          const courseNode = getCourse(dept, course);
          if (courseNode.course_id !== null && courseNode.course_id !== undefined && courseNode.course_id !== '') courseSet.set(String(courseNode.course_id), courseNode.course_name);

          (course?.semesters || []).forEach(sem => {
            const semNode = getSemester(courseNode, sem);
            if (semNode.semester_id !== null && semNode.semester_id !== undefined && semNode.semester_id !== '') semSet.set(String(semNode.semester_id), semNode.semester_name);

            (sem?.subjects || []).forEach(sub => {
              const facility = isFacilitySubject(sub);
              const subId = sub?.subject_id ?? '';
              const subName = safeText(sub?.subject_name);
              const subCode = safeText(sub?.subject_code);
              if (!facility && subId !== ''){
                subSet.set(String(subId), subName || subCode || `Subject #${subId}`);
                subTitleSet.set(String(subId), subCode || '');
              }

              const sections = Array.isArray(sub?.sections) && sub.sections.length ? sub.sections : [{ section_id: null, section_name: '', feedback_posts: sub?.feedback_posts || [] }];

              sections.forEach(sec => {
                const sectionNode = getSection(semNode, sec);
                if (sectionNode.section_id !== null && sectionNode.section_id !== undefined && sectionNode.section_id !== '') secSet.set(String(sectionNode.section_id), sectionNode.section_name);

                const semGroup = getGroup(semNode, sub, semNode.key, ALL_SECTIONS);
                const secGroup = getGroup(sectionNode, sub, semNode.key, sectionNode.key);

                (sec?.feedback_posts || []).forEach(post => {
                  const postId = post?.feedback_post_id;
                  if (!postId) return;

                  const academicYear = safeText(post?.academic_year);
                  const year = safeText(post?.year);
                  if (academicYear) acadYearSet.set(academicYear, academicYear);
                  if (year) yearSet.set(year, year);

                  const ctx = {
                    department_id: courseNode.department_id,
                    department_name: courseNode.department_name,
                    course_id: courseNode.course_id,
                    course_name: courseNode.course_name,
                    semester_id: semNode.semester_id,
                    semester_name: semNode.semester_name,
                    subject_id: facility ? null : (sub?.subject_id ?? null),
                    subject_code: facility ? '' : (sub?.subject_code ?? null),
                    subject_name: facility ? 'Facility' : (subName || null),
                    section_id: sectionNode.section_id,
                    section_name: sectionNode.section_name === 'No Section' ? null : sectionNode.section_name,
                    is_facility: facility,
                  };

                  post.__ctx = ctx;
                  post.__isFacility = facility;
                  post.__courseKey = courseNode.key;
                  post.__semKey = semNode.key;
                  post.__sectionKey = sectionNode.key;
                  post.__groupKey = secGroup.key;

                  const key = String(post?.result_key ?? post?.feedback_result_key ?? postId);
                  post.__resultKey = key;
                  state.postIndex.set(key, { ctx, post });

                  state.flatPosts.push({
                    key,
                    post_id: postId,
                    uuid: post?.feedback_post_uuid ?? '',
                    title: post?.title ?? '—',
                    short_title: post?.short_title ?? '',
                    publish_at: post?.publish_at ?? '',
                    expire_at: post?.expire_at ?? '',
                    description: post?.description ?? '',
                    academic_year: post?.academic_year ?? '',
                    year: post?.year ?? '',
                    participated_students: postParticipated(post),
                    eligible_students: postEligible(post),
                    ctx
                  });

                  pushPostEverywhere(post, [courseNode, semNode, sectionNode, semGroup, secGroup]);
                });
              });
            });
          });
        });
      });

      state.courses = Array.from(state.courseMap.values()).sort((a,b)=>String(a.course_name).localeCompare(String(b.course_name)));

      state.optionCache.deptMap = deptSet;
      state.optionCache.courseMap = courseSet;
      state.optionCache.semMap = semSet;
      state.optionCache.subMap = subSet;
      state.optionCache.subTitleMap = subTitleSet;
      state.optionCache.secMap = secSet;
      state.optionCache.acadYearMap = acadYearSet;
      state.optionCache.yearMap = yearSet;

      fillSelects();
    }

    function fillSelects(){
      const fillSel = (sel, map, titleMap=null) => {
        if (!sel) return;
        const cur = sel.value || '';
        sel.innerHTML = `<option value="">All</option>` + Array.from(map.entries())
          .sort((a,b)=>String(a[1]).localeCompare(String(b[1])))
          .map(([id,name]) => {
            const t = titleMap ? (titleMap.get(String(id)) || '') : '';
            return `<option value="${esc(id)}"${t ? ` title="${esc(t)}"` : ''}>${esc(name)}</option>`;
          }).join('');
        if (cur && Array.from(map.keys()).map(String).includes(String(cur))) sel.value = cur;
      };
      fillSel(fDept, state.optionCache.deptMap);
      fillSel(fCourse, state.optionCache.courseMap);
      fillSel(fSem, state.optionCache.semMap);
      fillSel(fSub, state.optionCache.subMap, state.optionCache.subTitleMap);
      fillSel(fSec, state.optionCache.secMap);
      fillSel(fAcad, state.optionCache.acadYearMap);
      fillSel(fYear, state.optionCache.yearMap);
    }

    function resetView(stage='courses'){
      state.view = { stage, courseKey: null, yearKey: null, semKey: null, sectionKey: null, groupKey: null };
    }

    function getCourse(){ return state.courseMap.get(state.view.courseKey) || null; }
    function getSem(){
      const c = getCourse();
      return c?.semesters?.get(state.view.semKey) || null;
    }
    function getSection(){
      const s = getSem();
      return s?.sections?.get(state.view.sectionKey) || null;
    }

    function matchesSearchText(text){
      const q = (state.q || '').toLowerCase().trim();
      if (!q) return true;
      return (text || '').toString().toLowerCase().includes(q);
    }

    function statHtml(stats, mode='plain'){
      const s = stats || calcStats([]);
      if (mode === 'semester'){
        return `
          <div class="fr-progress-line"><span>Unique student response</span><b>${esc(s.percent)}%</b></div>
          <div class="fr-progress"><i style="width:${esc(s.percent)}%"></i></div>`;
      }
      if (mode === 'final'){
        return `
          <div class="fr-card-stats">
            <div class="fr-stat-pill"><b>${esc(s.participated)}</b><span>Given</span></div>
            <div class="fr-stat-pill"><b>${esc(s.eligible)}</b><span>Assigned</span></div>
          </div>
          <div class="fr-progress-line"><span>Completion</span><b>${esc(s.percent)}%</b></div>
          <div class="fr-progress"><i style="width:${esc(s.percent)}%"></i></div>`;
      }
      return '';
    }

    function cardHtml({key, type, title, meta, stats, icon='fa-folder-open', muted=false, facility=false, foot='Open', mode='plain'}){
      const s = stats || calcStats([]);
      const countLine = mode === 'final'
        ? `<span class="fr-card-countline"><i class="fa-solid fa-users"></i>${esc(s.participated)} out of ${esc(s.eligible)} given</span>`
        : `<span>${esc(foot)}</span>`;

      return `
        <div class="fr-drill-card ${muted ? 'fr-card-muted' : ''} ${facility ? 'fr-card-facility' : ''}" data-drill="${esc(type)}" data-key="${esc(key)}" title="Open ${esc(title)}">
          <div class="fr-card-top">
            <div class="fr-card-icon"><i class="fa-solid ${esc(icon)}"></i></div>
            <div class="min-w-0">
              <div class="fr-card-title">${esc(title)}</div>
              <div class="fr-card-meta">${meta || '—'}</div>
            </div>
          </div>
          ${statHtml(s, mode)}
          <div class="fr-card-foot">
            ${countLine}
            <span class="fr-next">${esc(foot)} <i class="fa fa-arrow-right"></i></span>
          </div>
        </div>`;
    }

    function renderSummary(){
      if (!gridSummary) return;
      const stats = calcStats(state.flatPosts.map(x => state.postIndex.get(String(x.key))?.post).filter(Boolean));
      gridSummary.innerHTML = `
        <div class="fr-mini-stat"><div class="lbl">Courses</div><div class="val">${esc(state.courses.length)}</div><div class="hint">available</div></div>
        <div class="fr-mini-stat"><div class="lbl">Feedback Posts</div><div class="val">${esc(stats.posts)}</div><div class="hint">total</div></div>
        <div class="fr-mini-stat"><div class="lbl">Participated</div><div class="val">${esc(stats.participated)}</div><div class="hint">out of ${esc(stats.eligible)}</div></div>
        <div class="fr-mini-stat"><div class="lbl">Completion</div><div class="val">${esc(stats.percent)}%</div><div class="hint">overall</div></div>`;
    }

    function renderBreadcrumb(){
      if (!gridBreadcrumb) return;
      const c = getCourse();
      const s = getSem();
      const sec = getSection();
      const parts = [{stage:'courses', label:'Courses', icon:'fa-house'}];
      if (c) parts.push({stage:'years', label:c.course_name, icon:'fa-graduation-cap'});
      if (state.view.yearKey) parts.push({stage:'semesters', label: state.view.yearKey === NO_YEAR ? 'No Academic Year' : state.view.yearKey, icon:'fa-calendar-days'});
      if (s) parts.push({stage:'sections', label:s.semester_name, icon:'fa-layer-group'});
      if (sec && state.view.sectionKey && state.view.sectionKey !== ALL_SECTIONS) parts.push({stage:'groups', label:sec.section_name, icon:'fa-users'});
      

      gridBreadcrumb.innerHTML = parts.map((p, idx) => `
        ${idx ? '<span class="fr-bc-sep"><i class="fa fa-chevron-right"></i></span>' : ''}
        <button type="button" class="fr-bc-btn" data-nav-stage="${esc(p.stage)}"><i class="fa-solid ${esc(p.icon)}"></i>${esc(p.label)}</button>
      `).join('');
    }

    function setStageText(title, subtitle){
      if (gridStageTitle) gridStageTitle.textContent = title;
      if (gridStageSubtitle) gridStageSubtitle.textContent = subtitle;
    }

    function academicYearsForCourse(course){
      const years = new Set();
      (course?.posts || []).forEach(p => {
        const y = safeText(p?.academic_year);
        if (y) years.add(y);
      });
      const out = Array.from(years).sort((a,b)=>b.localeCompare(a));
      return out.length ? out : [NO_YEAR];
    }

    function hasRealSections(sem){
      const sections = Array.from(sem?.sections?.values() || []);
      return sections.some(s => s.section_id || (safeText(s.section_name) && s.section_name !== 'No Section'));
    }

    function groupsForSelected(){
      const sem = getSem();
      if (!sem) return [];
      const section = getSection();
      const from = section && state.view.sectionKey !== ALL_SECTIONS ? section.groups : sem.groups;
      const groups = Array.from(from?.values() || []);
      const hasFacility = groups.some(g => g.isFacility || g.key.includes(FACILITY));
      if (!hasFacility){
        groups.push({ key: makeKey('group', sem.key, state.view.sectionKey || ALL_SECTIONS, FACILITY), isFacility: true, subject_id: null, subject_name: 'Facility', subject_code: '', posts: [] });
      }
      return groups.sort((a,b)=>{
        if (a.isFacility && !b.isFacility) return 1;
        if (!a.isFacility && b.isFacility) return -1;
        return String(a.subject_name).localeCompare(String(b.subject_name));
      });
    }

    function currentGroup(){
      return groupsForSelected().find(g => g.key === state.view.groupKey) || null;
    }

    function renderCourses(){
      setStageText('Courses', 'Select a course to view available academic years. Counts are shown only in the final subject/facility step.');
      const cards = state.courses
        .filter(c => matchesSearchText(`${c.course_name} ${c.department_name}`))
        .map(c => cardHtml({
          key: c.key,
          type: 'course',
          title: c.course_name,
          meta: `<i class="fa-solid fa-building me-1"></i>${esc(c.department_name || '—')}`,
          stats: calcStats(c.posts),
          icon: 'fa-graduation-cap',
          muted: !calcStats(c.posts).posts,
          foot: 'View years',
          mode: 'plain'
        })).join('');
      return cards;
    }

    function renderYears(){
      const c = getCourse();
      if (!c) { resetView(); return renderCourses(); }
      setStageText('Academic Years', `Course: ${c.course_name}. Select an academic year to view semesters.`);
      return academicYearsForCourse(c)
        .filter(y => matchesSearchText(y === NO_YEAR ? 'No Academic Year' : y))
        .map(y => {
          const posts = y === NO_YEAR ? (c.posts || []).filter(p => !safeText(p?.academic_year)) : filterPostsByYear(c.posts, y);
          const stats = calcStats(posts);
          return cardHtml({
            key: y,
            type: 'year',
            title: y === NO_YEAR ? 'No Academic Year Found' : y,
            meta: y === NO_YEAR ? 'No feedback post is mapped with academic year yet.' : 'Open semesters for this academic year',
            stats,
            icon: 'fa-calendar-days',
            muted: !stats.posts,
            foot: 'View semesters',
            mode: 'plain'
          });
        }).join('');
    }

    function renderSemesters(){
      const c = getCourse();
      if (!c) { resetView(); return renderCourses(); }
      const yearLabel = state.view.yearKey === NO_YEAR ? 'No Academic Year' : state.view.yearKey;
      setStageText('Semesters', `${c.course_name} / ${yearLabel}. Select a semester.`);
      const semesters = Array.from(c.semesters.values()).sort((a,b)=>String(a.semester_name).localeCompare(String(b.semester_name), undefined, {numeric:true}));
      return semesters
        .filter(s => matchesSearchText(s.semester_name))
        .map(s => {
          const posts = filterPostsByYear(s.posts, state.view.yearKey);
          const stats = calcStats(posts, { unique: true });
          return cardHtml({
            key: s.key,
            type: 'semester',
            title: s.semester_name,
            meta: hasRealSections(s) ? 'Sections available after this step' : 'No section split, subjects open directly',
            stats,
            icon: 'fa-layer-group',
            muted: !stats.posts,
            foot: hasRealSections(s) ? 'View sections' : 'View feedbacks',
            mode: 'semester'
          });
        }).join('');
    }

    function renderSections(){
      const sem = getSem();
      if (!sem) { state.view.stage = 'semesters'; return renderSemesters(); }
      setStageText('Sections', `${sem.semester_name}. Select a section to view subjects and facility.`);
      const hasReal = hasRealSections(sem);
      const sections = Array.from(sem.sections.values())
        .filter(s => !hasReal || s.section_id || (safeText(s.section_name) && s.section_name !== 'No Section'))
        .sort((a,b)=>String(a.section_name).localeCompare(String(b.section_name), undefined, {numeric:true}));
      return sections
        .filter(s => matchesSearchText(s.section_name))
        .map(s => {
          const posts = filterPostsByYear(s.posts, state.view.yearKey);
          const stats = calcStats(posts, { unique: true });
          return cardHtml({
            key: s.key,
            type: 'section',
            title: s.section_name || 'No Section',
            meta: 'Open feedbacks mapped to this section',
            stats,
            icon: 'fa-users',
            muted: !stats.posts,
            foot: 'View feedbacks',
            mode: 'plain'
          });
        }).join('');
    }

    function renderGroups(){
      const sem = getSem();
      if (!sem) { state.view.stage = 'semesters'; return renderSemesters(); }
      const sec = getSection();
      const title = sec && state.view.sectionKey !== ALL_SECTIONS ? sec.section_name : 'All Sections';
      setStageText('Faculty & Facility Feedbacks', `${sem.semester_name} / ${title}. Click a final card to open the detailed result modal directly.`);

      const allGroups = groupsForSelected()
        .filter(g => matchesSearchText(`${g.subject_name} ${g.subject_code}`));

      const subjectGroups = allGroups.filter(g => !g.isFacility);
      const facilityGroups = allGroups.filter(g => g.isFacility);

      const makeGroupCard = (g) => {
        const posts = filterPostsByYear(g.posts, state.view.yearKey);
        const stats = calcStats(posts, { unique: true });
        const label = g.isFacility ? 'Facility' : (g.subject_name || g.subject_code || 'Subject');
        const code = g.subject_code ? ` <span class="pill ms-1">${esc(g.subject_code)}</span>` : '';
        return cardHtml({
          key: g.key,
          type: 'group',
          title: label,
          meta: g.isFacility ? 'Facility feedback result' : `Faculty feedback result${code}`,
          stats,
          icon: g.isFacility ? 'fa-building-circle-check' : 'fa-user-graduate',
          facility: g.isFacility,
          muted: !stats.posts,
          foot: 'Open Result',
          mode: 'final'
        });
      };

      const subjectHtml = subjectGroups.length
        ? subjectGroups.map(makeGroupCard).join('')
        : `<div class="fr-group-empty"><i class="fa-regular fa-folder-open me-1"></i>No faculty feedback subject found here.</div>`;

      const facilityHtml = facilityGroups.some(g => calcStats(filterPostsByYear(g.posts, state.view.yearKey), { unique: true }).posts)
        ? facilityGroups.map(makeGroupCard).join('')
        : `<div class="fr-group-empty"><i class="fa-regular fa-building me-1"></i>No facility feedback post mapped here.</div>`;

      return `
        <div class="fr-group-section">
          <div class="fr-group-title"><span class="bubble"><i class="fa-solid fa-user-graduate"></i></span><span>Faculty Feedbacks</span></div>
          <div class="fr-group-grid">${subjectHtml}</div>
        </div>
        <div class="fr-group-section">
          <div class="fr-group-title"><span class="bubble"><i class="fa-solid fa-building-circle-check"></i></span><span>Facility Feedback</span></div>
          <div class="fr-group-grid">${facilityHtml}</div>
        </div>`;
    }

    function renderGrid(){
      renderSummary();
      renderBreadcrumb();

      let html = '';
      if (state.view.stage === 'courses') html = renderCourses();
      else if (state.view.stage === 'years') html = renderYears();
      else if (state.view.stage === 'semesters') html = renderSemesters();
      else if (state.view.stage === 'sections') html = renderSections();
      else if (state.view.stage === 'groups') html = renderGroups();

      if (info){
        const stats = calcStats(state.flatPosts.map(x => state.postIndex.get(String(x.key))?.post).filter(Boolean));
        info.textContent = `${state.courses.length} course(s) • ${stats.posts} feedback post(s) • ${stats.percent}% completion`;
      }

      if (!html || !html.trim()){
        if (gridCards) gridCards.innerHTML = '';
        setEmpty(true);
        return;
      }
      setEmpty(false);
      if (gridCards){
        gridCards.className = 'fr-card-grid';
        gridCards.innerHTML = html;
      }
    }

    /* ===========================
     * Detail modal - unchanged layout
     * =========================== */
    function collectFacultyFromQuestions(questions){
      const map = new Map();
      (questions || []).forEach(q => {
        (Array.isArray(q.faculty) ? q.faculty : []).forEach(f => {
          const id = Number(f?.faculty_id);
          if (!Number.isFinite(id)) return;
          const name = (f?.faculty_name ?? '').toString().trim() || ('Faculty #' + id);
          const short = (f?.name_short_form ?? f?.faculty_name_short_form ?? f?.short_name ?? f?.short_form ?? '').toString().trim();
          const key = String(id);
          const prev = map.get(key);
          if (!prev) map.set(key, { name, short });
          else map.set(key, { name: prev.name || name, short: prev.short || short });
        });
      });
      if (!map.has('0')) map.set('0', { name: 'Overall', short: 'Overall' });
      const out = [{ id: '0', name: 'Overall', short: 'Overall' }];
      Array.from(map.entries()).filter(([id]) => id !== '0')
        .sort((a,b)=>String(a[1]?.name || '').localeCompare(String(b[1]?.name || '')))
        .forEach(([id,obj]) => out.push({ id, name: obj?.name || ('Faculty #' + id), short: obj?.short || '' }));
      return out;
    }

    function renderFacultyTabs(){
      if (!detailFacultyTabs) return;
      const list = Array.isArray(state.availableFaculty) ? state.availableFaculty : [];
      if (list.length <= 1){ detailFacultyTabs.style.display = 'none'; return; }
      detailFacultyTabs.style.display = '';
      detailFacultyTabs.innerHTML = list.map(f => {
        const active = String(f.id) === String(state.activeFacultyId);
        const isOverall = String(f.id) === '0';
        const fullName = String(f.name || '');
        const displayName = isOverall ? 'Overall' : (String(f.short || '').trim() || fullName || ('Faculty #' + f.id));
        return `
          <button type="button" class="fac-tabbtn ${active ? 'active' : ''}" data-fid="${esc(String(f.id))}" data-fname="${esc(String(f.name || ''))}">
            <i class="fa-solid ${isOverall ? 'fa-star' : 'fa-user-tie'}"></i>
            <span class="nm" title="${esc(fullName || displayName)}">${esc(displayName)}</span>
          </button>`;
      }).join('');
    }

    function facultyRowForQuestion(q, fid){
      const arr = Array.isArray(q?.faculty) ? q.faculty : [];
      return arr.find(x => String(x?.faculty_id) === String(fid)) || null;
    }

    function renderMatrixHtml({ questions, mode, fid, facName }){
      const rowCounts = [];
      const totalCounts = {'5':0,'4':0,'3':0,'2':0,'1':0};
      const rowsHtml = (questions || []).map((q, idx) => {
        const qTitle = (q.question_title || '—').toString();
        const searchable = (qTitle || '').toLowerCase();
        let dist = null;
        if (mode === 'overall') dist = q.distribution || null;
        else {
          const f = facultyRowForQuestion(q, fid);
          dist = (f && f.distribution) ? f.distribution : null;
        }
        const counts = dist ? normalizeCountMap(dist.counts || {}) : {'5':0,'4':0,'3':0,'2':0,'1':0};
        const total = dist ? Number(dist.total || 0) : 0;
        ['5','4','3','2','1'].forEach(k => totalCounts[k] += Number(counts[k] || 0));
        rowCounts.push({ idx: idx+1, question: qTitle, counts, total });
        const cell = (k) => total ? esc(String(counts[k] ?? 0)) : '—';
        return `
          <tr data-qrow="1" data-qsearch="${esc(searchable)}">
            <td class="qtext">${esc((idx+1) + '. ' + qTitle)}</td>
            <td class="col5">${cell('5')}</td>
            <td class="col4">${cell('4')}</td>
            <td class="col3">${cell('3')}</td>
            <td class="col2">${cell('2')}</td>
            <td class="col1">${cell('1')}</td>
          </tr>`;
      }).join('');

      const agg = computeAvgGradeFromCounts(totalCounts);
      const avgGrade = agg.avg;
      const totalRatings = agg.total;
      const avgRowHtml = `
        <tr class="avgrow">
          <td class="qtext" colspan="6">
            <b>Avg grade:</b>
            ${avgGrade !== null ? `<b>${esc(String(avgGrade))}</b> / 5` : '—'}
            <span class="submeta">This is based on all submitted ratings.</span>
          </td>
        </tr>`;
      const html = `
        <div class="matrix-wrap">
          <table class="matrix">
            <thead>
              <tr>
                <th class="qcol">Question</th>
                <th class="col5">Outstanding [5]</th>
                <th class="col4">Excellent [4]</th>
                <th class="col3">Good [3]</th>
                <th class="col2">Fair [2]</th>
                <th class="col1">Not Satisfactory [1]</th>
              </tr>
            </thead>
            <tbody>${rowsHtml}${avgRowHtml}</tbody>
          </table>
        </div>`;
      return { html, rowCounts, totalCounts, avgGrade, totalRatings };
    }

    function renderDetail(postKey){
      const found = state.postIndex.get(String(postKey));
      if (!found) return;
      state.lastDetailPostKey = String(postKey);
      const ctx = found.ctx || {};
      const post = found.post || {};
      state.lastDetailCtx = ctx;
      state.lastDetailPost = post;
      const postName = (post.title || '—').toString();

      if (detailTitle) detailTitle.innerHTML = `<i class="fa fa-eye me-2"></i>${esc(postName)}`;
      if (detailPostName) detailPostName.textContent = postName;
      if (detailPublish) detailPublish.textContent = prettyDate(post.publish_at);
      if (detailDept) detailDept.textContent = ctx.department_name ?? '—';
      if (detailCourse) detailCourse.textContent = ctx.course_name ?? '—';
      if (detailSem) detailSem.textContent = ctx.semester_name ?? '—';
      if (detailSub) detailSub.textContent = (ctx.is_facility ? 'Facility' : (ctx.subject_name ?? '—')) || '—';
      if (detailSubCode) detailSubCode.textContent = (ctx.is_facility ? '—' : (ctx.subject_code ?? '—')) || '—';
      if (detailSec) detailSec.textContent = ctx.section_name ?? '—';
      if (detailAcadYear) detailAcadYear.textContent = (post.academic_year ?? '—') || '—';
      if (detailYear) detailYear.textContent = (post.year ?? '—') || '—';

      const participated = postParticipated(post);
      const eligible = postEligible(post);
      if (detailParticipated) detailParticipated.textContent = `${participated} out of ${eligible} (${pct(participated, eligible)}%)`;
      if (attMin) attMin.value = (state.filters.min_attendance ?? '');

      const desc = (post.description ?? '').toString().trim();
      if (detailDescWrap && detailDesc){
        if (desc){ detailDescWrap.style.display = ''; detailDesc.innerHTML = desc; }
        else { detailDescWrap.style.display = 'none'; detailDesc.innerHTML = ''; }
      }

      const questions = Array.isArray(post.questions) ? post.questions : [];
      state.lastDetailQuestions = questions;
      if (!questions.length){
        if (detailMatrixTitle) detailMatrixTitle.innerHTML = `<i class="fa fa-table me-2"></i>Question-wise Grade Distribution`;
        if (detailFacultyTabs) detailFacultyTabs.style.display = 'none';
        detailQuestions.innerHTML = `<div class="text-center text-muted" style="padding:22px;">No question ratings found for this post.</div>`;
        return;
      }

      state.availableFaculty = collectFacultyFromQuestions(questions);
      if (!state.availableFaculty.find(x => String(x.id) === String(state.activeFacultyId))){
        state.activeFacultyId = 0;
        state.activeFacultyName = 'Overall';
      } else {
        const f = state.availableFaculty.find(x => String(x.id) === String(state.activeFacultyId));
        state.activeFacultyName = f?.name || 'Overall';
      }
      renderFacultyTabs();

      const fid = String(state.activeFacultyId);
      const resetDetailSearch = () => { if (detailSearch) detailSearch.value = ''; };
      if (fid === '0'){
        if (detailMatrixTitle) detailMatrixTitle.innerHTML = `<i class="fa fa-table me-2"></i>Question-wise Grade Distribution <span class="pill ms-2"><i class="fa fa-star"></i>Overall</span>`;
        const { html } = renderMatrixHtml({ questions, mode: 'overall', fid: '0', facName: 'Overall' });
        detailQuestions.innerHTML = html;
        resetDetailSearch();
        return;
      }

      const facName = state.activeFacultyName || 'Faculty';
      if (detailMatrixTitle) detailMatrixTitle.innerHTML = `<i class="fa fa-table me-2"></i>Question-wise Grade Distribution <span class="pill ms-2"><i class="fa fa-user-tie"></i>${esc(facName)}</span>`;
      const { html } = renderMatrixHtml({ questions, mode: 'faculty', fid, facName });
      detailQuestions.innerHTML = html;
      resetDetailSearch();
    }

    detailSearch?.addEventListener('input', debounce(() => {
      const q = (detailSearch.value || '').toLowerCase().trim();
      const nodes = detailQuestions?.querySelectorAll('tr[data-qrow="1"]') || [];
      nodes.forEach(tr => {
        const hay = (tr.getAttribute('data-qsearch') || '').toLowerCase();
        tr.style.display = (!q || hay.includes(q)) ? '' : 'none';
      });
    }, 200));

    /* ===========================
     * Export - same behavior
     * =========================== */
    function buildBasicMetaRows(post, ctx){
      const participated = postParticipated(post);
      const eligible = postEligible(post);
      return [
        ['Feedback Post', safeText(post?.title)],
        ['Department', safeText(ctx?.department_name)],
        ['Course', safeText(ctx?.course_name)],
        ['Semester', safeText(ctx?.semester_name)],
        ['Subject', ctx?.is_facility ? 'Facility' : safeText(ctx?.subject_name)],
        ['Subject Code', ctx?.is_facility ? '—' : safeText(ctx?.subject_code)],
        ['Section', safeText(ctx?.section_name)],
        ['Academic Year', safeText(post?.academic_year)],
        ['Year', safeText(post?.year)],
        ['Publish', safeText(post?.publish_at)],
        ['Participated', `${participated} out of ${eligible} (${pct(participated, eligible)}%)`],
      ];
    }

    function exportModalFill(){
      if (!exportTargets) return;
      const post = state.lastDetailPost || {};
      const ctx = state.lastDetailCtx || {};
      if (exportPostTitle) exportPostTitle.textContent = safeText(post.title) || '—';
      if (exportPostSub) exportPostSub.textContent = `${safeText(ctx.department_name) || '—'} / ${safeText(ctx.course_name) || '—'} / ${ctx.is_facility ? 'Facility' : (safeText(ctx.subject_code) || '—')} / ${ctx.is_facility ? 'Facility' : (safeText(ctx.subject_name) || '—')}`;

      const list = Array.isArray(state.availableFaculty) ? state.availableFaculty : [{id:'0',name:'Overall',short:'Overall'}];
      const curActive = String(state.activeFacultyId || '0');
      exportTargets.innerHTML = list.map(f => {
        const isOverall = String(f.id) === '0';
        const checked = isOverall || (!isOverall && String(f.id) === curActive);
        const fullName = String(f.name || '');
        const displayName = isOverall ? 'Overall' : (String(f.short || '').trim() || fullName || ('Faculty #' + f.id));
        return `
          <label class="export-pill" title="${esc(fullName || displayName)}">
            <input type="checkbox" class="form-check-input m-0" data-fid="${esc(String(f.id))}" ${checked ? 'checked' : ''}>
            <i class="fa-solid ${isOverall ? 'fa-star' : 'fa-user-tie'}"></i>
            <span>${esc(displayName)}</span>
          </label>`;
      }).join('');
    }

    function getSelectedExportTargets(){
      const nodes = exportTargets?.querySelectorAll('input[type="checkbox"][data-fid]') || [];
      const selected = [];
      nodes.forEach(ch => { if (ch.checked) selected.push(String(ch.getAttribute('data-fid'))); });
      const list = Array.isArray(state.availableFaculty) ? state.availableFaculty : [];
      const ordered = [];
      if (selected.includes('0')) ordered.push('0');
      list.filter(x => String(x.id) !== '0').forEach(x => { if (selected.includes(String(x.id))) ordered.push(String(x.id)); });
      return ordered;
    }

    function buildExportMatrixForTarget(fid){
      const questions = Array.isArray(state.lastDetailQuestions) ? state.lastDetailQuestions : [];
      const isOverall = String(fid) === '0';
      const facObj = state.availableFaculty.find(x => String(x.id) === String(fid));
      const facName = isOverall ? 'Overall' : (facObj?.name || ('Faculty #' + fid));
      const facShort = isOverall ? 'Overall' : (String(facObj?.short || '').trim() || facName);
      const matrix = renderMatrixHtml({ questions, mode: isOverall ? 'overall' : 'faculty', fid: String(fid), facName });
      return { facName, facShort, isOverall, matrix };
    }

    function doExportCsv(){
      const selected = getSelectedExportTargets();
      if (!selected.length){ err('Select at least one target (Overall/Faculty)'); return; }
      const post = state.lastDetailPost || {};
      const ctx = state.lastDetailCtx || {};
      const metaRows = buildBasicMetaRows(post, ctx);
      const lines = [];
      lines.push([ 'Academic Details', '' ].map(csvEscape).join(','));
      metaRows.forEach(([k,v]) => lines.push([k, v ?? '—'].map(csvEscape).join(',')));
      lines.push(''); lines.push('');
      const ordered = [];
      if (selected.includes('0')) ordered.push('0');
      selected.filter(x => x !== '0').forEach(x => ordered.push(x));
      const tableHeader = ['Q.No','Question','Outstanding [5] (Count)','Excellent [4] (Count)','Good [3] (Count)','Fair [2] (Count)','Not Satisfactory [1] (Count)'];
      ordered.forEach((fid, idx) => {
        const { facShort, isOverall, matrix } = buildExportMatrixForTarget(fid);
        const sheetLabel = isOverall ? 'Overall' : `Faculty: ${facShort}`;
        lines.push([sheetLabel].map(csvEscape).join(','));
        lines.push(tableHeader.map(csvEscape).join(','));
        (matrix.rowCounts || []).forEach(r => {
          const c = normalizeCountMap(r.counts || {});
          lines.push([String(r.idx), r.question, String(c['5'] ?? 0), String(c['4'] ?? 0), String(c['3'] ?? 0), String(c['2'] ?? 0), String(c['1'] ?? 0)].map(csvEscape).join(','));
        });
        const avg = matrix.avgGrade;
        lines.push(['', `Avg Grade: ${avg !== null ? avg : '—'}/5`, '', '', '', '', ''].map(csvEscape).join(','));
        if (idx !== ordered.length - 1){ lines.push(''); lines.push(''); }
      });
      downloadBlob(`feedback_export_${slugify(post?.title)}_${nowStamp()}.csv`, 'text/csv;charset=utf-8', lines.join('\n'));
      ok('CSV exported');
    }

    function doExportPdf(){
      const selected = getSelectedExportTargets();
      if (!selected.length){ err('Select at least one target (Overall/Faculty)'); return; }
      const post = state.lastDetailPost || {};
      const ctx = state.lastDetailCtx || {};
      const title = safeText(post.title) || 'Feedback Result';
      const metaRows = buildBasicMetaRows(post, ctx);
      const { jsPDF } = (window.jspdf || {});
      if (!jsPDF){ err('PDF library not loaded'); return; }
      const doc = new jsPDF({ orientation:'landscape', unit:'pt', format:'a4' });
      const pageW = doc.internal.pageSize.getWidth();
      const margin = 32;
      let headerBottomY = 112;

      function addHeaderBlock(pageTitle){
        const contentW = pageW - (margin * 2);
        const colGap = 24;
        const colW = (contentW - colGap) / 2;
        const labelW = 82;
        const valueGap = 6;
        const lineH = 10;
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(14);
        const titleLines = doc.splitTextToSize(String(pageTitle || 'Feedback Result'), contentW);
        doc.text(titleLines, margin, 36);
        let y = 36 + (titleLines.length * 14) + 10;

        function drawMetaCell(label, value, x, topY, width){
          const safeLabel = String(label || '');
          const safeValue = String(value ?? '—') || '—';
          const valueW = Math.max(60, width - labelW - valueGap);
          doc.setFont('helvetica', 'bold'); doc.setFontSize(9); doc.text(`${safeLabel}:`, x, topY);
          doc.setFont('helvetica', 'normal');
          const valueLines = doc.splitTextToSize(safeValue, valueW);
          doc.text(valueLines, x + labelW + valueGap, topY);
          return Math.max(1, valueLines.length) * lineH;
        }
        for (let i = 0; i < metaRows.length; i += 2){
          const left = metaRows[i] || ['', '—'];
          const right = metaRows[i + 1] || null;
          const leftHeight = drawMetaCell(left[0], left[1], margin, y, colW);
          let rightHeight = 0;
          if (right) rightHeight = drawMetaCell(right[0], right[1], margin + colW + colGap, y, colW);
          y += Math.max(leftHeight, rightHeight, lineH) + 8;
        }
        headerBottomY = y + 4;
        doc.setDrawColor(200);
        doc.line(margin, headerBottomY, pageW - margin, headerBottomY);
      }

      function addMatrixTable(matrix, sheetLabel){
        const head = [['Question','Outstanding [5]','Excellent [4]','Good [3]','Fair [2]','Not Satisfactory [1]']];
        const body = (matrix.rowCounts || []).map(r => {
          const c = normalizeCountMap(r.counts || {});
          return [`${r.idx}. ${r.question}`, String(c['5']), String(c['4']), String(c['3']), String(c['2']), String(c['1'])];
        });
        const avg = matrix.avgGrade;
        body.push([`Avg Grade: ${avg !== null ? avg : '—'}/5`, '', '', '', '', '']);
        const sheetLabelY = headerBottomY + 20;
        const tableStartY = sheetLabelY + 12;
        doc.setFont('helvetica', 'bold'); doc.setFontSize(11); doc.text(sheetLabel, margin, sheetLabelY);
        doc.autoTable({
          startY: tableStartY,
          head,
          body,
          theme: 'grid',
          styles: { font: 'helvetica', fontSize: 9, cellPadding: 6, overflow: 'linebreak' },
          headStyles: { fontStyle: 'bold' },
          columnStyles: { 0: { cellWidth: 420 }, 1: { halign:'center' }, 2: { halign:'center' }, 3: { halign:'center' }, 4: { halign:'center' }, 5: { halign:'center' } },
          margin: { left: margin, right: margin },
          didParseCell: (data) => {
            if (data.section === 'body' && data.row.index === body.length - 1){
              data.cell.styles.fillColor = [245,245,245];
              data.cell.styles.fontStyle = 'bold';
            }
          }
        });
      }

      const ordered = [];
      if (selected.includes('0')) ordered.push('0');
      selected.filter(x => x !== '0').forEach(x => ordered.push(x));
      ordered.forEach((fid, idx) => {
        if (idx > 0) doc.addPage();
        const target = buildExportMatrixForTarget(fid);
        const sheetLabel = target.isOverall ? 'Overall' : `Faculty: ${target.facShort}`;
        addHeaderBlock(title);
        addMatrixTable(target.matrix, sheetLabel);
      });
      doc.save(`feedback_export_${slugify(post?.title)}_${nowStamp()}.pdf`);
      ok('PDF exported');
    }

    btnExport?.addEventListener('click', () => {
      if (!state.lastDetailPostKey){ err('Open a feedback post first'); return; }
      exportModalFill();
      exportModal && exportModal.show();
    });
    btnDoCsv?.addEventListener('click', () => { try{ doExportCsv(); exportModal && exportModal.hide(); }catch(ex){ err(ex?.message || 'CSV export failed'); } });
    btnDoPdf?.addEventListener('click', () => { try{ doExportPdf(); exportModal && exportModal.hide(); }catch(ex){ err(ex?.message || 'PDF export failed'); } });

    async function loadResults(){
      setLoadingGrid();
      try{
        const qs = buildParams();
        const res = await fetchWithTimeout(API.results(qs), { headers: authHeaders() }, 25000);
        if (res.status === 401 || res.status === 403){ window.location.href = '/'; return; }
        const js = await res.json().catch(()=> ({}));
        if (!res.ok || js.success === false) throw new Error(js?.message || 'Failed to load');
        state.rawHierarchy = Array.isArray(js.data) ? js.data : [];
        rebuildFromHierarchy();
        renderGrid();
      }catch(ex){
        if (gridCards) gridCards.innerHTML = '';
        setEmpty(true);
        err(ex?.name === 'AbortError' ? 'Request timed out' : (ex.message || 'Failed'));
      }
    }

    function syncFiltersFromInputs(){
      state.filters.department_id = (fDept?.value || '').trim();
      state.filters.course_id = (fCourse?.value || '').trim();
      state.filters.semester_id = (fSem?.value || '').trim();
      state.filters.subject_id = (fSub?.value || '').trim();
      state.filters.section_id = (fSec?.value || '').trim();
      state.filters.academic_year = (fAcad?.value || '').trim();
      state.filters.year = (fYear?.value || '').trim();
    }

    function applyTopFilters(){
      syncFiltersFromInputs();
      resetView('courses');
      loadResults();
    }

    [fDept, fCourse, fSem, fSub, fSec, fAcad, fYear].forEach(el => el?.addEventListener('change', () => applyTopFilters()));

    function resetAll(){
      state.q = '';
      if (searchInput) searchInput.value = '';
      state.filters = { department_id:'', course_id:'', semester_id:'', subject_id:'', section_id:'', academic_year:'', year:'', min_attendance:'' };
      [fDept, fCourse, fSem, fSub, fSec, fAcad, fYear].forEach(el => { if (el) el.value = ''; });
      if (attMin) attMin.value = '';
      resetView('courses');
      loadResults();
    }


    function runSearch(){
      state.q = (searchInput?.value || '').trim();
      renderGrid();
    }

    btnSearch?.addEventListener('click', runSearch);
    searchInput?.addEventListener('keydown', (e) => {
      if (e.key === 'Enter'){
        e.preventDefault();
        runSearch();
      }
    });

    function newestPostFromGroup(group){
      const posts = uniquePosts(filterPostsByYear(group?.posts || [], state.view.yearKey));
      posts.sort((a,b) => {
        const ad = Date.parse(a?.publish_at || a?.updated_at || a?.created_at || '') || 0;
        const bd = Date.parse(b?.publish_at || b?.updated_at || b?.created_at || '') || 0;
        if (bd !== ad) return bd - ad;
        return toNum(b?.feedback_post_id) - toNum(a?.feedback_post_id);
      });
      return posts[0] || null;
    }

    function openPostModal(postKey){
      if (!postKey) return;
      state.activeFacultyId = 0;
      state.activeFacultyName = 'Overall';
      state.pinnedDetailPostKey = String(postKey);
      state.lastDetailPostKey = String(postKey);
      renderDetail(postKey);
      if (detailSearch) detailSearch.value = '';
      detailModal && detailModal.show();
    }

    document.addEventListener('click', (e) => {
      const nav = e.target.closest('[data-nav-stage]');
      if (nav){
        e.preventDefault();
        const stage = nav.getAttribute('data-nav-stage');
        if (stage === 'courses') resetView('courses');
        else if (stage === 'years') state.view = { ...state.view, stage:'years', yearKey:null, semKey:null, sectionKey:null, groupKey:null };
        else if (stage === 'semesters') state.view = { ...state.view, stage:'semesters', semKey:null, sectionKey:null, groupKey:null };
        else if (stage === 'sections') state.view = { ...state.view, stage: hasRealSections(getSem()) ? 'sections' : 'groups', sectionKey:null, groupKey:null };
        else if (stage === 'groups') state.view = { ...state.view, stage:'groups', groupKey:null };
        renderGrid();
        return;
      }

      const drill = e.target.closest('[data-drill][data-key]');
      if (drill){
        e.preventDefault();
        const type = drill.getAttribute('data-drill');
        const key = drill.getAttribute('data-key');
        if (type === 'course') state.view = { stage:'years', courseKey:key, yearKey:null, semKey:null, sectionKey:null, groupKey:null };
        else if (type === 'year') state.view = { ...state.view, stage:'semesters', yearKey:key, semKey:null, sectionKey:null, groupKey:null };
        else if (type === 'semester'){
          state.view = { ...state.view, semKey:key, sectionKey:null, groupKey:null };
          state.view.stage = hasRealSections(getSem()) ? 'sections' : 'groups';
          if (state.view.stage === 'groups') state.view.sectionKey = ALL_SECTIONS;
        }
        else if (type === 'section') {
          state.view = { ...state.view, stage:'groups', sectionKey:key, groupKey:null };
          renderGrid();
          return;
        }
        else if (type === 'group') {
          state.view = { ...state.view, groupKey:key };
          const group = currentGroup();
          const post = newestPostFromGroup(group);
          if (!post){
            err('No feedback post found for this selection');
            return;
          }
          openPostModal(String(post.__resultKey ?? post.result_key ?? post.feedback_result_key ?? post.feedback_post_id));
          return;
        }
        renderGrid();
        return;
      }

      const btn = e.target.closest('[data-action="view"][data-post]');
      if (btn){
        e.preventDefault();
        e.stopPropagation();
        const postKey = btn.getAttribute('data-post');
        openPostModal(postKey);
      }
    });

    document.addEventListener('click', (e) => {
      const b = e.target.closest('#detailFacultyTabs .fac-tabbtn[data-fid]');
      if (!b) return;
      const fid = b.dataset.fid;
      const fname = b.dataset.fname || 'Faculty';
      state.activeFacultyId = Number(fid || 0);
      state.activeFacultyName = fname;
      detailFacultyTabs?.querySelectorAll('.fac-tabbtn').forEach(x => x.classList.toggle('active', x === b));
      if (state.lastDetailPostKey) renderDetail(state.lastDetailPostKey);
    });

    async function applyAttendanceFromModal(){
      const val = clampAttendance(attMin ? attMin.value : '');
      state.filters.min_attendance = val;
      if (attMin) attMin.value = val;
      const keepPost = state.pinnedDetailPostKey ? String(state.pinnedDetailPostKey) : null;
      await loadResults();
      if (keepPost && state.postIndex.has(keepPost)){
        state.lastDetailPostKey = keepPost;
        renderDetail(keepPost);
      } else if (keepPost){
        state.lastDetailPostKey = null;
        if (detailFacultyTabs) detailFacultyTabs.style.display = 'none';
        if (detailMatrixTitle) detailMatrixTitle.innerHTML = `<i class="fa fa-table me-2"></i>Question-wise Grade Distribution`;
        if (detailQuestions) detailQuestions.innerHTML = `<div class="text-center text-muted" style="padding:22px;">No results for this post under current attendance filter.</div>`;
      }
    }

    btnAttApply?.addEventListener('click', () => { applyAttendanceFromModal(); });
    btnAttClear?.addEventListener('click', () => {
      if (attMin) attMin.value = '';
      state.filters.min_attendance = '';
      applyAttendanceFromModal();
    });
    attMin?.addEventListener('keydown', (e) => {
      if (e.key === 'Enter'){
        e.preventDefault();
        applyAttendanceFromModal();
      }
    });

    (async () => {
      try{
        await loadResults();
        ok('Loaded feedback results');
      }catch(_){ }
    })();
  });
})();
</script>
@endpush
