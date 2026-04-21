<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('student_subject')) {
            return;
        }

        if (Schema::hasColumn('student_subject', 'section_id')) {
            return;
        }

        Schema::table('student_subject', function (Blueprint $table) {
            $table->unsignedBigInteger('section_id')->nullable()->index();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('student_subject') || !Schema::hasColumn('student_subject', 'section_id')) {
            return;
        }

        Schema::table('student_subject', function (Blueprint $table) {
            $table->dropIndex(['section_id']);
            $table->dropColumn('section_id');
        });
    }
};
