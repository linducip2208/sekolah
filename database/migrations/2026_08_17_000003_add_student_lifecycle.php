<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('students', 'status')) {
            Schema::table('students', function (Blueprint $table) {
                $table->enum('status', ['applicant', 'enrolled', 'active', 'transferred', 'graduated', 'alumni', 'withdrawn'])
                    ->default('active')->after('class_section_id');
                $table->date('enrolled_at')->nullable()->after('admission_date');
                $table->date('graduated_at')->nullable()->after('enrolled_at');
                $table->date('transferred_at')->nullable()->after('graduated_at');
                $table->index(['school_id', 'status']);
            });

            DB::statement("UPDATE students SET status = 'active'");
        }

        if (!Schema::hasTable('student_status_history')) {
            Schema::create('student_status_history', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->constrained()->cascadeOnDelete();
                $table->foreignId('student_id')->constrained()->cascadeOnDelete();
                $table->string('from_status', 20)->nullable();
                $table->string('to_status', 20);
                $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('note')->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->index(['school_id', 'student_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('student_status_history');

        if (Schema::hasColumn('students', 'status')) {
            Schema::table('students', function (Blueprint $table) {
                $table->dropIndex(['school_id', 'status']);
                $table->dropColumn(['status', 'enrolled_at', 'graduated_at', 'transferred_at']);
            });
        }
    }
};
