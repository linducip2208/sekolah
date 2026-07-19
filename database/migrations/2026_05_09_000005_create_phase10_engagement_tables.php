<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Module 29 — Donations
        Schema::create('donation_campaigns', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->string('title');
            $t->string('slug', 200);
            $t->text('description');
            $t->unsignedBigInteger('target_amount');
            $t->unsignedBigInteger('raised_amount')->default(0);
            $t->date('start_date');
            $t->date('end_date');
            $t->string('cover_image_path')->nullable();
            $t->json('updates')->nullable();
            $t->string('category', 30)->default('general');
            $t->enum('status', ['draft', 'active', 'completed', 'cancelled'])->default('draft');
            $t->boolean('is_public')->default(true);
            $t->timestamps(); $t->softDeletes();
            $t->unique(['school_id', 'slug']);
        });

        Schema::create('donations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('donation_campaign_id')->nullable()->constrained();
            $t->foreignId('user_id')->nullable()->constrained();
            $t->string('donor_name')->nullable();
            $t->string('donor_email')->nullable();
            $t->string('donor_phone')->nullable();
            $t->string('npwp', 30)->nullable();
            $t->boolean('is_anonymous')->default(false);
            $t->boolean('show_amount')->default(true);
            $t->unsignedInteger('amount');
            $t->text('message')->nullable();
            $t->foreignId('payment_transaction_id')->nullable()->constrained();
            $t->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            $t->string('receipt_no', 30)->nullable()->unique();
            $t->timestamp('donated_at')->nullable();
            $t->timestamps();
        });

        // Module 30 — Alumni
        Schema::create('alumni_profiles', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->unique()->constrained();
            $t->unsignedSmallInteger('graduation_year');
            $t->string('class_of')->nullable();
            $t->string('current_position')->nullable();
            $t->string('current_company')->nullable();
            $t->string('city')->nullable();
            $t->string('country', 5)->default('ID');
            $t->string('linkedin_url')->nullable();
            $t->string('industry')->nullable();
            $t->json('skills')->nullable();
            $t->boolean('willing_to_mentor')->default(false);
            $t->boolean('willing_to_offer_internship')->default(false);
            $t->boolean('verified')->default(false);
            $t->timestamps();
        });

        Schema::create('alumni_job_posts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('posted_by')->constrained('users');
            $t->string('title');
            $t->string('company');
            $t->string('location');
            $t->string('type', 30);
            $t->text('description');
            $t->string('apply_url')->nullable();
            $t->date('expires_at');
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('alumni_mentorships', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('mentor_id')->constrained('users');
            $t->foreignId('mentee_id')->constrained('users');
            $t->enum('status', ['requested', 'active', 'completed', 'cancelled'])->default('requested');
            $t->text('goals')->nullable();
            $t->date('start_date');
            $t->date('end_date')->nullable();
            $t->timestamps();
        });

        Schema::create('alumni_events', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->string('title');
            $t->text('description');
            $t->dateTime('starts_at');
            $t->dateTime('ends_at');
            $t->string('venue');
            $t->string('city');
            $t->unsignedInteger('capacity')->nullable();
            $t->unsignedInteger('ticket_price')->default(0);
            $t->boolean('is_published')->default(false);
            $t->timestamps();
        });

        // Module 37 — Achievement
        Schema::create('achievement_categories', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->enum('scope', ['internal','district','province','national','international']);
            $t->unsignedSmallInteger('points')->default(10);
            $t->timestamps();
        });

        Schema::create('student_achievements', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('student_id')->constrained();
            $t->foreignId('achievement_category_id')->constrained();
            $t->string('title');
            $t->date('achieved_at');
            $t->string('issuer')->nullable();
            $t->string('certificate_path')->nullable();
            $t->text('description')->nullable();
            $t->boolean('verified')->default(false);
            $t->foreignId('verified_by')->nullable()->constrained('users');
            $t->timestamps();
        });

        Schema::create('certificate_templates', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('layout_path');
            $t->json('placeholders');
            $t->boolean('is_default')->default(false);
            $t->timestamps();
        });

        Schema::create('digital_badges', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('icon_path');
            $t->text('description');
            $t->json('award_criteria');
            $t->timestamps();
        });

        Schema::create('student_badges', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('student_id')->constrained();
            $t->foreignId('digital_badge_id')->constrained();
            $t->date('awarded_at');
            $t->timestamps();
            $t->unique(['student_id', 'digital_badge_id']);
        });

        // Module 38 — Scholarship
        Schema::create('scholarship_programs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('source', 30);
            $t->enum('discount_type', ['percentage','fixed','full']);
            $t->unsignedInteger('discount_value');
            $t->json('eligibility_criteria');
            $t->date('open_date');
            $t->date('close_date');
            $t->unsignedInteger('quota')->nullable();
            $t->json('required_documents')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('scholarship_applications', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('scholarship_program_id')->constrained();
            $t->foreignId('student_id')->constrained();
            $t->json('documents')->nullable();
            $t->text('motivation')->nullable();
            $t->enum('status', ['draft','submitted','review','interview','granted','rejected','withdrawn'])->default('draft');
            $t->foreignId('reviewer_id')->nullable()->constrained('users');
            $t->text('reviewer_note')->nullable();
            $t->date('granted_from')->nullable();
            $t->date('granted_until')->nullable();
            $t->timestamps();
        });

        Schema::create('scholarship_grants', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('scholarship_application_id')->constrained();
            $t->foreignId('student_id')->constrained();
            $t->foreignId('fee_invoice_id')->nullable()->constrained();
            $t->unsignedInteger('discount_applied');
            $t->date('applied_at');
            $t->timestamps();
        });

        // Module 39 — Career Guidance
        Schema::create('career_assessments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('student_id')->constrained();
            $t->string('test_type', 30);
            $t->json('responses');
            $t->json('result');
            $t->date('taken_at');
            $t->timestamps();
        });

        Schema::create('college_database', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('country', 5);
            $t->string('type', 20);
            $t->string('city')->nullable();
            $t->json('majors_offered')->nullable();
            $t->decimal('passing_grade_avg', 5, 2)->nullable();
            $t->string('website')->nullable();
            $t->timestamps();
        });

        Schema::create('internship_placements', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('student_id')->constrained();
            $t->string('company_name');
            $t->string('position');
            $t->string('mentor_name')->nullable();
            $t->string('mentor_phone')->nullable();
            $t->date('start_date');
            $t->date('end_date');
            $t->enum('status', ['planned','active','completed','dropped']);
            $t->json('daily_logs')->nullable();
            $t->json('evaluation')->nullable();
            $t->string('certificate_path')->nullable();
            $t->timestamps();
        });

        Schema::create('industry_certifications', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('student_id')->constrained();
            $t->string('cert_name');
            $t->string('issuer');
            $t->date('issued_at');
            $t->date('expires_at')->nullable();
            $t->string('cert_number')->nullable();
            $t->string('file_path')->nullable();
            $t->timestamps();
        });

        // Module 42 — Events
        Schema::create('school_events', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->string('title');
            $t->string('slug', 200);
            $t->text('description');
            $t->string('event_type', 30);
            $t->dateTime('starts_at');
            $t->dateTime('ends_at');
            $t->string('venue');
            $t->string('city')->nullable();
            $t->decimal('venue_lat', 10, 7)->nullable();
            $t->decimal('venue_lng', 10, 7)->nullable();
            $t->unsignedInteger('capacity')->nullable();
            $t->unsignedInteger('ticket_price')->default(0);
            $t->json('target_audience')->nullable();
            $t->string('cover_image_path')->nullable();
            $t->boolean('require_rsvp')->default(true);
            $t->boolean('is_published')->default(false);
            $t->timestamps(); $t->softDeletes();
            $t->unique(['school_id', 'slug']);
        });

        Schema::create('event_rsvps', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('school_event_id')->constrained();
            $t->foreignId('user_id')->constrained();
            $t->unsignedSmallInteger('guests_count')->default(0);
            $t->enum('status', ['going','maybe','not_going','cancelled'])->default('going');
            $t->foreignId('payment_transaction_id')->nullable()->constrained();
            $t->string('ticket_qr_token', 100)->nullable();
            $t->timestamp('checked_in_at')->nullable();
            $t->timestamps();
            $t->unique(['school_event_id', 'user_id']);
        });

        // Module 43 — Daily Report
        Schema::create('daily_reports', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('student_id')->constrained();
            $t->date('report_date');
            $t->json('attendance')->nullable();
            $t->json('subjects_today')->nullable();
            $t->json('homework_due')->nullable();
            $t->json('canteen_summary')->nullable();
            $t->json('clinic_visit')->nullable();
            $t->json('discipline_events')->nullable();
            $t->json('wellness_checkin')->nullable();
            $t->json('teacher_notes')->nullable();
            $t->timestamp('sent_at')->nullable();
            $t->timestamps();
            $t->unique(['student_id', 'report_date']);
        });

        Schema::create('daily_report_preferences', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->unique()->constrained();
            $t->boolean('enabled')->default(true);
            $t->time('preferred_send_time')->default('17:00:00');
            $t->json('channels')->nullable();
            $t->json('sections_enabled')->nullable();
            $t->timestamps();
        });

        // Module 44 — Extracurricular
        Schema::create('extracurriculars', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('icon')->nullable();
            $t->text('description')->nullable();
            $t->foreignId('coach_id')->nullable()->constrained('users');
            $t->json('schedule')->nullable();
            $t->unsignedInteger('capacity')->nullable();
            $t->unsignedInteger('fee_per_month')->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('student_extracurriculars', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('extracurricular_id')->constrained();
            $t->foreignId('student_id')->constrained();
            $t->date('joined_at');
            $t->date('left_at')->nullable();
            $t->string('level', 30)->nullable();
            $t->json('achievements')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('extracurricular_attendances', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('extracurricular_id')->constrained();
            $t->foreignId('student_id')->constrained();
            $t->date('session_date');
            $t->enum('status', ['present','absent','late','excused']);
            $t->foreignId('marked_by')->constrained('users');
            $t->timestamps();
            $t->unique(['extracurricular_id','student_id','session_date'], 'ekskul_attendance_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extracurricular_attendances');
        Schema::dropIfExists('student_extracurriculars');
        Schema::dropIfExists('extracurriculars');
        Schema::dropIfExists('daily_report_preferences');
        Schema::dropIfExists('daily_reports');
        Schema::dropIfExists('event_rsvps');
        Schema::dropIfExists('school_events');
        Schema::dropIfExists('industry_certifications');
        Schema::dropIfExists('internship_placements');
        Schema::dropIfExists('college_database');
        Schema::dropIfExists('career_assessments');
        Schema::dropIfExists('scholarship_grants');
        Schema::dropIfExists('scholarship_applications');
        Schema::dropIfExists('scholarship_programs');
        Schema::dropIfExists('student_badges');
        Schema::dropIfExists('digital_badges');
        Schema::dropIfExists('certificate_templates');
        Schema::dropIfExists('student_achievements');
        Schema::dropIfExists('achievement_categories');
        Schema::dropIfExists('alumni_events');
        Schema::dropIfExists('alumni_mentorships');
        Schema::dropIfExists('alumni_job_posts');
        Schema::dropIfExists('alumni_profiles');
        Schema::dropIfExists('donations');
        Schema::dropIfExists('donation_campaigns');
    }
};
