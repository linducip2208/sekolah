<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Module 22 — PPDB
        Schema::create('ppdb_periods', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('academic_year_id')->constrained();
            $t->string('name');
            $t->date('open_date'); $t->date('close_date');
            $t->date('announcement_date')->nullable();
            $t->date('reregistration_deadline')->nullable();
            $t->unsignedInteger('form_fee')->default(0);
            $t->json('jalur_config')->nullable();
            $t->json('document_requirements')->nullable();
            $t->boolean('is_published')->default(false);
            $t->timestamps(); $t->softDeletes();
        });

        Schema::create('ppdb_applications', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('ppdb_period_id')->constrained();
            $t->string('registration_no', 30)->unique();
            $t->string('jalur', 20);
            $t->string('student_name');
            $t->string('nisn', 20)->nullable();
            $t->date('date_of_birth'); $t->string('gender', 10);
            $t->text('address'); $t->string('district'); $t->string('city');
            $t->decimal('home_lat', 10, 7)->nullable();
            $t->decimal('home_lng', 10, 7)->nullable();
            $t->decimal('distance_km', 8, 3)->nullable();
            $t->string('previous_school')->nullable();
            $t->string('parent_name');
            $t->string('parent_phone');
            $t->string('parent_email');
            $t->json('documents')->nullable();
            $t->json('achievements')->nullable();
            $t->decimal('average_score', 5, 2)->nullable();
            $t->decimal('ranking_score', 8, 3)->nullable();
            $t->unsignedSmallInteger('rank_position')->nullable();
            $t->string('status', 30)->default('draft');
            $t->foreignId('reviewer_id')->nullable()->constrained('users');
            $t->text('reviewer_note')->nullable();
            $t->foreignId('form_payment_id')->nullable()->constrained('fee_payments');
            $t->timestamp('submitted_at')->nullable();
            $t->timestamp('verified_at')->nullable();
            $t->timestamp('accepted_at')->nullable();
            $t->timestamps(); $t->softDeletes();
            $t->index(['school_id', 'ppdb_period_id', 'status']);
        });

        Schema::create('ppdb_zonasi_zones', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->string('district');
            $t->string('subdistrict')->nullable();
            $t->decimal('priority_score', 5, 2)->default(100);
            $t->timestamps();
        });

        // Module 23 — Bus Tracking + ID Gate
        Schema::create('vehicle_locations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $t->decimal('lat', 10, 7);
            $t->decimal('lng', 10, 7);
            $t->decimal('speed_kmh', 5, 2)->nullable();
            $t->decimal('heading_deg', 5, 2)->nullable();
            $t->timestamp('recorded_at');
            $t->index(['vehicle_id', 'recorded_at']);
        });

        Schema::create('vehicle_trips', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('vehicle_id')->constrained();
            $t->foreignId('transport_route_id')->constrained();
            $t->enum('direction', ['pickup', 'drop']);
            $t->timestamp('started_at');
            $t->timestamp('ended_at')->nullable();
            $t->json('stops_completed')->nullable();
            $t->string('status', 20)->default('active');
            $t->timestamps();
        });

        Schema::create('id_gate_devices', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('location');
            $t->string('device_token_encrypted', 500);
            $t->enum('type', ['entry', 'exit', 'both']);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('id_gate_events', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('id_gate_device_id')->constrained();
            $t->foreignId('user_id')->constrained();
            $t->enum('direction', ['in', 'out']);
            $t->timestamp('scanned_at');
            $t->index(['school_id', 'user_id', 'scanned_at']);
        });

        Schema::create('student_id_cards', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('student_id')->unique()->constrained();
            $t->string('card_uid', 50)->unique();
            $t->string('qr_token', 100)->unique();
            $t->boolean('is_active')->default(true);
            $t->timestamp('issued_at');
            $t->timestamps();
        });

        // Module 24 — UKS / Klinik
        Schema::create('medical_records', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('student_id')->unique()->constrained();
            $t->string('blood_type', 5)->nullable();
            $t->json('allergies')->nullable();
            $t->json('chronic_conditions')->nullable();
            $t->json('current_medications')->nullable();
            $t->string('emergency_contact_name')->nullable();
            $t->string('emergency_contact_phone')->nullable();
            $t->string('insurance_provider')->nullable();
            $t->string('insurance_number')->nullable();
            $t->timestamps();
        });

        Schema::create('clinic_visits', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('student_id')->constrained();
            $t->foreignId('attended_by')->constrained('users');
            $t->timestamp('visit_at');
            $t->text('symptoms');
            $t->text('diagnosis')->nullable();
            $t->text('treatment')->nullable();
            $t->json('medications_given')->nullable();
            $t->decimal('temperature_c', 4, 1)->nullable();
            $t->string('blood_pressure', 10)->nullable();
            $t->boolean('parent_notified')->default(false);
            $t->boolean('returned_to_class')->default(true);
            $t->boolean('sent_home')->default(false);
            $t->boolean('referred_external')->default(false);
            $t->string('referred_to')->nullable();
            $t->timestamps();
            $t->index(['school_id', 'student_id', 'visit_at']);
        });

        Schema::create('vaccinations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('student_id')->constrained();
            $t->string('vaccine_name');
            $t->date('vaccinated_at');
            $t->string('batch_number')->nullable();
            $t->string('administered_by')->nullable();
            $t->date('next_dose_due')->nullable();
            $t->string('certificate_path')->nullable();
            $t->timestamps();
        });

        // Module 25 — BP/BK + Discipline
        Schema::create('counseling_sessions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('student_id')->constrained();
            $t->foreignId('counselor_id')->constrained('users');
            $t->dateTime('scheduled_at');
            $t->unsignedSmallInteger('duration_minutes')->default(45);
            $t->enum('type', ['academic', 'behavior', 'mental_health', 'career', 'family', 'social']);
            $t->enum('status', ['scheduled', 'completed', 'no_show', 'cancelled', 'rescheduled'])->default('scheduled');
            $t->text('notes')->nullable();
            $t->boolean('refer_external')->default(false);
            $t->string('referred_to')->nullable();
            $t->timestamps(); $t->softDeletes();
        });

        Schema::create('discipline_categories', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->enum('type', ['violation', 'achievement']);
            $t->integer('point_value');
            $t->text('description')->nullable();
            $t->boolean('auto_sanction')->default(false);
            $t->json('sanction_thresholds')->nullable();
            $t->timestamps();
        });

        Schema::create('discipline_records', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('student_id')->constrained();
            $t->foreignId('discipline_category_id')->constrained();
            $t->foreignId('reported_by')->constrained('users');
            $t->date('incident_date');
            $t->text('description');
            $t->json('evidence_files')->nullable();
            $t->integer('points');
            $t->enum('status', ['reported', 'reviewed', 'sanctioned', 'closed'])->default('reported');
            $t->text('sanction_applied')->nullable();
            $t->boolean('parent_notified')->default(false);
            $t->timestamps();
        });

        Schema::create('bullying_reports', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('reporter_id')->nullable()->constrained('users');
            $t->boolean('is_anonymous')->default(false);
            $t->json('victims_described')->nullable();
            $t->json('perpetrators_described')->nullable();
            $t->enum('type', ['verbal', 'physical', 'cyber', 'social', 'other']);
            $t->date('incident_date')->nullable();
            $t->string('location')->nullable();
            $t->text('description');
            $t->json('evidence_files')->nullable();
            $t->enum('status', ['received', 'investigating', 'action_taken', 'closed', 'unfounded'])->default('received');
            $t->foreignId('assigned_to')->nullable()->constrained('users');
            $t->text('investigation_notes')->nullable();
            $t->text('action_summary')->nullable();
            $t->timestamps();
        });

        Schema::create('wellness_checkins', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('student_id')->constrained();
            $t->date('checkin_date');
            $t->unsignedTinyInteger('mood_score');
            $t->json('feeling_tags')->nullable();
            $t->text('note')->nullable();
            $t->boolean('flagged_for_review')->default(false);
            $t->timestamps();
            $t->unique(['student_id', 'checkin_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wellness_checkins');
        Schema::dropIfExists('bullying_reports');
        Schema::dropIfExists('discipline_records');
        Schema::dropIfExists('discipline_categories');
        Schema::dropIfExists('counseling_sessions');
        Schema::dropIfExists('vaccinations');
        Schema::dropIfExists('clinic_visits');
        Schema::dropIfExists('medical_records');
        Schema::dropIfExists('student_id_cards');
        Schema::dropIfExists('id_gate_events');
        Schema::dropIfExists('id_gate_devices');
        Schema::dropIfExists('vehicle_trips');
        Schema::dropIfExists('vehicle_locations');
        Schema::dropIfExists('ppdb_zonasi_zones');
        Schema::dropIfExists('ppdb_applications');
        Schema::dropIfExists('ppdb_periods');
    }
};
