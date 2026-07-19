<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private array $tables = [
        'achievement_categories', 'ai_feature_assignments', 'ai_models', 'ai_usage_logs',
        'alumni_events', 'alumni_job_posts', 'alumni_mentorships', 'alumni_profiles',
        'asset_categories', 'asset_loans', 'assignment_submissions', 'book_issues',
        'bullying_reports', 'canteen_categories', 'canteen_menu_items', 'canteen_orders',
        'canteen_topups', 'canteen_wallets', 'career_assessments', 'certificate_templates',
        'class_section_subjects', 'clinic_visits', 'college_database', 'competency_assessments',
        'competency_lesson_map', 'curriculum_competencies', 'curriculum_frameworks',
        'daily_report_preferences', 'daily_reports', 'dapodik_config', 'dapodik_sync_logs',
        'digital_badges', 'discipline_categories', 'discipline_records', 'donations',
        'event_rsvps', 'exam_questions', 'exam_results', 'extracurricular_attendances',
        'extracurriculars', 'foundation_admins', 'foundation_school_links', 'grade_rules',
        'hafalan_progress', 'hafalan_targets', 'ibadah_logs', 'id_gate_devices',
        'id_gate_events', 'industry_certifications', 'internship_placements',
        'kitab_kuning_progress', 'learning_analytics_reports', 'lesson_plan_attachments',
        'live_class_attendances', 'live_class_sessions', 'maintenance_requests',
        'medical_records', 'messages', 'parent_student', 'payment_webhook_logs',
        'ppdb_zonasi_zones', 'question_bank_categories', 'religious_mode_config',
        'scholarship_applications', 'scholarship_grants', 'scholarship_programs',
        'student_achievements', 'student_badges', 'student_extracurriculars',
        'student_id_cards', 'student_risk_scores', 'study_materials',
        'transport_route_stops', 'vaccinations', 'vehicle_locations', 'vehicle_trips',
        'video_providers', 'visitor_blacklist', 'visitor_logs', 'wellness_checkins',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, fn (Blueprint $t) => $t->softDeletes());
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, fn (Blueprint $t) => $t->dropSoftDeletes());
            }
        }
    }
};
