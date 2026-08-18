<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teaching_journals', function (Blueprint $t) {
            if (!Schema::hasColumn('teaching_journals', 'staff_id')) {
                $t->foreignId('staff_id')->nullable()->after('school_id')->constrained('staffs')->nullOnDelete();
            }
            if (!Schema::hasColumn('teaching_journals', 'class_room_id')) {
                $t->foreignId('class_room_id')->nullable()->after('class_section_id')->constrained('class_rooms')->nullOnDelete();
            }
            if (!Schema::hasColumn('teaching_journals', 'timetable_id')) {
                $t->foreignId('timetable_id')->nullable()->after('class_room_id')->constrained('timetable_slots')->nullOnDelete();
            }
            if (!Schema::hasColumn('teaching_journals', 'class_number')) {
                $t->unsignedTinyInteger('class_number')->nullable()->after('subject_id');
            }
            if (!Schema::hasColumn('teaching_journals', 'topic')) {
                $t->string('topic')->nullable()->after('material');
            }
            if (!Schema::hasColumn('teaching_journals', 'status')) {
                $t->enum('status', ['draft', 'published'])->default('draft')->after('notes');
            }
        });

        Schema::table('teaching_journals', function (Blueprint $t) {
            $indexes = collect(Schema::getIndexes('teaching_journals'))->pluck('name')->all();
            if (!in_array('teaching_journals_school_id_staff_id_index', $indexes)) {
                $t->index(['school_id', 'staff_id']);
            }
            if (!in_array('teaching_journals_school_id_status_index', $indexes)) {
                $t->index(['school_id', 'status']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('teaching_journals', function (Blueprint $t) {
            $t->dropForeign(['staff_id']);
            $t->dropForeign(['class_room_id']);
            $t->dropForeign(['timetable_id']);
            $t->dropColumn(['staff_id', 'class_room_id', 'timetable_id', 'class_number', 'topic', 'status']);
        });
    }
};
