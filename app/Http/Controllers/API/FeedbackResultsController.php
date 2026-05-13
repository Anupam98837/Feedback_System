<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FeedbackResultsController extends Controller
{
    private const POSTS     = 'feedback_posts';
    private const SUBS      = 'feedback_submissions';
    private const QUESTIONS = 'feedback_questions';
    private const USERS     = 'users';

    /** cache schema checks */
    protected array $colCache = [];
    protected array $tableCache = [];

    /* =========================================================
     | Helpers
     |========================================================= */

    private function actor(Request $r): array
    {
        return [
            'id'   => (int) ($r->attributes->get('auth_tokenable_id') ?? optional($r->user())->id ?? 0),
            'role' => (string) ($r->attributes->get('auth_role') ?? ($r->user()->role ?? '')),
            'type' => (string) ($r->attributes->get('auth_tokenable_type') ?? ($r->user() ? get_class($r->user()) : '')),
            'uuid' => (string) ($r->attributes->get('auth_user_uuid') ?? ($r->user()->uuid ?? '')),
        ];
    }

    private function hasCol(string $table, string $col): bool
    {
        $k = $table . '.' . $col;
        if (array_key_exists($k, $this->colCache)) return (bool) $this->colCache[$k];

        try {
            return $this->colCache[$k] = Schema::hasColumn($table, $col);
        } catch (\Throwable $e) {
            return $this->colCache[$k] = false;
        }
    }

    private function tableExists(string $t): bool
    {
        if (array_key_exists($t, $this->tableCache)) return (bool) $this->tableCache[$t];
        try { return $this->tableCache[$t] = Schema::hasTable($t); }
        catch (\Throwable $e) { return $this->tableCache[$t] = false; }
    }

    private function requireStaff(Request $r)
    {
        $role = strtolower((string)($this->actor($r)['role'] ?? ''));
        $allowed = ['admin','director','principal','hod','faculty','technical_assistant','it_person'];
        if (!in_array($role, $allowed, true)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized Access'], 403);
        }
        return null;
    }

    private function pickNameColumn(string $table, array $candidates, string $fallback='id'): string
    {
        foreach ($candidates as $c) {
            if ($this->hasCol($table, $c)) return $c;
        }
        return $fallback;
    }

    private function toInt($v): ?int
    {
        if ($v === null) return null;
        $s = trim((string)$v);
        if ($s === '') return null;
        return is_numeric($s) ? (int)$s : null;
    }

    private function normalizeJson($v)
    {
        if ($v === null) return null;
        if (is_array($v)) return $v;
        if (is_object($v)) return json_decode(json_encode($v), true);
        if (is_string($v)) {
            $s = trim($v);
            if ($s === '') return null;
            $decoded = json_decode($s, true);
            return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
        }
        return null;
    }

    private function normalizeIdList($v): array
    {
        $arr = $this->normalizeJson($v);
        if (!is_array($arr)) return [];

        $out = [];
        foreach ($arr as $x) {
            if (is_array($x)) {
                $x = $x['id'] ?? $x['user_id'] ?? $x['student_id'] ?? null;
            }
            if ($x === null || $x === '') continue;
            if (is_numeric($x)) $out[] = (int)$x;
        }
        $out = array_values(array_unique(array_filter($out, fn($x) => $x > 0)));
        sort($out);
        return $out;
    }

    private function initDist(): array
    {
        return [
            'counts' => ['5'=>0,'4'=>0,'3'=>0,'2'=>0,'1'=>0],
            'total'  => 0,
            'avg'    => null,
        ];
    }

    private function finalizeDist(array &$dist): void
    {
        $total = (int)($dist['total'] ?? 0);
        if ($total <= 0) {
            $dist['avg'] = null;
            return;
        }

        $sum = 0;
        foreach ([5,4,3,2,1] as $s) {
            $sum += $s * (int)($dist['counts'][(string)$s] ?? 0);
        }
        $dist['avg'] = round($sum / $total, 2);
    }

    private function responseRate(int $given, int $assigned): float
    {
        if ($assigned <= 0) return 0.0;
        return round(($given / $assigned) * 100, 2);
    }

    private function questionKeyToId($key): ?int
    {
        if ($key === null) return null;
        $s = trim((string)$key);
        if ($s === '') return null;
        if (preg_match('/^\d+$/', $s)) return (int)$s;
        if (preg_match('/(\d+)$/', $s, $m)) return (int)$m[1];
        return null;
    }

    private function extractRating($v): ?int
    {
        if ($v === null || $v === '') return null;

        if (is_numeric($v)) {
            $n = (int)$v;
            return ($n >= 1 && $n <= 5) ? $n : null;
        }

        if (is_string($v)) {
            $s = trim($v);
            if ($s === '') return null;
            if (is_numeric($s)) {
                $n = (int)$s;
                return ($n >= 1 && $n <= 5) ? $n : null;
            }
            $decoded = json_decode($s, true);
            if (json_last_error() === JSON_ERROR_NONE) return $this->extractRating($decoded);
            return null;
        }

        if (is_object($v)) $v = json_decode(json_encode($v), true);

        if (is_array($v)) {
            foreach (['stars','rating','value','answer','grade','score'] as $k) {
                if (array_key_exists($k, $v)) {
                    $n = $this->extractRating($v[$k]);
                    if ($n !== null) return $n;
                }
            }
            if (array_key_exists(0, $v)) return $this->extractRating($v[0]);
        }

        return null;
    }

    private function looksLikeRatingObject(array $v): bool
    {
        foreach (['stars','rating','value','answer','grade','score'] as $k) {
            if (array_key_exists($k, $v)) return true;
        }
        return false;
    }

    /**
     * Convert any accepted answers JSON shape into rows:
     * [ ['question_id'=>int, 'faculty_id'=>int, 'stars'=>int] ]
     */
    private function extractAnswerRows($answers): array
    {
        $answers = $this->normalizeJson($answers);
        if (!is_array($answers)) return [];

        $rows = [];

        $isList = array_keys($answers) === range(0, count($answers) - 1);

        if ($isList) {
            foreach ($answers as $item) {
                if (is_object($item)) $item = json_decode(json_encode($item), true);
                if (!is_array($item)) continue;

                $qid = $this->questionKeyToId($item['question_id'] ?? $item['qid'] ?? $item['question'] ?? $item['id'] ?? null);
                if (!$qid) continue;

                $fidRaw = $item['faculty_id'] ?? $item['faculty'] ?? $item['teacher_id'] ?? 0;
                $fid = is_numeric($fidRaw) ? (int)$fidRaw : 0;
                if ($fid < 0) $fid = 0;

                $stars = $this->extractRating($item);
                if ($stars !== null) {
                    $rows[] = ['question_id'=>$qid, 'faculty_id'=>$fid, 'stars'=>$stars];
                }
            }
            return $rows;
        }

        foreach ($answers as $qKey => $raw) {
            $qid = $this->questionKeyToId($qKey);
            if (!$qid) continue;

            if (is_object($raw)) $raw = json_decode(json_encode($raw), true);

            // Simple scalar: {"12": 5}
            if (!is_array($raw)) {
                $stars = $this->extractRating($raw);
                if ($stars !== null) $rows[] = ['question_id'=>$qid, 'faculty_id'=>0, 'stars'=>$stars];
                continue;
            }

            // Object holding the rating directly: {"12": {"rating":5}}
            if ($this->looksLikeRatingObject($raw)) {
                $fidRaw = $raw['faculty_id'] ?? $raw['faculty'] ?? $raw['teacher_id'] ?? 0;
                $fid = is_numeric($fidRaw) ? (int)$fidRaw : 0;
                if ($fid < 0) $fid = 0;

                $stars = $this->extractRating($raw);
                if ($stars !== null) $rows[] = ['question_id'=>$qid, 'faculty_id'=>$fid, 'stars'=>$stars];
                continue;
            }

            // Faculty map: {"12": {"45": 5, "78": {"rating":4}}}
            foreach ($raw as $fKey => $val) {
                $fKeyStr = trim((string)$fKey);
                if ($fKeyStr !== '0' && !preg_match('/^\d+$/', $fKeyStr)) continue;
                $fid = (int)$fKeyStr;
                if ($fid < 0) $fid = 0;
                $stars = $this->extractRating($val);
                if ($stars !== null) $rows[] = ['question_id'=>$qid, 'faculty_id'=>$fid, 'stars'=>$stars];
            }
        }

        return $rows;
    }

    private function addQuestionSkeleton(array &$post, int $qid, ?array $questionMap, array $facultyIdsForQuestion = []): void
    {
        if ($qid <= 0) return;
        $qKey = (string)$qid;
        if (!isset($post['questions'][$qKey])) {
            $post['questions'][$qKey] = [
                'question_id'    => $qid,
                'question_title' => (string)($questionMap[$qid]['title'] ?? ('Question #' . $qid)),
                'group_title'    => $questionMap[$qid]['group_title'] ?? null,
                'distribution'   => $this->initDist(),
                'faculty'        => [],
            ];
        }

        foreach ($facultyIdsForQuestion as $fid) {
            $fid = (int)$fid;
            if ($fid <= 0) continue;
            $fKey = (string)$fid;
            if (!isset($post['questions'][$qKey]['faculty'][$fKey])) {
                $post['questions'][$qKey]['faculty'][$fKey] = [
                    'faculty_id'      => $fid,
                    'faculty_name'    => 'Faculty #' . $fid,
                    'name_short_form' => null,
                    'employee_id'     => null,
                    'avg_rating'      => null,
                    'count'           => 0,
                    'out_of'          => 5,
                    'distribution'    => $this->initDist(),
                ];
            }
        }
    }

    private function facultyIdsForQuestion(array $post, int $qid): array
    {
        $global = is_array($post['_faculty_ids'] ?? null) ? $post['_faculty_ids'] : [];
        $qf = $post['_question_faculty'] ?? null;
        if (!is_array($qf) || !array_key_exists((string)$qid, $qf)) return $global;

        $rule = $qf[(string)$qid];
        if ($rule === null) return $global;
        if (!is_array($rule)) return $global;
        if (!array_key_exists('faculty_ids', $rule) || $rule['faculty_ids'] === null) return $global;
        return $this->normalizeIdList($rule['faculty_ids']);
    }

    private function fetchFacultyInfo(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), fn($x) => $x > 0)));
        if (empty($ids) || !$this->tableExists(self::USERS)) return [];

        $nameCol = $this->pickNameColumn(self::USERS, ['name','full_name'], 'id');
        $uHasShort = $this->hasCol(self::USERS, 'name_short_form');
        $uHasEmp   = $this->hasCol(self::USERS, 'employee_id');

        $q = DB::table(self::USERS)
            ->whereIn('id', $ids)
            ->select(['id', DB::raw("$nameCol as faculty_name")]);

        if ($uHasShort) $q->addSelect('name_short_form');
        if ($uHasEmp)   $q->addSelect('employee_id');
        if ($this->hasCol(self::USERS,'deleted_at')) $q->whereNull('deleted_at');

        $map = [];
        foreach ($q->get() as $u) {
            $id = (int)($u->id ?? 0);
            if ($id <= 0) continue;
            $map[$id] = [
                'name'            => isset($u->faculty_name) ? (string)$u->faculty_name : ('Faculty #' . $id),
                'name_short_form' => $uHasShort ? ((trim((string)($u->name_short_form ?? '')) !== '') ? (string)$u->name_short_form : null) : null,
                'employee_id'     => $uHasEmp ? ((trim((string)($u->employee_id ?? '')) !== '') ? (string)$u->employee_id : null) : null,
            ];
        }
        return $map;
    }

    private function assignedIdsWithAttendance(array $assignedIds, $post, bool $fpHasDept, bool $fpHasCourse, bool $fpHasSem, ?float $minAttendance): array
    {
        $assignedIds = array_values(array_unique(array_filter(array_map('intval', $assignedIds), fn($x) => $x > 0)));
        sort($assignedIds);

        if ($minAttendance === null) return $assignedIds;
        if (empty($assignedIds)) return [];
        if (!$this->tableExists('student_subject')) return $assignedIds;

        $subjectId = isset($post->subject_id) && $post->subject_id !== null ? (int)$post->subject_id : 0;
        if ($subjectId <= 0) return $assignedIds;

        try {
            $q = DB::table('student_subject as ss')
                ->crossJoin(DB::raw("JSON_TABLE(ss.subject_json, '$[*]' COLUMNS (student_id INT PATH '$.student_id', subject_id INT PATH '$.subject_id', current_attendance DECIMAL(6,2) PATH '$.current_attendance')) sj"))
                ->whereIn('sj.student_id', $assignedIds)
                ->where('sj.subject_id', $subjectId)
                ->where('sj.current_attendance', '>=', $minAttendance)
                ->distinct();

            if ($this->hasCol('student_subject','deleted_at')) $q->whereNull('ss.deleted_at');
            if ($this->hasCol('student_subject','status')) {
                $q->where(function($w){ $w->whereNull('ss.status')->orWhere('ss.status', 'active'); });
            }
            if ($fpHasDept && $this->hasCol('student_subject','department_id') && isset($post->department_id) && $post->department_id !== null) {
                $q->where('ss.department_id', (int)$post->department_id);
            }
            if ($fpHasCourse && $this->hasCol('student_subject','course_id') && isset($post->course_id) && $post->course_id !== null) {
                $q->where('ss.course_id', (int)$post->course_id);
            }
            if ($fpHasSem && $this->hasCol('student_subject','semester_id') && isset($post->semester_id)) {
                if ($post->semester_id === null) $q->whereNull('ss.semester_id');
                else $q->where('ss.semester_id', (int)$post->semester_id);
            }

            $ids = $q->pluck('sj.student_id')->map(fn($x)=>(int)$x)->filter(fn($x)=>$x>0)->unique()->values()->all();
            sort($ids);
            return $ids;
        } catch (\Throwable $e) {
            return $assignedIds;
        }
    }

    private function countAssignedWithAttendance(array $assignedIds, $post, bool $fpHasDept, bool $fpHasCourse, bool $fpHasSem, ?float $minAttendance): int
    {
        return count($this->assignedIdsWithAttendance($assignedIds, $post, $fpHasDept, $fpHasCourse, $fpHasSem, $minAttendance));
    }


    /**
     * Break assigned students into real sections.
     *
     * Important for common feedback posts (section_id = NULL): they should not appear as a
     * separate "Common / No Section" card when a semester has real sections. Instead, the same
     * post is shown inside each section, and each clone only counts/submits students of that
     * section.
     */
    private function sectionStudentBreakdown(
        array $assignedIds,
        $post,
        bool $fpHasDept,
        bool $fpHasCourse,
        bool $fpHasSem,
        bool $fpHasAcad,
        bool $fpHasYear
    ): array {
        $assignedIds = array_values(array_unique(array_filter(array_map('intval', $assignedIds), fn($x) => $x > 0)));
        sort($assignedIds);

        $explicitSectionId = (isset($post->section_id) && $post->section_id !== null && $post->section_id !== '')
            ? (int) $post->section_id
            : 0;

        if (empty($assignedIds)) {
            return [$explicitSectionId => []];
        }

        if (!$this->tableExists('student_academic_details')) {
            return [$explicitSectionId => $assignedIds];
        }

        $studentCol = $this->hasCol('student_academic_details', 'user_id')
            ? 'user_id'
            : ($this->hasCol('student_academic_details', 'student_id') ? 'student_id' : null);

        if (!$studentCol || !$this->hasCol('student_academic_details', 'section_id')) {
            return [$explicitSectionId => $assignedIds];
        }

        try {
            $q = DB::table('student_academic_details as sad')
                ->whereIn("sad.$studentCol", $assignedIds)
                ->select([DB::raw("sad.$studentCol as student_id"), 'sad.section_id']);

            if ($this->hasCol('student_academic_details', 'deleted_at')) {
                $q->whereNull('sad.deleted_at');
            }

            if ($this->hasCol('student_academic_details', 'status')) {
                $q->where(function ($w) {
                    $w->whereNull('sad.status')->orWhere('sad.status', 'active');
                });
            }

            if ($fpHasDept && $this->hasCol('student_academic_details', 'department_id') && isset($post->department_id) && $post->department_id !== null) {
                $q->where('sad.department_id', (int) $post->department_id);
            }

            if ($fpHasCourse && $this->hasCol('student_academic_details', 'course_id') && isset($post->course_id) && $post->course_id !== null) {
                $q->where('sad.course_id', (int) $post->course_id);
            }

            if ($fpHasSem && $this->hasCol('student_academic_details', 'semester_id') && isset($post->semester_id)) {
                if ($post->semester_id === null) $q->whereNull('sad.semester_id');
                else $q->where('sad.semester_id', (int) $post->semester_id);
            }

            if ($fpHasAcad && $this->hasCol('student_academic_details', 'academic_year') && isset($post->academic_year) && trim((string)$post->academic_year) !== '') {
                $q->where('sad.academic_year', trim((string)$post->academic_year));
            }

            if ($fpHasYear && $this->hasCol('student_academic_details', 'year') && isset($post->year) && $post->year !== null && $post->year !== '') {
                $q->where('sad.year', (int)$post->year);
            }

            $grouped = [];
            foreach ($q->get() as $row) {
                $sid = (int)($row->student_id ?? 0);
                $secId = (int)($row->section_id ?? 0);
                if ($sid <= 0 || $secId <= 0) continue;
                if (!isset($grouped[$secId])) $grouped[$secId] = [];
                $grouped[$secId][] = $sid;
            }

            foreach ($grouped as $secId => $ids) {
                $ids = array_values(array_unique(array_filter(array_map('intval', $ids), fn($x) => $x > 0)));
                sort($ids);
                $grouped[$secId] = $ids;
            }

            // Explicit section post: count only that section if mapping is available.
            // If no mapping exists, keep the original assigned list for backward compatibility.
            if ($explicitSectionId > 0) {
                if (isset($grouped[$explicitSectionId]) && !empty($grouped[$explicitSectionId])) {
                    return [$explicitSectionId => $grouped[$explicitSectionId]];
                }
                return [$explicitSectionId => $assignedIds];
            }

            // Common post: if real sections are known, spread the post into each section.
            // No separate "Common" section will be produced in that case.
            if (!empty($grouped)) {
                ksort($grouped, SORT_NATURAL);
                return $grouped;
            }

            // Only when no real section breakdown is available, keep one no-section bucket.
            return [0 => $assignedIds];
        } catch (\Throwable $e) {
            return [$explicitSectionId => $assignedIds];
        }
    }

    /* =========================================================
     | GET /api/feedback-results
     |========================================================= */
    public function results(Request $r)
    {
        if ($resp = $this->requireStaff($r)) return $resp;

        $deptId     = $this->toInt($r->query('department_id'));
        $courseId   = $this->toInt($r->query('course_id'));
        $semesterId = $this->toInt($r->query('semester_id'));
        $subjectId  = $this->toInt($r->query('subject_id'));
        $sectionId  = $this->toInt($r->query('section_id'));
        $year       = $this->toInt($r->query('year'));
        $acadYear   = trim((string)$r->query('academic_year', ''));

        $minAttendance = $r->query('min_attendance', $r->query('attendance', null));
        $minAttendance = ($minAttendance !== null && $minAttendance !== '')
            ? max(0, min(100, (float)$minAttendance))
            : null;

        $hasDepts   = $this->tableExists('departments');
        $hasCourses = $this->tableExists('courses');
        $hasSubsTbl = $this->tableExists('subjects');
        $hasCourseSems     = $this->tableExists('course_semesters');
        $hasCourseSections = $this->tableExists('course_semester_sections');
        $hasSemsTbl     = $this->tableExists('semesters');
        $hasSectionsTbl = $this->tableExists('sections');

        $deptNameCol   = $hasDepts   ? $this->pickNameColumn('departments', ['name','title','department_name','dept_name'], 'id') : null;
        $courseNameCol = $hasCourses ? $this->pickNameColumn('courses', ['title','name','course_name','course_title'], 'id') : null;
        $subNameCol    = $hasSubsTbl ? $this->pickNameColumn('subjects', ['name','title','subject_name'], 'id') : null;
        $csNameCol     = $hasCourseSems ? $this->pickNameColumn('course_semesters', ['title','name','semester_name'], 'id') : null;
        $cssNameCol    = $hasCourseSections ? $this->pickNameColumn('course_semester_sections', ['title','name','section_name'], 'id') : null;
        $semNameCol    = $hasSemsTbl ? $this->pickNameColumn('semesters', ['name','title','semester_name'], 'id') : null;
        $secNameCol    = $hasSectionsTbl ? $this->pickNameColumn('sections', ['name','title','section_name'], 'id') : null;

        $fpHasDept   = $this->hasCol(self::POSTS, 'department_id');
        $fpHasCourse = $this->hasCol(self::POSTS, 'course_id');
        $fpHasSem    = $this->hasCol(self::POSTS, 'semester_id');
        $fpHasSub    = $this->hasCol(self::POSTS, 'subject_id');
        $fpHasSec    = $this->hasCol(self::POSTS, 'section_id');
        $fpHasAcad   = $this->hasCol(self::POSTS, 'academic_year');
        $fpHasYear   = $this->hasCol(self::POSTS, 'year');

        $fsHasStudent = $this->hasCol(self::SUBS, 'student_id');
        $fsHasStatus  = $this->hasCol(self::SUBS, 'status');

        /* ---------------------------------------------------------
         | Lookup maps
         |--------------------------------------------------------- */
        $deptMap = [];
        if ($hasDepts) {
            $q = DB::table('departments')->select(['id', DB::raw("$deptNameCol as nm")]);
            if ($this->hasCol('departments','deleted_at')) $q->whereNull('deleted_at');
            $deptMap = $q->pluck('nm','id')->toArray();
        }

        $courseMap = [];
        $courseDeptMap = [];
        if ($hasCourses) {
            $q = DB::table('courses')->select(['id', DB::raw("$courseNameCol as nm")]);
            if ($this->hasCol('courses', 'department_id')) $q->addSelect('department_id');
            if ($this->hasCol('courses','deleted_at')) $q->whereNull('deleted_at');
            foreach ($q->get() as $c) {
                $id = (int)($c->id ?? 0);
                if ($id <= 0) continue;
                $courseMap[$id] = (string)($c->nm ?? ('Course #' . $id));
                if (isset($c->department_id) && $c->department_id !== null) $courseDeptMap[$id] = (int)$c->department_id;
            }
        }

        $subLabelMap = [];
        $subCodeMap  = [];
        if ($hasSubsTbl) {
            $subHasCode = $this->hasCol('subjects', 'subject_code') || $this->hasCol('subjects', 'code') || $this->hasCol('subjects', 'paper_code');
            $codeCol = $this->hasCol('subjects', 'subject_code') ? 'subject_code' : ($this->hasCol('subjects', 'code') ? 'code' : ($this->hasCol('subjects', 'paper_code') ? 'paper_code' : null));

            $q = DB::table('subjects')->select(['id', DB::raw("$subNameCol as subject_name")]);
            if ($subHasCode && $codeCol) $q->addSelect(DB::raw("$codeCol as subject_code"));
            if ($this->hasCol('subjects','deleted_at')) $q->whereNull('deleted_at');

            foreach ($q->get() as $s) {
                $id = (int)($s->id ?? 0);
                if ($id <= 0) continue;
                $name = trim((string)($s->subject_name ?? ''));
                $code = $subHasCode ? trim((string)($s->subject_code ?? '')) : '';
                $subCodeMap[$id] = ($code !== '') ? $code : null;
                if ($code !== '' && $name !== '') $subLabelMap[$id] = $code . ' - ' . $name;
                elseif ($name !== '')             $subLabelMap[$id] = $name;
                elseif ($code !== '')             $subLabelMap[$id] = $code;
                else                              $subLabelMap[$id] = null;
            }
        }

        $semMap = [];
        if ($hasCourseSems) {
            $q = DB::table('course_semesters')->select(['id', DB::raw("$csNameCol as nm")]);
            if ($this->hasCol('course_semesters','deleted_at')) $q->whereNull('deleted_at');
            $semMap = $q->pluck('nm','id')->toArray();
        } elseif ($hasSemsTbl) {
            $q = DB::table('semesters')->select(['id', DB::raw("$semNameCol as nm")]);
            if ($this->hasCol('semesters','deleted_at')) $q->whereNull('deleted_at');
            $semMap = $q->pluck('nm','id')->toArray();
        }

        $secMap = [];
        if ($hasCourseSections) {
            $q = DB::table('course_semester_sections')->select(['id', DB::raw("$cssNameCol as nm")]);
            if ($this->hasCol('course_semester_sections','deleted_at')) $q->whereNull('deleted_at');
            $secMap = $q->pluck('nm','id')->toArray();
        } elseif ($hasSectionsTbl) {
            $q = DB::table('sections')->select(['id', DB::raw("$secNameCol as nm")]);
            if ($this->hasCol('sections','deleted_at')) $q->whereNull('deleted_at');
            $secMap = $q->pluck('nm','id')->toArray();
        }

        /* ---------------------------------------------------------
         | 1) Fetch ALL feedback posts first.
         | This is the core fix: zero-result posts no longer disappear.
         |--------------------------------------------------------- */
        $select = [
            'fp.id', 'fp.uuid', 'fp.title', 'fp.short_title', 'fp.description',
            DB::raw($fpHasDept   ? 'fp.department_id as department_id' : 'NULL as department_id'),
            DB::raw($fpHasCourse ? 'fp.course_id as course_id' : 'NULL as course_id'),
            DB::raw($fpHasSem    ? 'fp.semester_id as semester_id' : 'NULL as semester_id'),
            DB::raw($fpHasSub    ? 'fp.subject_id as subject_id' : 'NULL as subject_id'),
            DB::raw($fpHasSec    ? 'fp.section_id as section_id' : 'NULL as section_id'),
            DB::raw($fpHasAcad   ? 'fp.academic_year as academic_year' : 'NULL as academic_year'),
            DB::raw($fpHasYear   ? 'fp.year as year' : 'NULL as year'),
            'fp.question_ids', 'fp.faculty_ids', 'fp.question_faculty', 'fp.student_ids',
            'fp.publish_at', 'fp.expire_at', 'fp.created_at', 'fp.updated_at',
        ];

        $postsQ = DB::table(self::POSTS . ' as fp')->select($select)->whereNull('fp.deleted_at');

        if ($fpHasDept && $deptId !== null)     $postsQ->where('fp.department_id', $deptId);
        if ($fpHasCourse && $courseId !== null) $postsQ->where('fp.course_id', $courseId);
        if ($fpHasSem && $semesterId !== null)  $postsQ->where('fp.semester_id', $semesterId);
        if ($fpHasSub && $subjectId !== null)   $postsQ->where('fp.subject_id', $subjectId);
        if ($fpHasSec && $sectionId !== null) {
            // When filtering by a section, include common/no-section posts too; they will be
            // broken down into that section's assigned students below.
            $postsQ->where(function ($w) use ($sectionId) {
                $w->where('fp.section_id', $sectionId)->orWhereNull('fp.section_id');
            });
        }
        if ($fpHasAcad && $acadYear !== '')     $postsQ->where('fp.academic_year', $acadYear);
        if ($fpHasYear && $year !== null)       $postsQ->where('fp.year', $year);

        // Department filter fallback through courses.department_id when posts.department_id is absent.
        if (!$fpHasDept && $deptId !== null && $hasCourses && $this->hasCol('courses','department_id') && $fpHasCourse) {
            $postsQ->whereIn('fp.course_id', array_keys(array_filter($courseDeptMap, fn($d) => (int)$d === (int)$deptId)));
        }

        $postsQ->orderBy('fp.id');
        if ($fpHasCourse) $postsQ->orderBy('fp.course_id');
        if ($fpHasAcad)   $postsQ->orderBy('fp.academic_year');
        if ($fpHasSem)    $postsQ->orderBy('fp.semester_id');
        if ($fpHasSec)    $postsQ->orderBy('fp.section_id');
        if ($fpHasSub)    $postsQ->orderBy('fp.subject_id');

        $postRows = $postsQ->get();

        if ($postRows->isEmpty()) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $postIds = $postRows->pluck('id')->map(fn($x)=>(int)$x)->unique()->values()->all();

        /* ---------------------------------------------------------
         | 2) Questions + faculty skeleton from post assignments.
         |--------------------------------------------------------- */
        $allQuestionIds = [];
        $allFacultyIds = [];
        $postMeta = [];

        foreach ($postRows as $p) {
            $pid = (int)$p->id;
            $qids = $this->normalizeIdList($p->question_ids ?? null);
            $fids = $this->normalizeIdList($p->faculty_ids ?? null);
            $qf = $this->normalizeJson($p->question_faculty ?? null);
            $studentIds = $this->normalizeIdList($p->student_ids ?? null);

            foreach ($qids as $qid) $allQuestionIds[] = $qid;
            foreach ($fids as $fid) $allFacultyIds[] = $fid;
            if (is_array($qf)) {
                foreach ($qf as $rule) {
                    if (is_array($rule) && array_key_exists('faculty_ids', $rule) && is_array($rule['faculty_ids'])) {
                        foreach ($this->normalizeIdList($rule['faculty_ids']) as $fid) $allFacultyIds[] = $fid;
                    }
                }
            }

            $scopedStudentIds = $this->assignedIdsWithAttendance($studentIds, $p, $fpHasDept, $fpHasCourse, $fpHasSem, $minAttendance);
            $assigned = count($scopedStudentIds);

            // Section-wise assigned students. For section_id = NULL posts, this spreads the post
            // across every real section with that section's students only.
            $sectionStudentIds = $this->sectionStudentBreakdown(
                $scopedStudentIds,
                $p,
                $fpHasDept,
                $fpHasCourse,
                $fpHasSem,
                $fpHasAcad,
                $fpHasYear
            );

            if ($sectionId !== null) {
                if (isset($sectionStudentIds[$sectionId])) {
                    $sectionStudentIds = [$sectionId => $sectionStudentIds[$sectionId]];
                } elseif (isset($sectionStudentIds[0])) {
                    // Fallback for old data without student_academic_details mapping.
                    $sectionStudentIds = [$sectionId => $sectionStudentIds[0]];
                } else {
                    $sectionStudentIds = [$sectionId => []];
                }
            }

            $postMeta[$pid] = [
                'question_ids' => $qids,
                'faculty_ids' => $fids,
                'question_faculty' => is_array($qf) ? $qf : null,
                'student_ids' => $scopedStudentIds,
                'assigned_students' => $assigned,
                'section_student_ids' => $sectionStudentIds,
            ];
        }

        $allQuestionIds = array_values(array_unique(array_filter(array_map('intval', $allQuestionIds), fn($x) => $x > 0)));
        sort($allQuestionIds);

        $questionMap = [];
        if (!empty($allQuestionIds) && $this->tableExists(self::QUESTIONS)) {
            $qTitleCol = $this->pickNameColumn(self::QUESTIONS, ['title','question','name'], 'id');
            $q = DB::table(self::QUESTIONS)->whereIn('id', $allQuestionIds)->select(['id', DB::raw("$qTitleCol as title")]);
            if ($this->hasCol(self::QUESTIONS, 'group_title')) $q->addSelect('group_title');
            if ($this->hasCol(self::QUESTIONS, 'deleted_at')) $q->whereNull('deleted_at');
            foreach ($q->get() as $row) {
                $qid = (int)($row->id ?? 0);
                if ($qid <= 0) continue;
                $questionMap[$qid] = [
                    'title' => (string)($row->title ?? ('Question #' . $qid)),
                    'group_title' => property_exists($row, 'group_title') ? $row->group_title : null,
                ];
            }
        }

        /* ---------------------------------------------------------
         | 3) Prepare nested output with ALL posts and zero values.
         |--------------------------------------------------------- */
        $out = [];
        $postRefMap = [];

        foreach ($postRows as $p) {
            $postId = (int)$p->id;
            $cId = $p->course_id !== null ? (int)$p->course_id : 0;
            $dId = $p->department_id !== null ? (int)$p->department_id : 0;
            if (!$dId && $cId && isset($courseDeptMap[$cId])) $dId = (int)$courseDeptMap[$cId];
            $semId = $p->semester_id !== null ? (int)$p->semester_id : 0;
            $sbId = $p->subject_id !== null ? (int)$p->subject_id : 0;

            $deptKey = (string)$dId;
            $courseKey = (string)$cId;
            $semKey = (string)$semId;
            $subKey = (string)$sbId;

            if (!isset($out[$deptKey])) {
                $out[$deptKey] = [
                    'department_id' => $dId ?: null,
                    'department_name' => ($dId && isset($deptMap[$dId])) ? (string)$deptMap[$dId] : null,
                    'courses' => [],
                ];
            }

            if (!isset($out[$deptKey]['courses'][$courseKey])) {
                $out[$deptKey]['courses'][$courseKey] = [
                    'course_id' => $cId ?: null,
                    'course_name' => ($cId && isset($courseMap[$cId])) ? (string)$courseMap[$cId] : null,
                    'semesters' => [],
                ];
            }

            if (!isset($out[$deptKey]['courses'][$courseKey]['semesters'][$semKey])) {
                $out[$deptKey]['courses'][$courseKey]['semesters'][$semKey] = [
                    'semester_id' => $semId ?: null,
                    'semester_name' => ($semId && isset($semMap[$semId])) ? (string)$semMap[$semId] : null,
                    'subjects' => [],
                ];
            }

            if (!isset($out[$deptKey]['courses'][$courseKey]['semesters'][$semKey]['subjects'][$subKey])) {
                $isFacility = $sbId <= 0;
                $out[$deptKey]['courses'][$courseKey]['semesters'][$semKey]['subjects'][$subKey] = [
                    'subject_id'   => $sbId ?: null,
                    'subject_code' => ($sbId && array_key_exists($sbId, $subCodeMap)) ? $subCodeMap[$sbId] : null,
                    'subject_name' => $isFacility ? 'Facility' : (($sbId && array_key_exists($sbId, $subLabelMap)) ? $subLabelMap[$sbId] : null),
                    'type'         => $isFacility ? 'facility' : 'subject',
                    'sections' => [],
                ];
            }

            $sectionStudentIds = $postMeta[$postId]['section_student_ids'] ?? [0 => ($postMeta[$postId]['student_ids'] ?? [])];
            if (!is_array($sectionStudentIds) || empty($sectionStudentIds)) {
                $rawSec = ($p->section_id !== null && $p->section_id !== '') ? (int)$p->section_id : 0;
                $sectionStudentIds = [$rawSec => ($postMeta[$postId]['student_ids'] ?? [])];
            }

            foreach ($sectionStudentIds as $secIdRaw => $assignedIdsForSection) {
                $secId = (int)$secIdRaw;
                $assignedIdsForSection = array_values(array_unique(array_filter(array_map('intval', (array)$assignedIdsForSection), fn($x) => $x > 0)));
                sort($assignedIdsForSection);

                $secKey = (string)$secId;
                $postKey = $postId . ':' . $secKey;

                if (!isset($out[$deptKey]['courses'][$courseKey]['semesters'][$semKey]['subjects'][$subKey]['sections'][$secKey])) {
                    $out[$deptKey]['courses'][$courseKey]['semesters'][$semKey]['subjects'][$subKey]['sections'][$secKey] = [
                        'section_id' => $secId ?: null,
                        'section_name' => ($secId && isset($secMap[$secId])) ? (string)$secMap[$secId] : null,
                        'feedback_posts' => [],
                    ];
                }

                $secRef =& $out[$deptKey]['courses'][$courseKey]['semesters'][$semKey]['subjects'][$subKey]['sections'][$secKey];
                $assigned = count($assignedIdsForSection);

                $secRef['feedback_posts'][$postKey] = [
                    'feedback_post_id' => $postId,
                    'result_key' => $postKey,
                    'feedback_result_key' => $postKey,
                    'feedback_post_uuid' => (string)($p->uuid ?? ''),
                    'title' => (string)($p->title ?? ''),
                    'short_title' => $p->short_title !== null ? (string)$p->short_title : null,
                    'description' => $p->description,
                    'publish_at' => $p->publish_at,
                    'expire_at'  => $p->expire_at,
                    'academic_year' => $p->academic_year ?? null,
                    'year' => $p->year ?? null,

                    // Section-scoped API fields.
                    'given_students'              => 0,
                    'participated_students'       => 0,
                    'assigned_students'           => $assigned,
                    'eligible_students'           => $assigned,
                    'total_assigned_students'     => $assigned,
                    'response_rate'               => 0.0,
                    'response_percentage'         => 0.0,
                    'feedback_given_percentage'   => 0.0,

                    // Section-scoped IDs used by UI for unique progress.
                    'assigned_student_ids'        => $assignedIdsForSection,
                    'given_student_ids'           => [],

                    'questions' => [],

                    // internal only until final cleanup
                    '_faculty_ids' => $postMeta[$postId]['faculty_ids'] ?? [],
                    '_question_faculty' => $postMeta[$postId]['question_faculty'] ?? null,
                    '_assigned_student_lookup' => $assignedIdsForSection,
                    '_section_clone_key' => $postKey,
                ];

                $postRef =& $secRef['feedback_posts'][$postKey];
                foreach (($postMeta[$postId]['question_ids'] ?? []) as $qid) {
                    $this->addQuestionSkeleton($postRef, (int)$qid, $questionMap, $this->facultyIdsForQuestion($postRef, (int)$qid));
                }

                if (!isset($postRefMap[$postId])) $postRefMap[$postId] = [];
                $postRefMap[$postId][$postKey] =& $postRef;

                unset($postRef);
                unset($secRef);
            }
        }

        /* ---------------------------------------------------------
         | 4) Fetch submitted rows, count given, and fill distributions.
         |--------------------------------------------------------- */
        if (!empty($postIds) && $this->tableExists(self::SUBS)) {
            $subQ = DB::table(self::SUBS . ' as fs')
                ->join(self::POSTS . ' as fp', 'fp.id', '=', 'fs.feedback_post_id')
                ->whereIn('fs.feedback_post_id', $postIds)
                ->whereNull('fs.deleted_at')
                ->whereNull('fp.deleted_at')
                ->select(['fs.id', 'fs.feedback_post_id', 'fs.answers']);

            if ($fsHasStudent) $subQ->addSelect('fs.student_id');
            if ($fsHasStatus) {
                $subQ->where(function($w){ $w->whereNull('fs.status')->orWhere('fs.status', 'submitted'); });
            }

            if ($minAttendance !== null && $this->tableExists('student_subject') && $fsHasStudent && $fpHasSub) {
                $subQ->whereExists(function($ex) use ($fpHasDept, $fpHasCourse, $fpHasSem, $minAttendance) {
                    $ex->select(DB::raw(1))
                        ->from('student_subject as ss')
                        ->crossJoin(DB::raw("JSON_TABLE(ss.subject_json, '$[*]' COLUMNS (student_id INT PATH '$.student_id', subject_id INT PATH '$.subject_id', current_attendance DECIMAL(6,2) PATH '$.current_attendance')) sj"))
                        ->whereColumn('sj.student_id', 'fs.student_id')
                        ->whereColumn('sj.subject_id', 'fp.subject_id')
                        ->where('sj.current_attendance', '>=', $minAttendance);

                    if ($this->hasCol('student_subject','deleted_at')) $ex->whereNull('ss.deleted_at');
                    if ($this->hasCol('student_subject','status')) {
                        $ex->where(function($w){ $w->whereNull('ss.status')->orWhere('ss.status', 'active'); });
                    }
                    if ($fpHasDept && $this->hasCol('student_subject','department_id')) $ex->whereColumn('ss.department_id', 'fp.department_id');
                    if ($fpHasCourse && $this->hasCol('student_subject','course_id')) $ex->whereColumn('ss.course_id', 'fp.course_id');
                    if ($fpHasSem && $this->hasCol('student_subject','semester_id')) $ex->whereRaw('ss.semester_id <=> fp.semester_id');
                });
            }

            $givenSeen = [];

            foreach ($subQ->get() as $fs) {
                $postId = (int)($fs->feedback_post_id ?? 0);
                if ($postId <= 0 || !isset($postRefMap[$postId]) || !is_array($postRefMap[$postId])) continue;

                $studentId = ($fsHasStudent && isset($fs->student_id) && is_numeric($fs->student_id))
                    ? (int)$fs->student_id
                    : 0;

                $studentKey = $studentId > 0
                    ? (string)$studentId
                    : ('submission_' . (int)$fs->id);

                $answerRows = $this->extractAnswerRows($fs->answers ?? null);
                if (empty($answerRows)) continue;

                foreach ($postRefMap[$postId] as $cloneKey => &$postRef) {
                    $assignedLookup = is_array($postRef['_assigned_student_lookup'] ?? null)
                        ? array_values(array_unique(array_map('intval', $postRef['_assigned_student_lookup'])))
                        : [];

                    // For common posts cloned into sections, count a submission only in the
                    // clone whose section contains that student.
                    if ($studentId > 0 && !empty($assignedLookup) && !in_array($studentId, $assignedLookup, true)) {
                        continue;
                    }

                    if (!isset($givenSeen[$postId])) $givenSeen[$postId] = [];
                    if (!isset($givenSeen[$postId][$cloneKey])) $givenSeen[$postId][$cloneKey] = [];
                    $givenSeen[$postId][$cloneKey][$studentKey] = true;

                    foreach ($answerRows as $ar) {
                        $qid = (int)$ar['question_id'];
                        $fid = (int)$ar['faculty_id'];
                        $stars = (int)$ar['stars'];
                        if ($qid <= 0 || $stars < 1 || $stars > 5) continue;

                        $this->addQuestionSkeleton($postRef, $qid, $questionMap, $this->facultyIdsForQuestion($postRef, $qid));

                        $qKey = (string)$qid;
                        $fKey = (string)max(0, $fid);

                        $postRef['questions'][$qKey]['distribution']['counts'][(string)$stars]++;
                        $postRef['questions'][$qKey]['distribution']['total']++;

                        if (!isset($postRef['questions'][$qKey]['faculty'][$fKey])) {
                            $postRef['questions'][$qKey]['faculty'][$fKey] = [
                                'faculty_id'      => $fid <= 0 ? 0 : $fid,
                                'faculty_name'    => $fid <= 0 ? 'Overall' : ('Faculty #' . $fid),
                                'name_short_form' => null,
                                'employee_id'     => null,
                                'avg_rating'      => null,
                                'count'           => 0,
                                'out_of'          => 5,
                                'distribution'    => $this->initDist(),
                            ];
                        }

                        $postRef['questions'][$qKey]['faculty'][$fKey]['distribution']['counts'][(string)$stars]++;
                        $postRef['questions'][$qKey]['faculty'][$fKey]['distribution']['total']++;

                        if ($fid > 0) $allFacultyIds[] = $fid;
                    }
                }
                unset($postRef);
            }

            foreach ($givenSeen as $postId => $cloneRows) {
                if (!isset($postRefMap[$postId]) || !is_array($postRefMap[$postId])) continue;

                foreach ($cloneRows as $cloneKey => $students) {
                    if (!isset($postRefMap[$postId][$cloneKey])) continue;

                    $given = count($students);
                    $assigned = (int)($postRefMap[$postId][$cloneKey]['assigned_students'] ?? 0);
                    $rate = $this->responseRate($given, $assigned);

                    $postRefMap[$postId][$cloneKey]['given_students'] = $given;
                    $postRefMap[$postId][$cloneKey]['participated_students'] = $given;
                    $postRefMap[$postId][$cloneKey]['response_rate'] = $rate;
                    $postRefMap[$postId][$cloneKey]['response_percentage'] = $rate;
                    $postRefMap[$postId][$cloneKey]['feedback_given_percentage'] = $rate;

                    $givenIds = [];
                    foreach (array_keys($students) as $sidKey) {
                        if (is_numeric($sidKey) && (int)$sidKey > 0) $givenIds[] = (int)$sidKey;
                    }
                    $givenIds = array_values(array_unique($givenIds));
                    sort($givenIds);
                    $postRefMap[$postId][$cloneKey]['given_student_ids'] = $givenIds;
                }
            }
        }

        /* ---------------------------------------------------------
         | 5) Apply faculty names, finalize averages, normalize arrays.
         |--------------------------------------------------------- */
        $facultyInfoMap = $this->fetchFacultyInfo($allFacultyIds);

        foreach ($out as &$dept) {
            foreach ($dept['courses'] as &$course) {
                foreach ($course['semesters'] as &$sem) {
                    foreach ($sem['subjects'] as &$sub) {
                        foreach ($sub['sections'] as &$sec) {
                            foreach ($sec['feedback_posts'] as &$post) {
                                foreach ($post['questions'] as &$q) {
                                    $this->finalizeDist($q['distribution']);
                                    $overallTotal = (int)($q['distribution']['total'] ?? 0);

                                    foreach ($q['faculty'] as &$frow) {
                                        $fid = (int)($frow['faculty_id'] ?? 0);
                                        if ($fid > 0 && isset($facultyInfoMap[$fid])) {
                                            $frow['faculty_name'] = $facultyInfoMap[$fid]['name'] ?? ('Faculty #' . $fid);
                                            $frow['name_short_form'] = $facultyInfoMap[$fid]['name_short_form'] ?? null;
                                            $frow['employee_id'] = $facultyInfoMap[$fid]['employee_id'] ?? null;
                                        }
                                        $this->finalizeDist($frow['distribution']);
                                        $frow['count'] = (int)($frow['distribution']['total'] ?? 0);
                                        $frow['avg_rating'] = $frow['distribution']['avg'];
                                    }
                                    unset($frow);

                                    $q['faculty']['0'] = [
                                        'faculty_id'      => 0,
                                        'faculty_name'    => 'Overall',
                                        'name_short_form' => null,
                                        'employee_id'     => null,
                                        'avg_rating'      => $q['distribution']['avg'],
                                        'count'           => $overallTotal,
                                        'out_of'          => 5,
                                        'distribution'    => $q['distribution'],
                                    ];

                                    ksort($q['faculty'], SORT_NATURAL);
                                }
                                unset($q);

                                unset($post['_faculty_ids'], $post['_question_faculty'], $post['_assigned_student_lookup'], $post['_section_clone_key']);
                            }
                            unset($post);
                        }
                        unset($sec);
                    }
                    unset($sub);
                }
                unset($sem);
            }
            unset($course);
        }
        unset($dept);

        $final = array_values(array_map(function ($dept) {
            $dept['courses'] = array_values(array_map(function ($course) {
                $course['semesters'] = array_values(array_map(function ($sem) {
                    $sem['subjects'] = array_values(array_map(function ($sub) {
                        $sub['sections'] = array_values(array_map(function ($sec) {
                            $sec['feedback_posts'] = array_values(array_map(function ($post) {
                                $post['questions'] = array_values(array_map(function ($q) {
                                    $q['faculty'] = array_values($q['faculty']);
                                    return $q;
                                }, $post['questions']));
                                return $post;
                            }, $sec['feedback_posts']));
                            return $sec;
                        }, $sub['sections']));
                        return $sub;
                    }, $sem['subjects']));
                    return $sem;
                }, $course['semesters']));
                return $course;
            }, $dept['courses']));
            return $dept;
        }, $out));

        return response()->json([
            'success' => true,
            'data' => $final,
        ]);
    }
}
