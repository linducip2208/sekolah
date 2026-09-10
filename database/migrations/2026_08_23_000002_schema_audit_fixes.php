<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FK & schema audit fixes (hasil audit model↔migration menyeluruh):
 *
 * 1. hostel_rooms        : +school_id (SchoolScope) + softDeletes (SchoolModel)
 * 2. bpjs_configs        : +softDeletes
 * 3. staff_tax_profiles  : +softDeletes
 * 4. pph21_brackets      : +softDeletes
 * 5. bpjs_reports        : +softDeletes
 * 6. kpi_scores          : +softDeletes
 * 7. ai_data_chat_logs   : +softDeletes
 * 8. automation_logs     : +softDeletes
 * 9. benchmark_results   : +softDeletes
 *10. report_templates    : +softDeletes
 *11. saved_reports       : +softDeletes
 *12. signed_documents    : +softDeletes
 *13. student_activity_log: +softDeletes
 *14. notification_preferences : +softDeletes
 *15. hostel_attendances  : +softDeletes
 *16. hostel_mess_menus   : +softDeletes
 *17. foundation_master_data   : +school_id (SchoolModel)
 *18. foundation_user_management : +school_id (SchoolModel)
 *19. job_applications    : +school_id
 *20. survey_answers      : +school_id
 *21. visitor_qr_sessions : +school_id +softDeletes
 *22. tenant_usages       : create table (model ada, migration belum ada)
 */
return new class extends Migration
{
    protected array $addSoftDeletes = [
        'hostel_rooms', 'bpjs_configs', 'staff_tax_profiles', 'pph21_brackets',
        'bpjs_reports', 'kpi_scores', 'ai_data_chat_logs', 'automation_logs',
        'benchmark_results', 'report_templates', 'saved_reports', 'signed_documents',
        'student_activity_log', 'notification_preferences', 'hostel_attendances',
        'hostel_mess_menus', 'visitor_qr_sessions',
    ];

    protected array $addSchoolId = [
        // table => [afterColumn, viaBackfillFrom]
        'hostel_rooms' => ['id', 'hostels.hostel_id'],
        'foundation_master_data' => null,
        'foundation_user_management' => null,
        'job_applications' => null,
        'survey_answers' => null,
        'visitor_qr_sessions' => null,
    ];

    public function up(): void
    {
        foreach ($this->addSoftDeletes as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, fn (Blueprint $t) => $t->softDeletes());
            }
        }

        foreach ($this->addSchoolId as $table => $cfg) {
            if (!Schema::hasTable($table) || Schema::hasColumn($table, 'school_id')) {
                continue;
            }
            $after = is_array($cfg) ? $cfg[0] : null;
            Schema::table($table, function (Blueprint $t) use ($after) {
                if ($after !== null && $after !== '') {
                    $t->foreignId('school_id')->nullable()->after($after)->constrained()->cascadeOnDelete();
                } else {
                    $t->foreignId('school_id')->nullable()->constrained()->cascadeOnDelete();
                }
            });
        }

        // Backfill school_id untuk hostel_rooms dari tabel hostels induk.
        if (Schema::hasTable('hostel_rooms') && Schema::hasColumn('hostel_rooms', 'school_id')) {
            DB::statement(
                'UPDATE hostel_rooms hr JOIN hostels h ON h.id = hr.hostel_id '
                . 'SET hr.school_id = h.school_id WHERE hr.school_id IS NULL'
            );
        }
        // Backfill school_id untuk survey_answers dari surveys induk.
        if (Schema::hasTable('survey_answers') && Schema::hasColumn('survey_answers', 'school_id')
            && Schema::hasTable('surveys') && Schema::hasColumn('surveys', 'school_id')) {
            DB::statement(
                'UPDATE survey_answers sa JOIN surveys s ON s.id = sa.survey_id '
                . 'SET sa.school_id = s.school_id WHERE sa.school_id IS NULL'
            );
        }
        // Backfill school_id untuk job_applications dari job_listings.
        if (Schema::hasTable('job_applications') && Schema::hasColumn('job_applications', 'school_id')
            && Schema::hasTable('job_listings') && Schema::hasColumn('job_listings', 'school_id')) {
            DB::statement(
                'UPDATE job_applications ja JOIN job_listings jl ON jl.id = ja.job_listing_id '
                . 'SET ja.school_id = jl.school_id WHERE ja.school_id IS NULL'
            );
        }

        // Tabel tenant_usages — model App\Models\Saas\TenantUsage sudah ada.
        if (!Schema::hasTable('tenant_usages')) {
            Schema::create('tenant_usages', function (Blueprint $t) {
                $t->id();
                $t->foreignId('school_id')->constrained()->cascadeOnDelete();
                $t->string('month', 7); // "2026-08"
                $t->unsignedInteger('active_students')->default(0);
                $t->unsignedInteger('active_teachers')->default(0);
                $t->unsignedBigInteger('total_logins')->default(0);
                $t->unsignedBigInteger('api_calls')->default(0);
                $t->unsignedBigInteger('storage_used_bytes')->default(0);
                $t->unsignedBigInteger('sms_sent')->default(0);
                $t->unsignedBigInteger('emails_sent')->default(0);
                $t->timestamps();
                $t->softDeletes();
                $t->unique(['school_id', 'month']);
            });
        }
    }

    public function down(): void
    {
        foreach (array_reverse($this->addSoftDeletes) as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, fn (Blueprint $t) => $t->dropSoftDeletes());
            }
        }

        foreach (array_keys($this->addSchoolId) as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'school_id')) {
                try {
                    Schema::table($table, function (Blueprint $t) {
                        try { $t->dropForeign(['school_id']); } catch (\Throwable) {}
                        $t->dropColumn('school_id');
                    });
                } catch (\Throwable) {
                    // rollback best-effort
                }
            }
        }

        Schema::dropIfExists('tenant_usages');
    }
};
