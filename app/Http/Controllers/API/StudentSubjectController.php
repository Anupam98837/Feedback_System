<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class StudentSubjectController extends Controller
{
    private const TABLE            = 'student_subject';
    private const TABLE_USERS      = 'users';
    private const TABLE_DEPTS      = 'departments';
    private const TABLE_COURSES    = 'courses';
    private const TABLE_SEMESTERS  = 'course_semesters';
    private const TABLE_ACTIVITY   = 'user_data_activity_log';

    private const COL_UUID         = 'uuid';
    private const COL_DELETED_AT   = 'deleted_at';

    /* ============================================
     | Access Control
     |============================================ */
    private function accessControl(int $userId): array
    {
        if ($userId <= 0) {
            return ['mode' => 'none', 'department_id' => null];
        }

        if (!Schema::hasColumn('users', 'department_id')) {
            return ['mode' => 'not_allowed', 'department_id' => null];
        }

        $q = DB::table('users')->select(['id', 'role', 'department_id', 'status']);

        if (Schema::hasColumn('users', 'deleted_at')) {
            $q->whereNull('deleted_at');
        }

        $u = $q->where('id', $userId)->first();

        if (!$u) {
            return ['mode' => 'none', 'department_id' => null];
        }

        if (isset($u->status) && (string)$u->status !== 'active') {
            return ['mode' => 'none', 'department_id' => null];
        }

        $role = strtolower(trim((string)($u->role ?? '')));
        $role = str_replace([' ', '-'], '_', $role);
        $role = preg_replace('/_+/', '_', $role) ?? $role;

        $deptId = $u->department_id !== null ? (int)$u->department_id : null;
        if ($deptId !== null && $deptId <= 0) {
            $deptId = null;
        }

        $adminRoles = ['admin', 'super_admin', 'director', 'principal'];
        if (in_array($role, $adminRoles, true)) {
            return ['mode' => 'all', 'department_id' => null];
        }

        if ($deptId !== null) {
            return ['mode' => 'department', 'department_id' => $deptId];
        }

        return ['mode' => 'none', 'department_id' => null];
    }

    /* ============================================
     | Helpers
     |============================================ */
    private function actor(Request $r): array
    {
        return [
            'id'   => (int) ($r->attributes->get('auth_tokenable_id') ?? optional($r->user())->id ?? 0),
            'role' => (string) ($r->attributes->get('auth_role') ?? ($r->user()->role ?? '')),
            'type' => (string) ($r->attributes->get('auth_tokenable_type') ?? ($r->user() ? get_class($r->user()) : '')),
            'uuid' => (string) ($r->attributes->get('auth_user_uuid') ?? ($r->user()->uuid ?? '')),
            'ip'   => (string) ($r->ip() ?? ''),
        ];
    }

    private function now(): string
    {
        return Carbon::now()->toDateTimeString();
    }

    private function rid(Request $r): string
    {
        $rid = (string) ($r->attributes->get('_rid') ?? '');
        if ($rid !== '') {
            return $rid;
        }

        $rid = (string) ($r->header('X-Request-Id') ?? '');
        if ($rid === '') {
            $rid = (string) Str::uuid();
        }

        $r->attributes->set('_rid', $rid);
        return $rid;
    }

    private function reqMeta(Request $r, array $actor = []): array
    {
        return [
            'rid'    => $this->rid($r),
            'path'   => $r->path(),
            'method' => $r->method(),
            'ip'     => $r->ip(),
            'ua'     => (string) ($r->userAgent() ?? ''),
            'actor'  => $actor ?: $this->actor($r),
            'query'  => $r->query(),
        ];
    }

    private function logInfo(string $msg, array $ctx = []): void
    {
        Log::info('[StudentSubject] ' . $msg, $ctx);
    }

    private function logWarn(string $msg, array $ctx = []): void
    {
        Log::warning('[StudentSubject] ' . $msg, $ctx);
    }

    private function logErr(string $msg, array $ctx = []): void
    {
        Log::error('[StudentSubject] ' . $msg, $ctx);
    }

    private function isNumericId($v): bool
    {
        return is_string($v) || is_int($v)
            ? preg_match('/^\d+$/', (string)$v) === 1
            : false;
    }

    private function normalizeIdentifier(string $idOrUuid, ?string $alias = 'ss'): array
    {
        $idOrUuid = trim($idOrUuid);

        $rawCol = $this->isNumericId($idOrUuid) ? 'id' : self::COL_UUID;
        $val    = ($rawCol === 'id') ? (int) $idOrUuid : $idOrUuid;

        $prefix = ($alias !== null && $alias !== '') ? ($alias . '.') : '';

        return [
            'col'     => $prefix . $rawCol,
            'raw_col' => $rawCol,
            'val'     => $val,
        ];
    }

    private function normalizeJsonToString($value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value)) {
            try {
                return json_encode($value, JSON_UNESCAPED_UNICODE);
            } catch (\Throwable $e) {
                return null;
            }
        }

        if (is_string($value)) {
            $trim = trim($value);
            if ($trim === '') {
                return null;
            }

            json_decode($trim, true);
            return (json_last_error() === JSON_ERROR_NONE) ? $trim : null;
        }

        return null;
    }

    private function decodeJson($value)
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value)) {
            return $value;
        }

        if (is_string($value)) {
            $d = json_decode($value, true);
            return (json_last_error() === JSON_ERROR_NONE) ? $d : $value;
        }

        return $value;
    }

    private function aStr(?string $s, int $max): ?string
    {
        if ($s === null) {
            return null;
        }

        $s = (string) $s;
        if ($s === '') {
            return '';
        }

        return mb_substr($s, 0, $max);
    }

    private function toJsonOrNull($value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_object($value)) {
            $value = json_decode(json_encode($value), true);
        }

        if (is_string($value)) {
            $trim = trim($value);
            if ($trim === '') {
                return null;
            }

            json_decode($trim, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $trim;
            }

            return json_encode(['value' => $value], JSON_UNESCAPED_UNICODE);
        }

        try {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function snapshotRow($row): array
    {
        if (!$row) {
            return [];
        }

        $arr = is_array($row) ? $row : (array) $row;

        $keep = [
            'id','uuid',
            'department_id','course_id','semester_id',
            'subject_json','status','metadata',
            'created_by','created_at','updated_at','deleted_at',
            'created_at_ip','updated_at_ip',
        ];

        $out = [];
        foreach ($keep as $k) {
            if (array_key_exists($k, $arr)) {
                $out[$k] = $arr[$k];
            }
        }

        return $out;
    }

    private function diffSnapshots(array $old, array $new, array $watchKeys): array
    {
        $changed = [];
        $oldOut  = [];
        $newOut  = [];

        foreach ($watchKeys as $k) {
            $ov = $old[$k] ?? null;
            $nv = $new[$k] ?? null;

            if ($ov !== $nv) {
                $changed[]  = $k;
                $oldOut[$k] = $ov;
                $newOut[$k] = $nv;
            }
        }

        return [$changed, $oldOut, $newOut];
    }

    private function activityLog(
        Request $r,
        string $activity,
        string $module,
        string $tableName,
        ?int $recordId = null,
        $changedFields = null,
        $oldValues = null,
        $newValues = null,
        ?string $note = null
    ): void {
        try {
            if (!Schema::hasTable(self::TABLE_ACTIVITY)) {
                return;
            }

            $actor = $this->actor($r);

            DB::table(self::TABLE_ACTIVITY)->insert([
                'performed_by'      => (int) ($actor['id'] ?? 0),
                'performed_by_role' => $this->aStr((string)($actor['role'] ?? ''), 50),
                'ip'                => $this->aStr((string)($r->ip() ?? ''), 45),
                'user_agent'        => $this->aStr((string)($r->userAgent() ?? ''), 512),
                'activity'          => $this->aStr($activity, 50),
                'module'            => $this->aStr($module, 100),
                'table_name'        => $this->aStr($tableName, 128),
                'record_id'         => $recordId !== null ? (int)$recordId : null,
                'changed_fields'    => $this->toJsonOrNull($changedFields),
                'old_values'        => $this->toJsonOrNull($oldValues),
                'new_values'        => $this->toJsonOrNull($newValues),
                'log_note'          => $note,
                'created_at'        => $this->now(),
                'updated_at'        => $this->now(),
            ]);
        } catch (\Throwable $e) {
            $this->logWarn('ACTIVITY_LOG: failed to write', [
                'error'  => $e->getMessage(),
                'path'   => $r->path(),
                'method' => $r->method(),
            ]);
        }
    }

    private function baseQuery(bool $includeDeleted = false)
    {
        $q = DB::table(self::TABLE . ' as ss')
            ->leftJoin(self::TABLE_DEPTS . ' as d', 'd.id', '=', 'ss.department_id')
            ->leftJoin(self::TABLE_COURSES . ' as c', 'c.id', '=', 'ss.course_id')
            ->leftJoin(self::TABLE_SEMESTERS . ' as cs', 'cs.id', '=', 'ss.semester_id')
            ->leftJoin(self::TABLE_USERS . ' as u', 'u.id', '=', 'ss.created_by')
            ->select([
                'ss.id',
                'ss.uuid',
                'ss.department_id',
                'ss.course_id',
                'ss.semester_id',
                'ss.subject_json',
                'ss.status',
                'ss.created_by',
                'ss.created_at_ip',
                'ss.updated_at_ip',
                'ss.metadata',
                'ss.created_at',
                'ss.updated_at',
                'ss.deleted_at',
                'd.title as department_title',
                'c.title as course_title',
                'cs.title as semester_title',
                'u.name as created_by_name',
                'u.email as created_by_email',
                'u.role as created_by_role',
            ]);

        if (!$includeDeleted) {
            $q->whereNull('ss.' . self::COL_DELETED_AT);
        }

        return $q;
    }

    private function presentRow($row): array
    {
        return [
            'id'   => (int) $row->id,
            'uuid' => (string) $row->uuid,

            'department_id' => (int) $row->department_id,
            'course_id'     => (int) $row->course_id,
            'semester_id'   => $row->semester_id !== null ? (int) $row->semester_id : null,

            'subject_json' => $this->decodeJson($row->subject_json),
            'status'       => (string) $row->status,

            'scope' => [
                'department' => $row->department_id ? ['id' => (int)$row->department_id, 'title' => $row->department_title] : null,
                'course'     => $row->course_id ? ['id' => (int)$row->course_id, 'title' => $row->course_title] : null,
                'semester'   => $row->semester_id ? ['id' => (int)$row->semester_id, 'title' => $row->semester_title] : null,
            ],

            'metadata' => $this->decodeJson($row->metadata),

            'created_by' => $row->created_by !== null ? [
                'id'    => (int) $row->created_by,
                'name'  => $row->created_by_name,
                'email' => $row->created_by_email,
                'role'  => $row->created_by_role,
            ] : null,

            'created_at'    => $row->created_at,
            'updated_at'    => $row->updated_at,
            'deleted_at'    => $row->deleted_at,
            'created_at_ip' => $row->created_at_ip,
            'updated_at_ip' => $row->updated_at_ip,
        ];
    }

    /* ============================================
     | Concurrency + subject_json merge helpers
     |============================================ */
    private function scopeLockKey(int $departmentId, int $courseId, ?int $semesterId): string
    {
        return 'student_subject_scope:' . $departmentId . ':' . $courseId . ':' . ($semesterId ?? 0);
    }

    private function acquireScopeLock(string $key, int $timeout = 10): bool
    {
        try {
            $driver = DB::getDriverName();

            if (in_array($driver, ['mysql', 'mariadb'], true)) {
                $row = DB::selectOne('SELECT GET_LOCK(?, ?) as lock_status', [$key, $timeout]);
                return isset($row->lock_status) && (int)$row->lock_status === 1;
            }

            return true;
        } catch (\Throwable $e) {
            $this->logWarn('LOCK: acquire failed', [
                'key'   => $key,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    private function releaseScopeLock(string $key): void
    {
        try {
            $driver = DB::getDriverName();

            if (in_array($driver, ['mysql', 'mariadb'], true)) {
                DB::selectOne('SELECT RELEASE_LOCK(?) as release_status', [$key]);
            }
        } catch (\Throwable $e) {
            $this->logWarn('LOCK: release failed', [
                'key'   => $key,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function acquireScopeLocks(array $keys, int $timeout = 10): array
    {
        $keys = array_values(array_unique(array_filter($keys)));
        sort($keys, SORT_STRING);

        $acquired = [];

        foreach ($keys as $key) {
            if (!$this->acquireScopeLock($key, $timeout)) {
                foreach (array_reverse($acquired) as $held) {
                    $this->releaseScopeLock($held);
                }
                return [false, []];
            }
            $acquired[] = $key;
        }

        return [true, $acquired];
    }

    private function releaseScopeLocks(array $keys): void
    {
        $keys = array_values(array_unique(array_filter($keys)));
        foreach (array_reverse($keys) as $key) {
            $this->releaseScopeLock($key);
        }
    }

    private function findActiveScopeRowForUpdate(int $departmentId, int $courseId, ?int $semesterId, ?int $ignoreId = null)
    {
        $q = DB::table(self::TABLE)
            ->where('department_id', $departmentId)
            ->where('course_id', $courseId)
            ->whereNull(self::COL_DELETED_AT);

        if ($semesterId === null) {
            $q->whereNull('semester_id');
        } else {
            $q->where('semester_id', $semesterId);
        }

        if ($ignoreId !== null) {
            $q->where('id', '!=', $ignoreId);
        }

        return $q->lockForUpdate()->orderBy('id', 'desc')->first();
    }

    private function parseSubjectJsonInput($value): ?array
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
                return null;
            }
            $value = $decoded;
        }

        if (!is_array($value)) {
            return null;
        }

        $normalized = [];

        foreach ($value as $idx => $row) {
            if (!is_array($row)) {
                return null;
            }

            if (!array_key_exists('student_id', $row) || !array_key_exists('subject_id', $row) || !array_key_exists('current_attendance', $row)) {
                return null;
            }

            if (!is_numeric($row['student_id']) || !is_numeric($row['subject_id']) || !is_numeric($row['current_attendance'])) {
                return null;
            }

            $studentId  = (int) $row['student_id'];
            $subjectId  = (int) $row['subject_id'];
            $attendance = (float) $row['current_attendance'];

            if ($studentId <= 0 || $subjectId <= 0 || $attendance < 0 || $attendance > 100) {
                return null;
            }

            $item = $row;
            $item['student_id'] = $studentId;
            $item['subject_id'] = $subjectId;

            if (floor($attendance) == $attendance) {
                $attendance = (int) $attendance;
            }
            $item['current_attendance'] = $attendance;

            $normalized[] = $item;
        }

        return $normalized;
    }

    private function decodeSubjectJsonToArray($value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        if (is_array($value)) {
            return $this->parseSubjectJsonInput($value) ?? [];
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
                return [];
            }
            return $this->parseSubjectJsonInput($decoded) ?? [];
        }

        return [];
    }

    private function subjectItemKey(array $item): string
    {
        return ((int)$item['student_id']) . ':' . ((int)$item['subject_id']);
    }

    private function mergeSubjectJson($existingValue, $incomingValue): ?string
    {
        $incoming = $this->parseSubjectJsonInput($incomingValue);
        if ($incoming === null) {
            return null;
        }

        $existing = $this->decodeSubjectJsonToArray($existingValue);

        $map = [];
        foreach ($existing as $item) {
            $map[$this->subjectItemKey($item)] = $item;
        }

        foreach ($incoming as $item) {
            $map[$this->subjectItemKey($item)] = $item; // incoming wins
        }

        $merged = array_values($map);

        usort($merged, function ($a, $b) {
            $s = ($a['student_id'] <=> $b['student_id']);
            if ($s !== 0) return $s;
            return $a['subject_id'] <=> $b['subject_id'];
        });

        return json_encode($merged, JSON_UNESCAPED_UNICODE);
    }

    /* =========================================================
     | LIST
     |========================================================= */
    public function index(Request $r)
    {
        $actor = $this->actor($r);
        $meta  = $this->reqMeta($r, $actor);

        $actorId = (int) ($r->attributes->get('auth_tokenable_id') ?? $actor['id'] ?? 0);
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed') {
            return response()->json(['success' => false, 'error' => 'Not allowed'], 403);
        }

        $qText  = trim((string)$r->query('q', ''));
        $status = trim((string)$r->query('status', ''));

        $departmentId = $r->query('department_id', null);
        $courseId     = $r->query('course_id', null);
        $semesterId   = $r->query('semester_id', null);

        $page = max(1, (int)$r->query('page', 1));
        $per  = min(100, max(5, (int)$r->query('per_page', 20)));

        $sort = (string)$r->query('sort', 'created_at');
        $dir  = strtolower((string)$r->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $allowedSort = ['created_at','updated_at','status','id'];
        if (!in_array($sort, $allowedSort, true)) {
            $sort = 'created_at';
        }

        if ($ac['mode'] === 'none') {
            return response()->json([
                'success' => true,
                'data' => [],
                'pagination' => [
                    'page' => $page,
                    'per_page' => $per,
                    'total' => 0,
                    'last_page' => 1,
                ],
            ], 200);
        }

        if ($ac['mode'] === 'department') {
            $departmentId = (int) $ac['department_id'];
        }

        $this->logInfo('INDEX: request received', $meta + [
            'q'        => $qText,
            'status'   => $status,
            'page'     => $page,
            'per_page' => $per,
            'ac'       => $ac,
        ]);

        try {
            $q = $this->baseQuery(false);

            if ($ac['mode'] === 'department') {
                $q->where('ss.department_id', (int) $ac['department_id']);
            }

            if ($qText !== '') {
                $q->where(function ($w) use ($qText) {
                    $w->where('ss.uuid', 'like', "%{$qText}%")
                      ->orWhere('d.title', 'like', "%{$qText}%")
                      ->orWhere('c.title', 'like', "%{$qText}%")
                      ->orWhere('cs.title', 'like', "%{$qText}%");
                });
            }

            if ($status !== '') {
                $q->where('ss.status', $status);
            }

            if ($ac['mode'] === 'all') {
                if ($departmentId !== null && $departmentId !== '') {
                    $q->where('ss.department_id', (int)$departmentId);
                }
            }

            if ($courseId !== null && $courseId !== '') {
                $q->where('ss.course_id', (int)$courseId);
            }

            if ($semesterId !== null && $semesterId !== '') {
                $q->where('ss.semester_id', (int)$semesterId);
            }

            $total = (clone $q)->count('ss.id');

            $q->orderBy("ss.$sort", $dir)->orderBy('ss.id', 'desc');

            $rows = $q->forPage($page, $per)->get();
            $data = $rows->map(fn($row) => $this->presentRow($row))->values();

            return response()->json([
                'success' => true,
                'data' => $data,
                'pagination' => [
                    'page'      => $page,
                    'per_page'  => $per,
                    'total'     => $total,
                    'last_page' => (int) ceil(max(1, $total) / max(1, $per)),
                ],
            ]);
        } catch (\Throwable $e) {
            $this->logErr('INDEX: failed', $meta + ['error' => $e->getMessage(), 'ac' => $ac]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load student subjects',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /* =========================================================
     | CURRENT
     |========================================================= */
    public function current(Request $r)
    {
        $actor = $this->actor($r);
        $meta  = $this->reqMeta($r, $actor);

        $actorId = (int) ($r->attributes->get('auth_tokenable_id') ?? $actor['id'] ?? 0);
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed') {
            return response()->json(['success' => false, 'error' => 'Not allowed'], 403);
        }

        if ($ac['mode'] === 'none') {
            return response()->json(['success' => true, 'data' => []], 200);
        }

        $departmentId = $r->query('department_id', null);
        $courseId     = $r->query('course_id', null);
        $semesterId   = $r->query('semester_id', null);

        if ($ac['mode'] === 'department') {
            $departmentId = (int) $ac['department_id'];
        }

        $this->logInfo('CURRENT: request received', $meta + ['ac' => $ac]);

        try {
            $q = $this->baseQuery(false)->where('ss.status', 'active');

            if ($ac['mode'] === 'department') {
                $q->where('ss.department_id', (int) $ac['department_id']);
            }

            if ($ac['mode'] === 'all') {
                if ($departmentId !== null && $departmentId !== '') {
                    $q->where('ss.department_id', (int)$departmentId);
                }
            }

            if ($courseId !== null && $courseId !== '') {
                $q->where('ss.course_id', (int)$courseId);
            }

            if ($semesterId !== null && $semesterId !== '') {
                $q->where('ss.semester_id', (int)$semesterId);
            }

            $rows = $q->orderBy('ss.id', 'desc')->get();
            $data = $rows->map(fn($row) => $this->presentRow($row))->values();

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            $this->logErr('CURRENT: failed', $meta + ['error' => $e->getMessage(), 'ac' => $ac]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load current student subjects',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /* =========================================================
     | TRASH
     |========================================================= */
    public function trash(Request $r)
    {
        $actor = $this->actor($r);
        $meta  = $this->reqMeta($r, $actor);

        $actorId = (int) ($r->attributes->get('auth_tokenable_id') ?? $actor['id'] ?? 0);
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed') {
            return response()->json(['success' => false, 'error' => 'Not allowed'], 403);
        }

        if ($ac['mode'] === 'none') {
            return response()->json(['success' => true, 'data' => []], 200);
        }

        $this->logInfo('TRASH: request received', $meta + ['ac' => $ac]);

        try {
            $q = $this->baseQuery(true)->whereNotNull('ss.deleted_at');

            if ($ac['mode'] === 'department') {
                $q->where('ss.department_id', (int) $ac['department_id']);
            }

            $rows = $q->orderBy('ss.deleted_at', 'desc')->get();
            $data = $rows->map(fn($row) => $this->presentRow($row))->values();

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            $this->logErr('TRASH: failed', $meta + ['error' => $e->getMessage(), 'ac' => $ac]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load trash',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /* =========================================================
     | SHOW
     |========================================================= */
    public function show(Request $r, string $idOrUuid)
    {
        $actor = $this->actor($r);
        $meta  = $this->reqMeta($r, $actor);

        $actorId = (int) ($r->attributes->get('auth_tokenable_id') ?? $actor['id'] ?? 0);
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed') {
            return response()->json(['success' => false, 'error' => 'Not allowed'], 403);
        }

        if ($ac['mode'] === 'none') {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }

        $this->logInfo('SHOW: request received', $meta + ['id_or_uuid' => $idOrUuid, 'ac' => $ac]);

        try {
            $w = $this->normalizeIdentifier($idOrUuid, 'ss');
            $q = $this->baseQuery(false)->where($w['col'], $w['val']);

            if ($ac['mode'] === 'department') {
                $q->where('ss.department_id', (int) $ac['department_id']);
            }

            $row = $q->first();

            if (!$row) {
                return response()->json(['success' => false, 'message' => 'Not found'], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $this->presentRow($row),
            ]);
        } catch (\Throwable $e) {
            $this->logErr('SHOW: failed', $meta + ['error' => $e->getMessage(), 'ac' => $ac]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load record',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /* =========================================================
     | CREATE
     |========================================================= */
    public function store(Request $r)
    {
        $actor = $this->actor($r);
        $meta  = $this->reqMeta($r, $actor);

        $this->logInfo('STORE: request received', $meta);

        $v = Validator::make($r->all(), [
            'department_id' => ['required','integer','exists:' . self::TABLE_DEPTS . ',id'],
            'course_id'     => ['required','integer','exists:' . self::TABLE_COURSES . ',id'],
            'semester_id'   => ['nullable','integer','exists:' . self::TABLE_SEMESTERS . ',id'],

            'subject_json'                      => ['required'],
            'subject_json.*.student_id'         => ['required','integer','min:1'],
            'subject_json.*.subject_id'         => ['required','integer','min:1'],
            'subject_json.*.current_attendance' => ['required','numeric','min:0','max:100'],

            'replace_subject_json' => ['nullable','boolean'],
            'status'               => ['nullable','string','max:20', Rule::in(['active','inactive'])],
            'metadata'             => ['nullable'],
        ]);

        if ($v->fails()) {
            $this->logWarn('STORE: validation failed', $meta + ['errors' => $v->errors()->toArray()]);

            $this->activityLog(
                $r,
                'create',
                'student_subjects',
                self::TABLE,
                null,
                array_keys($v->errors()->toArray()),
                null,
                ['errors' => $v->errors()->toArray()],
                'validation_failed'
            );

            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors'  => $v->errors(),
            ], 422);
        }

        $lockKeys = [];

        try {
            if ((int)$actor['id'] <= 0) {
                $this->activityLog($r, 'create', 'student_subjects', self::TABLE, null, null, null, null, 'unauthenticated');
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            $actorId = (int) ($r->attributes->get('auth_tokenable_id') ?? $actor['id'] ?? 0);
            $ac = $this->accessControl($actorId);

            if ($ac['mode'] === 'not_allowed' || $ac['mode'] === 'none') {
                $this->activityLog($r, 'create', 'student_subjects', self::TABLE, null, null, null, null, 'not_allowed');
                return response()->json(['success' => false, 'error' => 'Not allowed'], 403);
            }

            if ($ac['mode'] === 'department') {
                $reqDept = (int) $r->input('department_id');
                if ($reqDept !== (int)$ac['department_id']) {
                    $this->activityLog($r, 'create', 'student_subjects', self::TABLE, null, ['department_id'], null, ['department_id' => $reqDept], 'cross_department_blocked');
                    return response()->json(['success' => false, 'error' => 'Not allowed'], 403);
                }
            }

            $departmentId = (int) $r->input('department_id');
            $courseId     = (int) $r->input('course_id');
            $semesterId   = $r->filled('semester_id') ? (int)$r->input('semester_id') : null;

            $scopeKey = $this->scopeLockKey($departmentId, $courseId, $semesterId);
            [$ok, $lockKeys] = $this->acquireScopeLocks([$scopeKey], 10);

            if (!$ok) {
                $this->activityLog($r, 'create', 'student_subjects', self::TABLE, null, ['department_id','course_id','semester_id'], null, [
                    'department_id' => $departmentId,
                    'course_id'     => $courseId,
                    'semester_id'   => $semesterId,
                ], 'scope_lock_timeout');

                return response()->json([
                    'success' => false,
                    'message' => 'Another request is already processing this department, course and semester. Please try again.',
                ], 409);
            }

            $now = $this->now();
            $replaceSubjectJson = filter_var($r->input('replace_subject_json', false), FILTER_VALIDATE_BOOLEAN);

            DB::beginTransaction();

            $existing = $this->findActiveScopeRowForUpdate($departmentId, $courseId, $semesterId);

            $id = null;
            $responseMessage = 'Created';
            $statusCode = 201;

            if ($existing) {
                $oldSnapAll = $this->snapshotRow($existing);

                $subjectJsonString = $replaceSubjectJson
                    ? $this->normalizeJsonToString($r->input('subject_json'))
                    : $this->mergeSubjectJson($existing->subject_json, $r->input('subject_json'));

                if (!$subjectJsonString) {
                    DB::rollBack();

                    $this->activityLog($r, 'update', 'student_subjects', self::TABLE, (int)$existing->id, ['subject_json'], $oldSnapAll, null, 'invalid_subject_json');
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid subject_json format',
                    ], 422);
                }

                $upd = [
                    'subject_json'   => $subjectJsonString,
                    'status'         => $r->filled('status') ? (string)$r->input('status') : (string)($existing->status ?? 'active'),
                    'metadata'       => $r->has('metadata')
                        ? $this->normalizeJsonToString($r->input('metadata', null))
                        : $existing->metadata,
                    'updated_at'     => $now,
                    'updated_at_ip'  => $actor['ip'] ?: null,
                ];

                DB::table(self::TABLE)
                    ->where('id', (int)$existing->id)
                    ->update($upd);

                $id = (int)$existing->id;

                $fresh = DB::table(self::TABLE)
                    ->where('id', $id)
                    ->lockForUpdate()
                    ->first();

                $newSnapAll = $this->snapshotRow($fresh);

                [$changedFields, $oldDiff, $newDiff] = $this->diffSnapshots(
                    $oldSnapAll,
                    $newSnapAll,
                    ['subject_json','status','metadata','updated_at','updated_at_ip']
                );

                DB::commit();

                $this->activityLog(
                    $r,
                    'update',
                    'student_subjects',
                    self::TABLE,
                    $id,
                    $changedFields ?: ['subject_json','status','metadata','updated_at','updated_at_ip'],
                    $oldDiff ?: $oldSnapAll,
                    $newDiff ?: $newSnapAll,
                    $replaceSubjectJson ? 'existing_scope_replaced_from_store' : 'existing_scope_merged_from_store'
                );

                $responseMessage = $replaceSubjectJson
                    ? 'Updated existing record'
                    : 'Merged into existing record';

                $statusCode = 200;
            } else {
                $subjectJsonString = $this->normalizeJsonToString($r->input('subject_json'));
                if (!$subjectJsonString) {
                    DB::rollBack();

                    $this->activityLog($r, 'create', 'student_subjects', self::TABLE, null, ['subject_json'], null, null, 'invalid_subject_json');
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid subject_json format',
                    ], 422);
                }

                $uuid = (string) Str::uuid();

                $insertPayload = [
                    'uuid'          => $uuid,
                    'department_id' => $departmentId,
                    'course_id'     => $courseId,
                    'semester_id'   => $semesterId,
                    'subject_json'  => $subjectJsonString,
                    'status'        => $r->filled('status') ? (string)$r->input('status') : 'active',
                    'created_by'    => (int) ($actor['id'] ?: null),
                    'created_at_ip' => $actor['ip'] ?: null,
                    'updated_at_ip' => $actor['ip'] ?: null,
                    'metadata'      => $this->normalizeJsonToString($r->input('metadata', null)),
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ];

                $id = DB::table(self::TABLE)->insertGetId($insertPayload);

                DB::commit();

                $newSnap = $this->snapshotRow([
                    'id'            => (int)$id,
                    'uuid'          => $uuid,
                    'department_id' => (int)$insertPayload['department_id'],
                    'course_id'     => (int)$insertPayload['course_id'],
                    'semester_id'   => $insertPayload['semester_id'],
                    'subject_json'  => $insertPayload['subject_json'],
                    'status'        => $insertPayload['status'],
                    'metadata'      => $insertPayload['metadata'],
                    'created_by'    => $insertPayload['created_by'],
                    'created_at'    => $insertPayload['created_at'],
                    'updated_at'    => $insertPayload['updated_at'],
                    'deleted_at'    => null,
                    'created_at_ip' => $insertPayload['created_at_ip'],
                    'updated_at_ip' => $insertPayload['updated_at_ip'],
                ]);

                $this->activityLog(
                    $r,
                    'create',
                    'student_subjects',
                    self::TABLE,
                    (int)$id,
                    array_keys($insertPayload),
                    null,
                    $newSnap ?: $insertPayload,
                    'created'
                );
            }

            $rowQ = $this->baseQuery(false)->where('ss.id', (int)$id);

            if ($ac['mode'] === 'department') {
                $rowQ->where('ss.department_id', (int) $ac['department_id']);
            }

            $row = $rowQ->first();

            return response()->json([
                'success' => true,
                'message' => $responseMessage,
                'data'    => $row ? $this->presentRow($row) : null,
            ], $statusCode);
        } catch (\Throwable $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            $this->logErr('STORE: failed', $meta + ['error' => $e->getMessage()]);
            $this->activityLog($r, 'create', 'student_subjects', self::TABLE, null, null, null, null, 'exception: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create record',
                'error'   => $e->getMessage(),
            ], 500);
        } finally {
            $this->releaseScopeLocks($lockKeys);
        }
    }

    /* =========================================================
     | UPDATE
     |========================================================= */
    public function update(Request $r, string $idOrUuid)
    {
        $actor = $this->actor($r);
        $meta  = $this->reqMeta($r, $actor);

        $this->logInfo('UPDATE: request received', $meta + ['id_or_uuid' => $idOrUuid]);

        $lockKeys = [];

        try {
            if ((int)$actor['id'] <= 0) {
                $this->activityLog($r, 'update', 'student_subjects', self::TABLE, null, null, null, null, 'unauthenticated');
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            $actorId = (int) ($r->attributes->get('auth_tokenable_id') ?? $actor['id'] ?? 0);
            $ac = $this->accessControl($actorId);

            if ($ac['mode'] === 'not_allowed' || $ac['mode'] === 'none') {
                $this->activityLog($r, 'update', 'student_subjects', self::TABLE, null, null, null, null, 'not_allowed');
                return response()->json(['success' => false, 'error' => 'Not allowed'], 403);
            }

            $v = Validator::make($r->all(), [
                'department_id' => ['sometimes','required','integer','exists:' . self::TABLE_DEPTS . ',id'],
                'course_id'     => ['sometimes','required','integer','exists:' . self::TABLE_COURSES . ',id'],
                'semester_id'   => ['sometimes','nullable','integer','exists:' . self::TABLE_SEMESTERS . ',id'],

                'subject_json'                      => ['sometimes','required'],
                'subject_json.*.student_id'         => ['required_with:subject_json','integer','min:1'],
                'subject_json.*.subject_id'         => ['required_with:subject_json','integer','min:1'],
                'subject_json.*.current_attendance' => ['required_with:subject_json','numeric','min:0','max:100'],

                'replace_subject_json' => ['nullable','boolean'],
                'status'               => ['sometimes','nullable','string','max:20', Rule::in(['active','inactive'])],
                'metadata'             => ['sometimes','nullable'],
            ]);

            if ($v->fails()) {
                $this->logWarn('UPDATE: validation failed', $meta + ['errors' => $v->errors()->toArray()]);

                $this->activityLog(
                    $r,
                    'update',
                    'student_subjects',
                    self::TABLE,
                    null,
                    array_keys($v->errors()->toArray()),
                    null,
                    ['errors' => $v->errors()->toArray()],
                    'validation_failed'
                );

                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors'  => $v->errors(),
                ], 422);
            }

            $w = $this->normalizeIdentifier($idOrUuid, null);

            $baseExistingQ = DB::table(self::TABLE)
                ->where($w['raw_col'], $w['val'])
                ->whereNull(self::COL_DELETED_AT);

            if ($ac['mode'] === 'department') {
                $baseExistingQ->where('department_id', (int) $ac['department_id']);
            }

            $existing = $baseExistingQ->first();

            if (!$existing) {
                $this->activityLog($r, 'update', 'student_subjects', self::TABLE, null, null, null, null, 'not_found');
                return response()->json(['success' => false, 'message' => 'Not found'], 404);
            }

            if ($ac['mode'] === 'department' && $r->has('department_id')) {
                $reqDept = $r->filled('department_id') ? (int) $r->input('department_id') : 0;
                if ($reqDept !== (int)$ac['department_id']) {
                    $this->activityLog($r, 'update', 'student_subjects', self::TABLE, (int)$existing->id, ['department_id'], null, ['department_id' => $reqDept], 'cross_department_blocked');
                    return response()->json(['success' => false, 'error' => 'Not allowed'], 403);
                }
            }

            $oldDepartmentId = (int)$existing->department_id;
            $oldCourseId     = (int)$existing->course_id;
            $oldSemesterId   = $existing->semester_id !== null ? (int)$existing->semester_id : null;

            $newDepartmentId = $r->has('department_id') ? (int)$r->input('department_id') : $oldDepartmentId;
            $newCourseId     = $r->has('course_id') ? (int)$r->input('course_id') : $oldCourseId;
            $newSemesterId   = $r->has('semester_id')
                ? ($r->filled('semester_id') ? (int)$r->input('semester_id') : null)
                : $oldSemesterId;

            $oldScopeKey = $this->scopeLockKey($oldDepartmentId, $oldCourseId, $oldSemesterId);
            $newScopeKey = $this->scopeLockKey($newDepartmentId, $newCourseId, $newSemesterId);

            [$ok, $lockKeys] = $this->acquireScopeLocks([$oldScopeKey, $newScopeKey], 10);
            if (!$ok) {
                $this->activityLog($r, 'update', 'student_subjects', self::TABLE, (int)$existing->id, ['department_id','course_id','semester_id'], $this->snapshotRow($existing), [
                    'department_id' => $newDepartmentId,
                    'course_id'     => $newCourseId,
                    'semester_id'   => $newSemesterId,
                ], 'scope_lock_timeout');

                return response()->json([
                    'success' => false,
                    'message' => 'Another request is already processing this department, course and semester. Please try again.',
                ], 409);
            }

            DB::beginTransaction();

            $lockedExistingQ = DB::table(self::TABLE)
                ->where('id', (int)$existing->id)
                ->whereNull(self::COL_DELETED_AT)
                ->lockForUpdate();

            if ($ac['mode'] === 'department') {
                $lockedExistingQ->where('department_id', (int)$ac['department_id']);
            }

            $lockedExisting = $lockedExistingQ->first();

            if (!$lockedExisting) {
                DB::rollBack();

                $this->activityLog($r, 'update', 'student_subjects', self::TABLE, (int)$existing->id, null, null, null, 'not_found_after_lock');
                return response()->json(['success' => false, 'message' => 'Not found'], 404);
            }

            $oldSnapAll = $this->snapshotRow($lockedExisting);

            $scopeChanged =
                $newDepartmentId !== $oldDepartmentId ||
                $newCourseId !== $oldCourseId ||
                $newSemesterId !== $oldSemesterId;

            if ($scopeChanged) {
                $conflict = $this->findActiveScopeRowForUpdate($newDepartmentId, $newCourseId, $newSemesterId, (int)$lockedExisting->id);
                if ($conflict) {
                    DB::rollBack();

                    $this->activityLog($r, 'update', 'student_subjects', self::TABLE, (int)$lockedExisting->id, ['department_id','course_id','semester_id'], $oldSnapAll, [
                        'department_id' => $newDepartmentId,
                        'course_id'     => $newCourseId,
                        'semester_id'   => $newSemesterId,
                    ], 'duplicate_scope_blocked');

                    return response()->json([
                        'success' => false,
                        'message' => 'Another record already exists for this department, course and semester.',
                    ], 409);
                }
            }

            $now = $this->now();
            $replaceSubjectJson = filter_var($r->input('replace_subject_json', false), FILTER_VALIDATE_BOOLEAN);

            $upd = [
                'updated_at'    => $now,
                'updated_at_ip' => $actor['ip'] ?: null,
            ];

            if ($r->has('department_id')) {
                $upd['department_id'] = $newDepartmentId;
            }

            if ($r->has('course_id')) {
                $upd['course_id'] = $newCourseId;
            }

            if ($r->has('semester_id')) {
                $upd['semester_id'] = $newSemesterId;
            }

            if ($r->has('subject_json')) {
                $subjectJsonString = $replaceSubjectJson
                    ? $this->normalizeJsonToString($r->input('subject_json'))
                    : $this->mergeSubjectJson($lockedExisting->subject_json, $r->input('subject_json'));

                if (!$subjectJsonString) {
                    DB::rollBack();

                    $this->activityLog($r, 'update', 'student_subjects', self::TABLE, (int)$lockedExisting->id, ['subject_json'], $oldSnapAll, null, 'invalid_subject_json');
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid subject_json format',
                    ], 422);
                }

                $upd['subject_json'] = $subjectJsonString;
            }

            if ($r->has('status')) {
                $upd['status'] = $r->filled('status')
                    ? (string)$r->input('status')
                    : (string)($lockedExisting->status ?? 'active');
            }

            if ($r->has('metadata')) {
                $upd['metadata'] = $this->normalizeJsonToString($r->input('metadata', null));
            }

            DB::table(self::TABLE)
                ->where('id', (int)$lockedExisting->id)
                ->update($upd);

            $fresh = DB::table(self::TABLE)
                ->where('id', (int)$lockedExisting->id)
                ->lockForUpdate()
                ->first();

            $newSnapAll = $this->snapshotRow($fresh);

            [$changedFields, $oldDiff, $newDiff] = $this->diffSnapshots(
                $oldSnapAll,
                $newSnapAll,
                ['department_id','course_id','semester_id','subject_json','status','metadata','deleted_at','updated_at','updated_at_ip']
            );

            DB::commit();

            $this->activityLog(
                $r,
                'update',
                'student_subjects',
                self::TABLE,
                (int)$lockedExisting->id,
                $changedFields,
                $oldDiff,
                $newDiff,
                $replaceSubjectJson ? 'updated_replaced' : 'updated_merged'
            );

            $rowQ = $this->baseQuery(false)->where('ss.id', (int)$lockedExisting->id);

            if ($ac['mode'] === 'department') {
                $rowQ->where('ss.department_id', (int) $ac['department_id']);
            }

            $row = $rowQ->first();

            return response()->json([
                'success' => true,
                'message' => 'Updated',
                'data'    => $row ? $this->presentRow($row) : null,
            ]);
        } catch (\Throwable $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            $this->logErr('UPDATE: failed', $meta + ['error' => $e->getMessage()]);
            $this->activityLog($r, 'update', 'student_subjects', self::TABLE, null, null, null, null, 'exception: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update record',
                'error'   => $e->getMessage(),
            ], 500);
        } finally {
            $this->releaseScopeLocks($lockKeys);
        }
    }

    /* =========================================================
     | SOFT DELETE
     |========================================================= */
    public function destroy(Request $r, string $idOrUuid)
    {
        $actor = $this->actor($r);
        $meta  = $this->reqMeta($r, $actor);

        $this->logInfo('DESTROY: request received', $meta + ['id_or_uuid' => $idOrUuid]);

        try {
            if ((int)$actor['id'] <= 0) {
                $this->activityLog($r, 'delete', 'student_subjects', self::TABLE, null, null, null, null, 'unauthenticated');
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            $actorId = (int) ($r->attributes->get('auth_tokenable_id') ?? $actor['id'] ?? 0);
            $ac = $this->accessControl($actorId);

            if ($ac['mode'] === 'not_allowed' || $ac['mode'] === 'none') {
                $this->activityLog($r, 'delete', 'student_subjects', self::TABLE, null, null, null, null, 'not_allowed');
                return response()->json(['success' => false, 'error' => 'Not allowed'], 403);
            }

            $w = $this->normalizeIdentifier($idOrUuid, null);

            $existingQ = DB::table(self::TABLE)
                ->where($w['raw_col'], $w['val'])
                ->whereNull(self::COL_DELETED_AT);

            if ($ac['mode'] === 'department') {
                $existingQ->where('department_id', (int) $ac['department_id']);
            }

            $existing = $existingQ->first();

            if (!$existing) {
                $this->activityLog($r, 'delete', 'student_subjects', self::TABLE, null, null, null, null, 'not_found');
                return response()->json(['success' => false, 'message' => 'Not found'], 404);
            }

            $oldSnapAll = $this->snapshotRow($existing);
            $now = $this->now();

            DB::table(self::TABLE)
                ->where('id', (int)$existing->id)
                ->update([
                    'deleted_at'    => $now,
                    'updated_at'    => $now,
                    'updated_at_ip' => $actor['ip'] ?: null,
                ]);

            $fresh = DB::table(self::TABLE)->where('id', (int)$existing->id)->first();
            $newSnapAll = $this->snapshotRow($fresh);

            [$changedFields, $oldDiff, $newDiff] = $this->diffSnapshots(
                $oldSnapAll,
                $newSnapAll,
                ['deleted_at','updated_at','updated_at_ip']
            );

            $this->activityLog(
                $r,
                'delete',
                'student_subjects',
                self::TABLE,
                (int)$existing->id,
                $changedFields ?: ['deleted_at'],
                $oldDiff ?: ['deleted_at' => $oldSnapAll['deleted_at'] ?? null],
                $newDiff ?: ['deleted_at' => $newSnapAll['deleted_at'] ?? null],
                'moved_to_trash'
            );

            return response()->json([
                'success' => true,
                'message' => 'Moved to trash',
            ]);
        } catch (\Throwable $e) {
            $this->logErr('DESTROY: failed', $meta + ['error' => $e->getMessage()]);
            $this->activityLog($r, 'delete', 'student_subjects', self::TABLE, null, null, null, null, 'exception: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete record',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /* =========================================================
     | RESTORE
     |========================================================= */
    public function restore(Request $r, string $idOrUuid)
    {
        $actor = $this->actor($r);
        $meta  = $this->reqMeta($r, $actor);

        $this->logInfo('RESTORE: request received', $meta + ['id_or_uuid' => $idOrUuid]);

        $lockKeys = [];

        try {
            if ((int)$actor['id'] <= 0) {
                $this->activityLog($r, 'restore', 'student_subjects', self::TABLE, null, null, null, null, 'unauthenticated');
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            $actorId = (int) ($r->attributes->get('auth_tokenable_id') ?? $actor['id'] ?? 0);
            $ac = $this->accessControl($actorId);

            if ($ac['mode'] === 'not_allowed' || $ac['mode'] === 'none') {
                $this->activityLog($r, 'restore', 'student_subjects', self::TABLE, null, null, null, null, 'not_allowed');
                return response()->json(['success' => false, 'error' => 'Not allowed'], 403);
            }

            $w = $this->normalizeIdentifier($idOrUuid, null);

            $existingQ = DB::table(self::TABLE)
                ->where($w['raw_col'], $w['val'])
                ->whereNotNull(self::COL_DELETED_AT);

            if ($ac['mode'] === 'department') {
                $existingQ->where('department_id', (int) $ac['department_id']);
            }

            $existing = $existingQ->first();

            if (!$existing) {
                $this->activityLog($r, 'restore', 'student_subjects', self::TABLE, null, null, null, null, 'not_found_in_trash');
                return response()->json(['success' => false, 'message' => 'Not found in trash'], 404);
            }

            $departmentId = (int)$existing->department_id;
            $courseId     = (int)$existing->course_id;
            $semesterId   = $existing->semester_id !== null ? (int)$existing->semester_id : null;

            $scopeKey = $this->scopeLockKey($departmentId, $courseId, $semesterId);
            [$ok, $lockKeys] = $this->acquireScopeLocks([$scopeKey], 10);

            if (!$ok) {
                $this->activityLog($r, 'restore', 'student_subjects', self::TABLE, (int)$existing->id, ['department_id','course_id','semester_id'], null, [
                    'department_id' => $departmentId,
                    'course_id'     => $courseId,
                    'semester_id'   => $semesterId,
                ], 'scope_lock_timeout');

                return response()->json([
                    'success' => false,
                    'message' => 'Another request is already processing this department, course and semester. Please try again.',
                ], 409);
            }

            DB::beginTransaction();

            $lockedTrashed = DB::table(self::TABLE)
                ->where('id', (int)$existing->id)
                ->whereNotNull(self::COL_DELETED_AT)
                ->lockForUpdate()
                ->first();

            if (!$lockedTrashed) {
                DB::rollBack();

                $this->activityLog($r, 'restore', 'student_subjects', self::TABLE, (int)$existing->id, null, null, null, 'not_found_after_lock');
                return response()->json(['success' => false, 'message' => 'Not found in trash'], 404);
            }

            $conflict = $this->findActiveScopeRowForUpdate($departmentId, $courseId, $semesterId, (int)$lockedTrashed->id);
            if ($conflict) {
                DB::rollBack();

                $this->activityLog($r, 'restore', 'student_subjects', self::TABLE, (int)$lockedTrashed->id, ['department_id','course_id','semester_id'], $this->snapshotRow($lockedTrashed), [
                    'conflict_id'    => (int)$conflict->id,
                    'department_id'  => $departmentId,
                    'course_id'      => $courseId,
                    'semester_id'    => $semesterId,
                ], 'restore_conflict_active_scope_exists');

                return response()->json([
                    'success' => false,
                    'message' => 'Cannot restore because an active record already exists for this department, course and semester.',
                ], 409);
            }

            $oldSnapAll = $this->snapshotRow($lockedTrashed);
            $now = $this->now();

            DB::table(self::TABLE)
                ->where('id', (int)$lockedTrashed->id)
                ->update([
                    'deleted_at'    => null,
                    'updated_at'    => $now,
                    'updated_at_ip' => $actor['ip'] ?: null,
                ]);

            $fresh = DB::table(self::TABLE)->where('id', (int)$lockedTrashed->id)->lockForUpdate()->first();
            $newSnapAll = $this->snapshotRow($fresh);

            [$changedFields, $oldDiff, $newDiff] = $this->diffSnapshots(
                $oldSnapAll,
                $newSnapAll,
                ['deleted_at','updated_at','updated_at_ip']
            );

            DB::commit();

            $this->activityLog(
                $r,
                'restore',
                'student_subjects',
                self::TABLE,
                (int)$lockedTrashed->id,
                $changedFields ?: ['deleted_at'],
                $oldDiff ?: ['deleted_at' => $oldSnapAll['deleted_at'] ?? null],
                $newDiff ?: ['deleted_at' => $newSnapAll['deleted_at'] ?? null],
                'restored'
            );

            return response()->json([
                'success' => true,
                'message' => 'Restored',
            ]);
        } catch (\Throwable $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            $this->logErr('RESTORE: failed', $meta + ['error' => $e->getMessage()]);
            $this->activityLog($r, 'restore', 'student_subjects', self::TABLE, null, null, null, null, 'exception: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to restore record',
                'error'   => $e->getMessage(),
            ], 500);
        } finally {
            $this->releaseScopeLocks($lockKeys);
        }
    }

    /* =========================================================
     | FORCE DELETE
     |========================================================= */
    public function forceDelete(Request $r, string $idOrUuid)
    {
        $actor = $this->actor($r);
        $meta  = $this->reqMeta($r, $actor);

        $this->logInfo('FORCE DELETE: request received', $meta + ['id_or_uuid' => $idOrUuid]);

        try {
            if ((int)$actor['id'] <= 0) {
                $this->activityLog($r, 'force_delete', 'student_subjects', self::TABLE, null, null, null, null, 'unauthenticated');
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            $actorId = (int) ($r->attributes->get('auth_tokenable_id') ?? $actor['id'] ?? 0);
            $ac = $this->accessControl($actorId);

            if ($ac['mode'] === 'not_allowed' || $ac['mode'] === 'none') {
                $this->activityLog($r, 'force_delete', 'student_subjects', self::TABLE, null, null, null, null, 'not_allowed');
                return response()->json(['success' => false, 'error' => 'Not allowed'], 403);
            }

            $w = $this->normalizeIdentifier($idOrUuid, null);

            $existingQ = DB::table(self::TABLE)->where($w['raw_col'], $w['val']);

            if ($ac['mode'] === 'department') {
                $existingQ->where('department_id', (int) $ac['department_id']);
            }

            $existing = $existingQ->first();

            if (!$existing) {
                $this->activityLog($r, 'force_delete', 'student_subjects', self::TABLE, null, null, null, null, 'not_found');
                return response()->json(['success' => false, 'message' => 'Not found'], 404);
            }

            $oldSnapAll = $this->snapshotRow($existing);

            DB::table(self::TABLE)->where('id', (int)$existing->id)->delete();

            $this->activityLog(
                $r,
                'force_delete',
                'student_subjects',
                self::TABLE,
                (int)$existing->id,
                ['force_deleted'],
                $oldSnapAll,
                null,
                'permanently_deleted'
            );

            return response()->json([
                'success' => true,
                'message' => 'Permanently deleted',
            ]);
        } catch (\Throwable $e) {
            $this->logErr('FORCE DELETE: failed', $meta + ['error' => $e->getMessage()]);
            $this->activityLog($r, 'force_delete', 'student_subjects', self::TABLE, null, null, null, null, 'exception: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to force delete record',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}