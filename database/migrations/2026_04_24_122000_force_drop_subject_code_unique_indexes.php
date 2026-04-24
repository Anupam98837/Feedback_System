<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function hasIndex(string $table, string $index): bool
    {
        $database = DB::getDatabaseName();

        return DB::table('information_schema.statistics')
            ->where('table_schema', $database)
            ->where('table_name', $table)
            ->where('index_name', $index)
            ->exists();
    }

    private function ensurePlainIndex(string $table, string $column, string $index): void
    {
        if (!Schema::hasColumn($table, $column) || $this->hasIndex($table, $index)) {
            return;
        }

        DB::statement(sprintf(
            'ALTER TABLE `%s` ADD INDEX `%s` (`%s`)',
            $table,
            $index,
            $column
        ));
    }

    private function dropIndexIfExists(string $table, string $index): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        if ($this->hasIndex($table, $index)) {
            DB::statement(sprintf('ALTER TABLE `%s` DROP INDEX `%s`', $table, $index));
        }
    }

    public function up(): void
    {
        $this->ensurePlainIndex('subjects', 'department_id', 'subjects_department_id_idx');
        $this->ensurePlainIndex('subjects', 'course_id', 'subjects_course_id_idx');

        $this->dropIndexIfExists('subjects', 'subjects_dept_code_unique');
        $this->dropIndexIfExists('subjects', 'subjects_course_code_unique');
    }

    public function down(): void
    {
        // No-op. Subject code should remain non-unique.
    }
};
