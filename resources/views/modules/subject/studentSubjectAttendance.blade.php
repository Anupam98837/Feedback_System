{{-- resources/views/modules/subjects/studentSubjectAttendance.blade.php --}}
@section('title','Student Subject Attendance')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

<style>
/* Modern Variable Overrides / Definitions */
:root {
    --ssa-primary: #9E363A;
    --ssa-primary-soft: #ffeeeeff;
    --ssa-border: #f0e2e2ff;
    --ssa-bg-light: #fcf8f8ff;
    --ssa-header-bg: #f9f1f1ff;
    --ssa-sticky-bg: #ffffff;
}

/* ===== Shell ===== */
.ssa-wrap { max-width: 1400px; margin: 24px auto 60px; padding: 0 20px; }

.panel {
    background: #fff;
    border: 1px solid var(--ssa-border);
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    padding: 20px;
    transition: all 0.3s ease;
}

.ssa-card {
    border: 1px solid var(--ssa-border);
    border-radius: 12px;
    background: #fff;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.ssa-card .card-header {
    background: #fff;
    border-bottom: 1px solid var(--ssa-border);
    padding: 16px 20px;
}

.ssa-card-head{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:16px;
    flex-wrap:wrap;
}

.ssa-search{
    position:relative;
    min-width:280px;
    max-width:360px;
    width:100%;
}

.ssa-search i{
    position:absolute;
    left:12px;
    top:50%;
    transform:translateY(-50%);
    color:#94a3b8;
    pointer-events:none;
}

.ssa-search input{
    width:100%;
    height:40px;
    border-radius:10px;
    border:1px solid var(--ssa-border);
    background:#fff;
    padding:0 14px 0 38px;
    font-size:14px;
    color:#1e293b;
}

.ssa-search input:focus{
    outline:none;
    border-color:var(--ssa-primary);
    box-shadow:0 0 0 3px color-mix(in srgb, var(--ssa-primary) 12%, transparent);
}

@media(max-width: 640px){
    .ssa-search{
        max-width:none;
        min-width:100%;
    }
}

.loading-overlay {
    position: fixed; inset: 0;
    background: rgba(255, 255, 255, 0.7);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    backdrop-filter: blur(4px);
}
.loading-overlay.is-show { display: flex; }

/* ===== Toolbar ===== */
.ssa-toolbar {
    display: flex; align-items: center; justify-content: space-between;
    gap: 16px; flex-wrap: wrap;
}
.ssa-toolbar .left { display: flex; flex-direction: column; gap: 4px; }
.ssa-toolbar .right { display: flex; align-items: center; gap: 12px; }

.count-badge {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 6px 14px; border-radius: 8px;
    background: var(--ssa-primary-soft);
    color: var(--ssa-primary);
    font-weight: 600; font-size: 13px;
    border: 1px solid color-mix(in srgb, var(--ssa-primary) 20%, transparent);
}

.text-mini { font-size: 13px; color: #64748b; }

.ssa-form {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 20px;
    margin-top: 20px;
}
@media(max-width: 860px){
    .ssa-form { grid-template-columns: 1fr; }
}

.ssa-field label {
    font-size: 13px;
    font-weight: 600;
    color: #475569;
    margin-bottom: 8px;
    display: block;
}
.ssa-field select {
    width: 100%;
    height: 44px;
    border-radius: 8px;
    border: 1px solid var(--ssa-border);
    background: #fff;
    color: #1e293b;
    padding: 0 12px;
    font-size: 14px;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.ssa-field select:focus {
    outline: none;
    border-color: var(--ssa-primary);
    box-shadow: 0 0 0 3px color-mix(in srgb, var(--ssa-primary) 15%, transparent);
}

/* ===== Table Styling ===== */
.ssa-table-wrap {
    border-radius: 8px;
    overflow: auto;
    max-height: 650px;
    border: 1px solid var(--ssa-border);
}
.ssa-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 14px;
}

/* Header Aesthetics */
.ssa-table thead th {
    position: sticky;
    top: 0;
    z-index: 10;
    background: var(--ssa-header-bg);
    color: #334155;
    font-weight: 700;
    text-align: center;
    border-bottom: 1px solid var(--ssa-border);
    border-right: 1px solid var(--ssa-border);
    padding: 12px 8px;
    white-space: nowrap;
}

.ssa-table thead tr.h1 th { background: #f8fafc; font-size: 15px; color: #1e293b; }
.ssa-table thead tr.h2 th { font-size: 13px; background: #fff; }
.ssa-table thead tr.h3 th { font-size: 12px; background: var(--ssa-bg-light); }

/* ✅ NEW: Subject Type Badge (Optional/Compulsory) */
.type-pill{
    display:inline-flex;
    align-items:center;
    gap:6px;
    padding: 2px 8px;
    border-radius: 999px;
    font-size: 10.5px;
    font-weight: 800;
    border: 1px solid var(--ssa-border);
    margin-left: 6px;
}
.type-pill.compulsory{
    background: var(--ssa-primary-soft);
    color: var(--ssa-primary);
    border-color: color-mix(in srgb, var(--ssa-primary) 22%, var(--ssa-border));
}
.type-pill.optional{
    background: #fff7ed;
    color: #b45309;
    border-color: #fed7aa;
}

/* Sticky Left Columns */
.sticky-col {
    position: sticky;
    left: 0;
    z-index: 11 !important;
    background: var(--ssa-sticky-bg) !important;
    border-right: 2px solid var(--ssa-border) !important;
}
.sticky-col-2 {
    position: sticky;
    left: 70px; /* Adjusted based on Roll No width */
    z-index: 11 !important;
    background: var(--ssa-sticky-bg) !important;
    border-right: 2px solid var(--ssa-border) !important;
}

.ssa-table thead .sticky-col,
.ssa-table thead .sticky-col-2 {
    z-index: 12 !important;
}

.ssa-table tbody td {
    padding: 10px 12px;
    border-bottom: 1px solid var(--ssa-border);
    border-right: 1px solid var(--ssa-border);
    background: #fff;
    vertical-align: middle;
}

.ssa-table tbody tr:hover td {
    background: #f1f5f9 !important;
}

/* Widths */
.col-roll { width: 70px; text-align: center; font-weight: 700; color: #64748b; }
.col-name { min-width: 220px; font-weight: 600; color: #1e293b; }
.sub-col { min-width: 160px; }

/* Components in Cells */
.sub-head {
    display: flex; flex-direction: column; align-items: center; gap: 8px;
}
.sub-head .code {
    background: #e2e8f0;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.sub-head .all {
    display: flex; align-items: center; gap: 6px; font-size: 11px;
}

.cellbox {
    display: flex; align-items: center; justify-content: center; gap: 10px;
}
.att-input {
    width: 80px;
    height: 36px;
    border-radius: 6px;
    border: 1px solid var(--ssa-border);
    text-align: center;
    font-weight: 700;
    color: var(--ssa-primary);
    transition: all 0.2s;
}
.att-input:focus {
    border-color: var(--ssa-primary);
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    outline: none;
}

.cell-na {
    font-size: 11px;
    font-weight: 700;
    color: #94a3b8;
    background: #f1f5f9;
    padding: 4px 12px;
    border-radius: 4px;
    border: 1px dashed #cbd5e1;
}

/* Inputs */
input[type="checkbox"] {
    width: 18px; height: 18px;
    cursor: pointer;
    accent-color: var(--ssa-primary);
}

/* Error States */
.cell-error { background: #fff1f2 !important; }
.cell-error .att-input { border-color: #f43f5e !important; background: #fff !important; }

/* Empty state */
.empty-state {
    text-align: center; padding: 60px 20px;
    color: #64748b;
}
.empty-state i { font-size: 40px; margin-bottom: 16px; opacity: 0.5; }
.empty-state .title { font-size: 18px; font-weight: 700; color: #334155; }

.ssa-import-note{
    font-size: 13px;
    color: #64748b;
    line-height: 1.6;
}

.ssa-upload-box{
    border: 1px dashed var(--ssa-border);
    border-radius: 12px;
    background: var(--ssa-bg-light);
    padding: 16px;
}

.ssa-upload-box input[type="file"]{
    display: block;
    width: 100%;
}

.ssa-import-feedback{
    min-height: 24px;
    font-size: 13px;
}

.ssa-import-feedback.is-error{ color: #b91c1c; }
.ssa-import-feedback.is-success{ color: #166534; }

</style>
@endpush

@section('content')
<div class="ssa-wrap">

  <div id="globalLoading" class="loading-overlay">
    <div class="spinner-border text-danger" role="status"></div>
  </div>

  {{-- Top Toolbar Panel --}}
  <div class="panel mb-4">
    <div class="ssa-toolbar">
      <div class="left">
        <h5 class="mb-0 fw-bold"><i class="fa fa-clipboard-user text-danger me-2"></i>Student Subject Attendance</h5>
        <div class="text-mini" id="hintText">
          Select parameters to manage student subject mapping and current attendance percentages.
        </div>
      </div>

      <div class="right">
        <span class="count-badge" id="badgeSummary">
            <i class="fa fa-chart-simple"></i> —
        </span>
        <button id="btnImport" class="btn btn-outline-primary px-3" disabled>
          <i class="fa fa-file-import me-2"></i>Import CSV
        </button>
        <button id="btnRefresh" class="btn btn-outline-secondary px-3">
          <i class="fa fa-arrows-rotate"></i>
        </button>
        <button id="btnSave" class="btn btn-primary px-4 fw-bold">
          <i class="fa fa-cloud-arrow-up me-2"></i>Save Changes
        </button>
      </div>
    </div>

    <div class="ssa-form">
      <div class="ssa-field">
        <label for="courseSelect">Course</label>
        <select id="courseSelect">
          <option value="">Loading courses...</option>
        </select>
      </div>

      <div class="ssa-field">
        <label for="semesterSelect">Semester</label>
        <select id="semesterSelect" disabled>
          <option value="">Select a course first</option>
        </select>
      </div>
    </div>

    <div class="mt-3 pt-3 border-top d-flex justify-content-between align-items-center">
        <div class="text-mini" id="infoLine">Ready to load.</div>
        <div class="small text-muted">
            <i class="fa fa-info-circle me-1"></i> Data is auto-saved locally during your session.
        </div>
    </div>
  </div>

  {{-- Table Card --}}
  <div class="ssa-card">
    <div class="card-header">
      <div class="ssa-card-head">
        <div class="fw-bold text-secondary"><i class="fa fa-list-check me-2"></i>Attendance Matrix</div>
        <div class="d-flex align-items-center gap-2 flex-wrap ms-auto">
          <div class="ssa-search">
            <i class="fa fa-search"></i>
            <input id="studentSearchInput" type="search" placeholder="Search student by name or roll no">
          </div>
          <div class="badge bg-light text-dark border fw-normal">Toggle checkboxes to include subjects</div>
        </div>
      </div>
    </div>

    <div class="card-body p-0">
      <div id="emptyState" class="empty-state">
        <i class="fa fa-layer-group"></i>
        <div class="title">No Selection Made</div>
        <div class="subtitle">Please select a course and semester to view the attendance grid.</div>
      </div>

      <div id="tableRoot" style="display:none;"></div>
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

<div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold"><i class="fa fa-file-import text-danger me-2"></i>Import Attendance CSV</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="ssa-import-note mb-3">
          Download the latest template for the selected course and semester. Update only the attendance columns and upload the CSV to replace the current scope attendance mapping.
        </div>

        <div class="d-grid gap-2 mb-3">
          <button id="btnDownloadTemplate" type="button" class="btn btn-outline-primary">
            <i class="fa fa-download me-2"></i>Download Template
          </button>
        </div>

        <div class="ssa-upload-box">
          <label for="csvFileInput" class="form-label fw-semibold mb-2">Upload CSV</label>
          <input id="csvFileInput" type="file" class="form-control" accept=".csv,text/csv">
          <div class="form-text mt-2">The uploaded CSV replaces the current attendance values for this selected scope.</div>
        </div>

        <div id="importFeedback" class="ssa-import-feedback mt-3"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="btnUploadCsv">
          <i class="fa fa-cloud-arrow-up me-2"></i>Import CSV
        </button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
(() => {
  if (window.__STUDENT_SUBJECT_ATTENDANCE_V1__) return;
  window.__STUDENT_SUBJECT_ATTENDANCE_V1__ = true;

  const $ = (id) => document.getElementById(id);

  const API = {
    courses: () => '/api/courses?per_page=200&sort=title&direction=asc',
    semestersByCourse: (courseId) => `/api/courses/${encodeURIComponent(courseId)}/semesters`,
    subjectsByScope: (deptId, courseId, semesterId) =>
      `/api/subjects/current?department_id=${encodeURIComponent(deptId)}&course_id=${encodeURIComponent(courseId)}&semester_id=${encodeURIComponent(semesterId)}`,
    studentsByScope: (departmentId, courseId, semesterId) => {
      const params = new URLSearchParams({
        course_id: String(courseId),
        semester_id: String(semesterId),
      });
      if (departmentId) params.set('department_id', String(departmentId));
      return `/api/student-academic-details/by-academics?${params.toString()}`;
    },
    mappingCurrent: (departmentId, courseId, semesterId) =>
      `/api/student-subjects/current?department_id=${encodeURIComponent(departmentId)}&course_id=${encodeURIComponent(courseId)}&semester_id=${encodeURIComponent(semesterId)}`,
    importTemplate: (departmentId, courseId, semesterId) => {
      const params = new URLSearchParams({
        course_id: String(courseId),
        semester_id: String(semesterId),
      });
      if (departmentId) params.set('department_id', String(departmentId));
      return `/api/student-subjects/import-template?${params.toString()}`;
    },
    importCsv: () => '/api/student-subjects/import-csv',
    store: () => '/api/student-subjects',
    update: (idOrUuid) => `/api/student-subjects/${encodeURIComponent(idOrUuid)}`,
  };

  function esc(str){
    return (str ?? '').toString().replace(/[&<>"']/g, s => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    }[s]));
  }

  function idNum(v){
    const n = parseInt(String(v ?? '').trim(), 10);
    return Number.isFinite(n) ? n : null;
  }

  function pickArray(v){
    if (Array.isArray(v)) return v;
    if (v === null || v === undefined) return [];
    if (typeof v === 'string'){
      try{ const d = JSON.parse(v); return Array.isArray(d) ? d : []; }catch(_){ return []; }
    }
    return [];
  }

  function normalizeList(js){
    if (!js) return [];
    if (Array.isArray(js)) return js;
    if (Array.isArray(js.data)) return js.data;
    if (js.data && Array.isArray(js.data.data)) return js.data.data;
    if (Array.isArray(js.items)) return js.items;
    return [];
  }

  const token = () => (sessionStorage.getItem('token') || localStorage.getItem('token') || '');
  function authHeaders(extra={}){
    return Object.assign({
      'Authorization': 'Bearer ' + token(),
      'Accept': 'application/json'
    }, extra);
  }

  async function fetchWithTimeout(url, opts={}, ms=15000){
    const ctrl = new AbortController();
    const t = setTimeout(()=>ctrl.abort(), ms);
    try{ return await fetch(url, { ...opts, signal: ctrl.signal }); }
    finally{ clearTimeout(t); }
  }

  function showLoading(on){ $('globalLoading')?.classList.toggle('is-show', !!on); }

  const toastOk  = $('toastSuccess') ? new bootstrap.Toast($('toastSuccess')) : null;
  const toastErr = $('toastError') ? new bootstrap.Toast($('toastError')) : null;
  const ok  = (m) => { $('toastSuccessText').textContent = m || 'Done'; toastOk && toastOk.show(); };
  const err = (m) => { $('toastErrorText').textContent = m || 'Something went wrong'; toastErr && toastErr.show(); };
  const importModalEl = $('importModal');
  const importModal = importModalEl ? new bootstrap.Modal(importModalEl) : null;

  /**
   * ✅ Page State
   */
  const state = {
    courses: [],
    semesters: [],
    subjects: [],
    students: [],
    mapping: null,
    departmentId: null,
    selectedCourseId: null,
    selectedSemesterId: null,
    studentSearch: '',
    matrix: new Map(),
    saving: false,
    importing: false,
  };

  function courseLabel(c){ return String(c?.title || c?.name || c?.course_title || `Course #${c?.id}`); }
  function semesterLabel(s){
    if (s?.semester_no !== undefined && s?.semester_no !== null && String(s.semester_no).trim() !== ''){
      return `Semester ${String(s.semester_no)}`;
    }
    return s?.title || s?.name || `Semester #${s?.id}`;
  }

  function subjectTitle(sub){ return String(sub?.title || sub?.name || sub?.subject_title || `Subject #${sub?.id}`); }
  function subjectCode(sub){ return String(sub?.subject_code || sub?.code || sub?.paper_code || '').trim(); }

  // ✅ NEW: Subject compulsory/optional tag (default: compulsory)
  function subjectKind(sub){
    const t = String(sub?.subject_type || sub?.type || '').toLowerCase().trim();
    if (t === 'optional') return 'optional';
    return 'compulsory';
  }
  function subjectKindBadge(sub){
    const k = subjectKind(sub);
    if (k === 'optional') return `<span class="type-pill optional">Optional</span>`;
    return `<span class="type-pill compulsory">Compulsory</span>`;
  }

  function studentName(st){ return String(st?.name || st?.student_name || st?.full_name || 'Student'); }
  function studentRoll(st){ return String(st?.roll_no ?? st?.academic_details?.roll_no ?? '').trim(); }
  function studentMatchesSearch(st, q){
    const query = String(q || '').trim().toLowerCase();
    if (!query) return true;

    const hay = [
      studentName(st),
      studentRoll(st),
      st?.registration_no,
      st?.academic_details?.registration_no,
      st?.email,
    ]
      .map(v => String(v || '').toLowerCase())
      .filter(Boolean);

    return hay.some(v => v.includes(query));
  }
  function resolveDepartmentIdFromCourse(course){
    return idNum(course?.department_id) || idNum(course?.department?.id) || idNum(course?.dept_id);
  }

  function filteredStudents(){
    return (state.students || []).filter(st => studentMatchesSearch(st, state.studentSearch));
  }

  function keyOf(studentId, subjectId){ return String(studentId) + '_' + String(subjectId); }

  function getCell(studentId, subjectId){
    const k = keyOf(studentId, subjectId);
    if (!state.matrix.has(k)) state.matrix.set(k, { checked:false, attendance:'' });
    return state.matrix.get(k);
  }

  function setCell(studentId, subjectId, patch){
    const cell = getCell(studentId, subjectId);
    state.matrix.set(keyOf(studentId, subjectId), Object.assign({}, cell, patch));
  }

  function clearAllErrors(){
    document.querySelectorAll('.cell-error').forEach(x => x.classList.remove('cell-error'));
  }

  function hasScopeSelections(){
    return !!state.selectedCourseId && !!state.selectedSemesterId;
  }

  function hasImportableScope(){
    return hasScopeSelections() && state.students.length > 0 && state.subjects.length > 0;
  }

  function setImportFeedback(message = '', type = ''){
    const box = $('importFeedback');
    if (!box) return;
    box.textContent = message;
    box.className = 'ssa-import-feedback mt-3' + (type ? ` is-${type}` : '');
  }

  function clearImportState(){
    const file = $('csvFileInput');
    if (file) file.value = '';
    setImportFeedback('');
  }

  function updateActionState(){
    const canUseScope = hasScopeSelections();
    if ($('semesterSelect')) $('semesterSelect').disabled = !state.selectedCourseId;
    if ($('btnImport')) $('btnImport').disabled = !hasImportableScope();
    if ($('btnDownloadTemplate')) $('btnDownloadTemplate').disabled = !canUseScope;
    if ($('btnUploadCsv')) $('btnUploadCsv').disabled = !canUseScope || state.importing;
  }

  function updateSummaryBadge(){
    const visibleStudents = filteredStudents();
    let selected = 0;
    state.students.forEach(st => {
      const sid = idNum(st?.student_id ?? st?.id);
      state.subjects.forEach(sub => {
        const subid = idNum(sub?.id);
        if (getCell(sid, subid).checked) selected++;
      });
    });
    const studentCount = state.studentSearch
      ? `${visibleStudents.length}/${state.students.length}`
      : `${state.students.length}`;
    $('badgeSummary').innerHTML = `<i class="fa fa-user-graduate me-1"></i> ${studentCount} • <i class="fa fa-book me-1"></i> ${state.subjects.length} • <i class="fa fa-check-to-slot me-1"></i> ${selected}`;
  }

  function setEmpty(on){
    $('emptyState').style.display = on ? '' : 'none';
    $('tableRoot').style.display = on ? 'none' : '';
  }

  /**
   * ✅ Render Table
   */
  function renderTable(){
    const visibleStudents = filteredStudents();

    if (!hasScopeSelections()){
      setEmpty(true);
      $('tableRoot').innerHTML = '';
      $('infoLine').textContent = 'Please select course and semester.';
      updateActionState();
      return;
    }

    if (!state.students.length || !state.subjects.length){
      setEmpty(false);
      $('tableRoot').innerHTML = `<div class="empty-state"><i class="fa fa-circle-exclamation text-warning"></i><div class="title">Data Not Available</div><div class="subtitle">Check if students and subjects are mapped to this semester.</div></div>`;
      updateSummaryBadge();
      updateActionState();
      return;
    }

    if (!visibleStudents.length){
      setEmpty(false);
      $('tableRoot').innerHTML = `<div class="empty-state"><i class="fa fa-magnifying-glass text-warning"></i><div class="title">No Students Found</div><div class="subtitle">Try a different student name or roll number.</div></div>`;
      $('infoLine').textContent = `Loaded ${state.students.length} students and ${state.subjects.length} subjects. Search matched 0 students.`;
      updateSummaryBadge();
      updateActionState();
      return;
    }

    setEmpty(false);
    const headColSpan = state.subjects.length;

    $('tableRoot').innerHTML = `
      <div class="ssa-table-wrap">
        <table class="ssa-table">
          <thead>
            <tr class="h1">
              <th class="sticky-col col-roll" rowspan="3">Roll No.</th>
              <th class="sticky-col-2 col-name" rowspan="3">Student Name</th>
              <th colspan="${headColSpan}" class="text-center py-3">Subject Wise Attendance (%)</th>
            </tr>

            <tr class="h2">
              ${state.subjects.map(s => `
                <th class="sub-col">
                  <div style="display:flex; align-items:center; justify-content:center; flex-wrap:wrap; gap:6px;">
                    <span>${esc(subjectTitle(s))}</span>
                    ${subjectKindBadge(s)}
                  </div>
                </th>
              `).join('')}
            </tr>

            <tr class="h3">
              ${state.subjects.map(s => {
                const subid = idNum(s?.id);
                return `
                  <th class="sub-col">
                    <div class="sub-head">
                      <span class="code">${esc(subjectCode(s) || 'N/A')}</span>
                      <div class="all">
                        <input type="checkbox" class="chk-sub-all" data-subid="${esc(String(subid))}">
                        <label>Select All</label>
                      </div>
                    </div>
                  </th>`;
              }).join('')}
            </tr>
          </thead>
          <tbody>
            ${visibleStudents.map(st => {
              const sid = idNum(st?.student_id ?? st?.id);
              return `
                <tr data-sid="${esc(String(sid))}">
                  <td class="sticky-col col-roll">${esc(studentRoll(st) || '—')}</td>
                  <td class="sticky-col-2 col-name">${esc(studentName(st))}</td>
                  ${state.subjects.map(sub => {
                    const subid = idNum(sub?.id);
                    const cell = getCell(sid, subid);
                    return `
                      <td class="sub-col" data-cell="1" data-sid="${esc(String(sid))}" data-subid="${esc(String(subid))}">
                        <div class="cellbox">
                          <input type="checkbox" class="chk-cell" data-sid="${esc(String(sid))}" data-subid="${esc(String(subid))}" ${cell.checked ? 'checked' : ''}>
                          <input type="number" class="att-input" min="0" max="100" step="0.01" placeholder="%" 
                            value="${esc(cell.attendance)}" style="${cell.checked ? '' : 'display:none'}"
                            data-sid="${esc(String(sid))}" data-subid="${esc(String(subid))}">
                          ${!cell.checked ? `<span class="cell-na">NA</span>` : ``}
                        </div>
                      </td>`;
                  }).join('')}
                </tr>`;
            }).join('')}
          </tbody>
        </table>
      </div>
    `;

    syncAllHeaderCheckboxes();
    updateSummaryBadge();
    updateActionState();
  }

  function syncAllHeaderCheckboxes(){
    const visibleStudents = filteredStudents();
    document.querySelectorAll('.chk-sub-all').forEach(h => {
      const subid = idNum(h.dataset.subid);
      let total = visibleStudents.length, on = 0;
      visibleStudents.forEach(st => { if (getCell(idNum(st?.student_id ?? st?.id), subid).checked) on++; });
      h.indeterminate = (on > 0 && on < total);
      h.checked = (total > 0 && on === total);
    });
  }

  function applyExistingMapping(mapping){
    state.matrix.clear();
    pickArray(mapping?.subject_json).forEach(item => {
      const sid = idNum(item?.student_id), subid = idNum(item?.subject_id);
      if (sid && subid) setCell(sid, subid, { checked: true, attendance: String(item?.current_attendance ?? '') });
    });
  }

  async function loadCourses(){
    const res = await fetchWithTimeout(API.courses(), { headers: authHeaders() });
    if (!res.ok) throw new Error('Failed to load courses');

    state.courses = normalizeList(await res.json());
    $('courseSelect').innerHTML = `<option value="">Select Course</option>` + state.courses.map(c => `<option value="${esc(String(c.id))}">${esc(courseLabel(c))}</option>`).join('');
    updateActionState();
  }

  async function loadSemesters(courseId){
    const res = await fetchWithTimeout(API.semestersByCourse(courseId), { headers: authHeaders() });
    if (!res.ok) throw new Error('Failed to load semesters');
    state.semesters = normalizeList(await res.json());
    $('semesterSelect').innerHTML = `<option value="">Select Semester</option>` + state.semesters.map(s => `<option value="${esc(String(s.id))}">${esc(semesterLabel(s))}</option>`).join('');
    $('semesterSelect').disabled = false;
    updateActionState();
  }

  async function loadAll(){
    if (!hasScopeSelections()) return;
    showLoading(true);
    try {
      const selectedCourse = state.courses.find(c => idNum(c?.id) === state.selectedCourseId) || null;
      const resSt = await fetchWithTimeout(
        API.studentsByScope(state.departmentId, state.selectedCourseId, state.selectedSemesterId),
        { headers: authHeaders() }
      );
      if (!resSt.ok) throw new Error('Failed to load students');
      state.students = normalizeList(await resSt.json()).filter(x => x?.has_academic_details).map(x => ({
        ...x, student_id: idNum(x?.student_id) || idNum(x?.user_id) || idNum(x?.id),
        dept_id: idNum(x?.academic_details?.department_id)
      }));
      state.departmentId = state.students[0]?.dept_id || resolveDepartmentIdFromCourse(selectedCourse);
      if (!state.departmentId) throw new Error('Failed to resolve department for the selected course');

      const resSub = await fetchWithTimeout(API.subjectsByScope(state.departmentId, state.selectedCourseId, state.selectedSemesterId), { headers: authHeaders() });
      if (!resSub.ok) throw new Error('Failed to load subjects');
      state.subjects = normalizeList(await resSub.json());

      const resMap = await fetchWithTimeout(API.mappingCurrent(state.departmentId, state.selectedCourseId, state.selectedSemesterId), { headers: authHeaders() });
      if (!resMap.ok) throw new Error('Failed to load attendance mapping');
      state.mapping = normalizeList(await resMap.json())[0] || null;
      if (state.mapping) applyExistingMapping(state.mapping); else state.matrix.clear();

      const searchNote = state.studentSearch ? ` Search matched ${filteredStudents().length} students.` : '';
      $('infoLine').textContent = `Loaded ${state.students.length} students and ${state.subjects.length} subjects for import or direct editing.${searchNote}`;
      renderTable();
    } catch(ex) { err(ex.message); } finally { showLoading(false); }
  }

  async function readJsonSafe(res){
    try { return await res.json(); } catch(_){ return null; }
  }

  function extractFilename(res, fallback){
    const header = res.headers.get('content-disposition') || '';
    const utfMatch = header.match(/filename\*=UTF-8''([^;]+)/i);
    if (utfMatch?.[1]) return decodeURIComponent(utfMatch[1]);
    const plainMatch = header.match(/filename="?([^"]+)"?/i);
    return plainMatch?.[1] || fallback;
  }

  async function downloadTemplate(){
    if (!hasScopeSelections()) return err('Select course and semester first.');

    showLoading(true);
    setImportFeedback('');

    try {
      const res = await fetchWithTimeout(
        API.importTemplate(state.departmentId, state.selectedCourseId, state.selectedSemesterId),
        { headers: authHeaders() },
        30000
      );

      if (!res.ok) {
        const payload = await readJsonSafe(res);
        throw new Error(payload?.message || 'Template download failed');
      }

      const blob = await res.blob();
      const fileName = extractFilename(
        res,
        `student-subject-attendance-template-${state.selectedCourseId}-${state.selectedSemesterId}.csv`
      );

      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = fileName;
      document.body.appendChild(a);
      a.click();
      a.remove();
      URL.revokeObjectURL(url);

      setImportFeedback('Template downloaded successfully.', 'success');
    } catch (ex) {
      setImportFeedback(ex.message || 'Template download failed.', 'error');
      err(ex.message);
    } finally {
      showLoading(false);
    }
  }

  function importSummaryText(payload){
    const summary = payload?.import_summary;
    if (!summary) return payload?.message || 'CSV imported successfully.';
    return `Imported ${summary.parsed_records || 0} attendance records from ${summary.processed_rows || 0} rows.`;
  }

  async function uploadCsv(){
    const file = $('csvFileInput')?.files?.[0];
    if (!file) return setImportFeedback('Choose a CSV file to import.', 'error');
    if (!hasScopeSelections()) return setImportFeedback('Select course and semester first.', 'error');

    state.importing = true;
    updateActionState();
    showLoading(true);
    setImportFeedback('');

    try {
      const form = new FormData();
      if (state.departmentId) form.append('department_id', String(state.departmentId));
      form.append('course_id', String(state.selectedCourseId));
      form.append('semester_id', String(state.selectedSemesterId));
      form.append('file', file);

      const res = await fetchWithTimeout(API.importCsv(), {
        method: 'POST',
        headers: authHeaders(),
        body: form
      }, 45000);

      const payload = await readJsonSafe(res);
      if (!res.ok) {
        const firstError =
          Array.isArray(payload?.errors) ? payload.errors[0] :
          (payload?.errors && typeof payload.errors === 'object'
            ? Object.values(payload.errors).flat()[0]
            : payload?.message);
        throw new Error(firstError || payload?.message || 'CSV import failed');
      }

      ok(importSummaryText(payload));
      clearImportState();
      importModal?.hide();
      await loadAll();
    } catch (ex) {
      setImportFeedback(ex.message || 'CSV import failed.', 'error');
      err(ex.message);
    } finally {
      state.importing = false;
      updateActionState();
      showLoading(false);
    }
  }

  function validate(){
    clearAllErrors();
    let first = null, errs = 0;
    state.students.forEach(st => {
      const sid = idNum(st?.student_id ?? st?.id);
      state.subjects.forEach(sub => {
        const subid = idNum(sub?.id), cell = getCell(sid, subid);
        if (cell.checked){
          const n = Number(cell.attendance);
          if (cell.attendance.trim() === '' || isNaN(n) || n < 0 || n > 100){
            errs++;
            const td = document.querySelector(`td[data-sid="${sid}"][data-subid="${subid}"]`);
            td?.classList.add('cell-error');
            if(!first) first = td;
          }
        }
      });
    });
    if(errs){ first?.scrollIntoView({ behavior:'smooth', block:'center' }); return false; }
    return true;
  }

  async function save(){
    if (state.saving || !validate()) return;
    const payload = {
      department_id: state.departmentId,
      course_id: state.selectedCourseId,
      semester_id: state.selectedSemesterId,
      subject_json: [], status: 'active'
    };
    state.students.forEach(st => {
      const sid = idNum(st?.student_id ?? st?.id);
      state.subjects.forEach(sub => {
        const subid = idNum(sub?.id), cell = getCell(sid, subid);
        if(cell.checked) payload.subject_json.push({ student_id: sid, subject_id: subid, current_attendance: Number(cell.attendance) });
      });
    });

    if(!payload.subject_json.length) return err('Select at least one subject.');
    state.saving = true; showLoading(true);
    try {
      const isUpd = !!(state.mapping?.uuid || state.mapping?.id);
      const res = await fetchWithTimeout(isUpd ? API.update(state.mapping.uuid || state.mapping.id) : API.store(), {
        method: isUpd ? 'PATCH' : 'POST',
        headers: authHeaders({ 'Content-Type':'application/json' }),
        body: JSON.stringify(payload)
      });
      if(!res.ok) throw new Error('Save failed');
      ok('Data saved successfully');
      await loadAll();
    } catch(ex) { err(ex.message); } finally { state.saving = false; showLoading(false); }
  }

  function bindEvents(){
    $('courseSelect').addEventListener('change', async (e) => {
      state.selectedCourseId = idNum(e.target.value);
      state.selectedSemesterId = null;
      state.departmentId = resolveDepartmentIdFromCourse(state.courses.find(c => idNum(c?.id) === state.selectedCourseId) || null);
      state.students = [];
      state.subjects = [];
      state.mapping = null;
      state.matrix.clear();
      $('semesterSelect').innerHTML = '<option value="">Select a course first</option>';
      $('semesterSelect').disabled = !state.selectedCourseId;
      clearImportState();
      renderTable();

      if (state.selectedCourseId) {
        showLoading(true);
        try { await loadSemesters(state.selectedCourseId); }
        catch (ex) { err(ex.message); }
        finally { showLoading(false); }
      }
    });

    $('semesterSelect').addEventListener('change', async (e) => {
      state.selectedSemesterId = idNum(e.target.value);
      clearImportState();
      await loadAll();
    });

    $('btnRefresh').addEventListener('click', loadAll);
    $('btnSave').addEventListener('click', save);
    $('btnImport').addEventListener('click', () => {
      if (!hasImportableScope()) return err('Load course and semester data first.');
      clearImportState();
      importModal?.show();
    });
    $('btnDownloadTemplate').addEventListener('click', downloadTemplate);
    $('btnUploadCsv').addEventListener('click', uploadCsv);
    $('studentSearchInput').addEventListener('input', (e) => {
      state.studentSearch = String(e.target.value || '').trim();
      if (hasScopeSelections() && state.students.length && state.subjects.length) {
        const matched = filteredStudents().length;
        $('infoLine').textContent = `Loaded ${state.students.length} students and ${state.subjects.length} subjects for import or direct editing. Search matched ${matched} students.`;
      }
      renderTable();
    });

    document.addEventListener('change', (e) => {
  const h = e.target.closest('.chk-sub-all');
  if (h) {
    const subid = idNum(h.dataset.subid);

    filteredStudents().forEach(st => {
      const sid = idNum(st?.student_id ?? st?.id);
      setCell(sid, subid, { checked: h.checked });

      const td = document.querySelector(`td[data-sid="${sid}"][data-subid="${subid}"]`);
      if (!td) return;

      const input = td.querySelector('.att-input');
      const na = td.querySelector('.cell-na');
      const chk = td.querySelector('.chk-cell');

      if (chk) chk.checked = h.checked;

      if (h.checked) {
        if (input) input.style.display = '';
        if (na) na.style.display = 'none';
      } else {
        if (input) input.style.display = 'none';
        if (na) na.style.display = '';
      }
    });

    syncAllHeaderCheckboxes();
    updateSummaryBadge();
    return;
  }

  const c = e.target.closest('.chk-cell');
  if (c) {
    const sid = idNum(c.dataset.sid);
    const subid = idNum(c.dataset.subid);

    setCell(sid, subid, { checked: c.checked });

    const td = document.querySelector(`td[data-sid="${sid}"][data-subid="${subid}"]`);
    if (td) {
      const input = td.querySelector('.att-input');
      const na = td.querySelector('.cell-na');

      if (c.checked) {
        if (input) input.style.display = '';
        if (na) na.style.display = 'none';
      } else {
        if (input) input.style.display = 'none';
        if (na) na.style.display = '';
      }
    }

    syncAllHeaderCheckboxes();
    updateSummaryBadge();
  }
});

    document.addEventListener('input', (e) => {
      const inp = e.target.closest('.att-input');
      if (inp) {
        setCell(idNum(inp.dataset.sid), idNum(inp.dataset.subid), { checked: true, attendance: inp.value });
        updateSummaryBadge();
      }
    });
  }

  document.addEventListener('DOMContentLoaded', async () => {
    if (!token()) { window.location.href='/'; return; }
    bindEvents();
    showLoading(true);
    try { await loadCourses(); renderTable(); } finally { showLoading(false); }
  });
})();
</script>
@endpush
