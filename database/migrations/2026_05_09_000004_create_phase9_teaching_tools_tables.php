<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Module 26 — Lesson Plan / RPP
        Schema::create('lesson_plans', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('class_section_id')->constrained();
            $t->foreignId('subject_id')->constrained();
            $t->foreignId('teacher_id')->constrained('users');
            $t->foreignId('semester_id')->nullable()->constrained('semesters');
            $t->string('title');
            $t->date('lesson_date');
            $t->unsignedSmallInteger('duration_minutes');
            $t->json('learning_objectives');
            $t->text('material_summary');
            $t->json('activities');
            $t->json('assessment_methods')->nullable();
            $t->json('resources')->nullable();
            $t->string('curriculum_type', 30)->default('merdeka');
            $t->enum('status', ['draft', 'submitted', 'approved', 'rejected', 'completed'])->default('draft');
            $t->foreignId('reviewer_id')->nullable()->constrained('users');
            $t->timestamp('reviewed_at')->nullable();
            $t->text('reviewer_feedback')->nullable();
            $t->boolean('actually_executed')->default(false);
            $t->text('execution_note')->nullable();
            $t->timestamps(); $t->softDeletes();
            $t->index(['school_id', 'teacher_id', 'lesson_date']);
        });

        Schema::create('lesson_plan_attachments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('lesson_plan_id')->constrained()->cascadeOnDelete();
            $t->string('file_path');
            $t->string('file_name');
            $t->string('mime', 100);
            $t->unsignedInteger('size_bytes');
            $t->timestamps();
        });

        // Module 27 — Cafeteria
        Schema::create('canteen_wallets', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('student_id')->unique()->constrained();
            $t->unsignedInteger('balance')->default(0);
            $t->unsignedInteger('daily_limit')->default(0);
            $t->json('blocked_categories')->nullable();
            $t->boolean('is_locked')->default(false);
            $t->timestamps();
        });

        Schema::create('canteen_topups', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('canteen_wallet_id')->constrained()->cascadeOnDelete();
            $t->foreignId('initiated_by')->constrained('users');
            $t->foreignId('payment_transaction_id')->nullable()->constrained();
            $t->unsignedInteger('amount');
            $t->enum('status', ['pending','completed','failed','refunded'])->default('pending');
            $t->timestamps();
        });

        Schema::create('canteen_categories', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('icon')->nullable();
            $t->boolean('healthy_tag')->default(false);
            $t->timestamps();
        });

        Schema::create('canteen_menu_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('canteen_category_id')->constrained();
            $t->string('name');
            $t->text('description')->nullable();
            $t->unsignedInteger('price');
            $t->string('photo_path')->nullable();
            $t->json('allergens')->nullable();
            $t->boolean('is_available')->default(true);
            $t->unsignedInteger('stock_today')->nullable();
            $t->timestamps();
        });

        Schema::create('canteen_orders', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('student_id')->constrained();
            $t->foreignId('canteen_wallet_id')->constrained();
            $t->string('order_no', 30)->unique();
            $t->dateTime('pickup_at')->nullable();
            $t->json('items');
            $t->unsignedInteger('total');
            $t->enum('source', ['preorder', 'walkin']);
            $t->enum('status', ['pending','preparing','ready','picked_up','cancelled'])->default('pending');
            $t->timestamps();
        });

        // Module 28 — Pesantren / Madrasah
        Schema::create('religious_mode_config', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->unique()->constrained()->cascadeOnDelete();
            $t->boolean('enabled')->default(false);
            $t->enum('religion', ['islam', 'christian', 'catholic', 'hindu', 'buddha', 'confucian'])->default('islam');
            $t->string('institution_type', 50)->nullable();
            $t->json('hijri_holidays')->nullable();
            $t->boolean('use_hijri_calendar')->default(false);
            $t->json('prayer_times_config')->nullable();
            $t->timestamps();
        });

        Schema::create('hafalan_targets', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('class_section_id')->nullable()->constrained();
            $t->string('name');
            $t->json('target_ranges');
            $t->date('start_date');
            $t->date('deadline');
            $t->timestamps();
        });

        Schema::create('hafalan_progress', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('student_id')->constrained();
            $t->foreignId('hafalan_target_id')->nullable()->constrained();
            $t->foreignId('verified_by')->constrained('users');
            $t->string('surah');
            $t->unsignedSmallInteger('ayah_start');
            $t->unsignedSmallInteger('ayah_end');
            $t->date('memorized_at');
            $t->enum('quality', ['excellent', 'good', 'fair', 'needs_review']);
            $t->text('note')->nullable();
            $t->json('audio_path')->nullable();
            $t->timestamps();
            $t->index(['school_id', 'student_id']);
        });

        Schema::create('ibadah_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('student_id')->constrained();
            $t->date('log_date');
            $t->string('subuh', 10)->nullable();
            $t->string('dzuhur', 10)->nullable();
            $t->string('ashar', 10)->nullable();
            $t->string('maghrib', 10)->nullable();
            $t->string('isya', 10)->nullable();
            $t->boolean('puasa_sunnah')->default(false);
            $t->boolean('tilawah_done')->default(false);
            $t->unsignedSmallInteger('tilawah_ayah_count')->default(0);
            $t->json('extra_amalan')->nullable();
            $t->foreignId('verified_by')->nullable()->constrained('users');
            $t->timestamps();
            $t->unique(['student_id', 'log_date']);
        });

        Schema::create('kitab_kuning_progress', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('student_id')->constrained();
            $t->foreignId('teacher_id')->constrained('users');
            $t->string('kitab_name');
            $t->string('current_bab')->nullable();
            $t->unsignedInteger('halaman_terakhir')->default(0);
            $t->date('last_session');
            $t->text('catatan_ustadz')->nullable();
            $t->timestamps();
        });

        // Module 31 — AI Assistant (Dynamic)
        Schema::create('ai_providers', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('slug', 100);
            $t->string('api_format', 40);
            $t->string('base_url', 500);
            $t->text('api_key_encrypted')->nullable();
            $t->json('extra_headers')->nullable();
            $t->json('extra_config')->nullable();
            $t->boolean('is_active')->default(true);
            $t->unsignedSmallInteger('priority')->default(0);
            $t->timestamps(); $t->softDeletes();
            $t->unique(['school_id', 'slug']);
        });

        Schema::create('ai_models', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('ai_provider_id')->constrained()->cascadeOnDelete();
            $t->string('model_name');
            $t->string('display_name');
            $t->string('capability', 30)->default('chat');
            $t->unsignedInteger('context_window')->default(8192);
            $t->decimal('input_price_per_1k', 10, 6)->default(0);
            $t->decimal('output_price_per_1k', 10, 6)->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('ai_feature_assignments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->string('feature_key', 50);
            $t->foreignId('ai_model_id')->constrained();
            $t->json('feature_config')->nullable();
            $t->boolean('is_enabled')->default(true);
            $t->timestamps();
            $t->unique(['school_id', 'feature_key']);
        });

        Schema::create('ai_usage_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained();
            $t->foreignId('ai_model_id')->constrained();
            $t->string('feature_key', 50);
            $t->unsignedInteger('input_tokens');
            $t->unsignedInteger('output_tokens');
            $t->decimal('estimated_cost', 10, 6);
            $t->unsignedInteger('latency_ms');
            $t->boolean('success')->default(true);
            $t->text('error')->nullable();
            $t->timestamps();
            $t->index(['school_id', 'feature_key', 'created_at']);
        });

        // Module 35 — Live Class
        Schema::create('video_providers', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('slug', 100);
            $t->string('api_format', 40);
            $t->string('base_url', 500)->nullable();
            $t->text('client_id_encrypted')->nullable();
            $t->text('client_secret_encrypted')->nullable();
            $t->text('access_token_encrypted')->nullable();
            $t->json('extra_config')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('live_class_sessions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('class_section_id')->constrained();
            $t->foreignId('subject_id')->constrained();
            $t->foreignId('teacher_id')->constrained('users');
            $t->foreignId('video_provider_id')->nullable()->constrained();
            $t->string('topic');
            $t->dateTime('scheduled_start');
            $t->unsignedSmallInteger('duration_minutes');
            $t->string('meeting_id')->nullable();
            $t->string('join_url', 1000)->nullable();
            $t->string('host_url', 1000)->nullable();
            $t->string('passcode')->nullable();
            $t->enum('status', ['scheduled','live','ended','cancelled'])->default('scheduled');
            $t->string('recording_url', 1000)->nullable();
            $t->timestamps();
        });

        Schema::create('live_class_attendances', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('live_class_session_id')->constrained();
            $t->foreignId('student_id')->constrained();
            $t->timestamp('joined_at')->nullable();
            $t->timestamp('left_at')->nullable();
            $t->unsignedInteger('total_minutes')->default(0);
            $t->timestamps();
            $t->unique(['live_class_session_id', 'student_id']);
        });

        // Module 36 — Question Bank
        Schema::create('question_bank_categories', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('subject_id')->constrained();
            $t->string('name');
            $t->foreignId('parent_id')->nullable()->constrained('question_bank_categories');
            $t->timestamps();
        });

        Schema::create('question_bank_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('subject_id')->constrained();
            $t->foreignId('question_bank_category_id')->nullable()->constrained();
            $t->foreignId('author_id')->constrained('users');
            $t->text('question_html');
            $t->string('type', 30);
            $t->json('options')->nullable();
            $t->json('answer_key');
            $t->text('explanation_html')->nullable();
            $t->enum('difficulty', ['easy','medium','hard']);
            $t->string('cognitive_level', 4);
            $t->json('tags')->nullable();
            $t->unsignedInteger('used_count')->default(0);
            $t->decimal('avg_score_pct', 5, 2)->nullable();
            $t->decimal('discrimination', 5, 3)->nullable();
            $t->boolean('is_published')->default(true);
            $t->timestamps(); $t->softDeletes();
            $t->index(['school_id', 'subject_id', 'difficulty']);
        });

        // Module 40 — Curriculum Mapping
        Schema::create('curriculum_frameworks', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('type', 30);
            $t->json('config')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('curriculum_competencies', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('curriculum_framework_id')->constrained();
            $t->foreignId('subject_id')->constrained();
            $t->foreignId('class_room_id')->constrained();
            $t->string('code', 30);
            $t->text('description');
            $t->string('level_type', 20);
            $t->foreignId('parent_id')->nullable()->constrained('curriculum_competencies');
            $t->json('indicators')->nullable();
            $t->timestamps();
        });

        Schema::create('competency_lesson_map', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('curriculum_competency_id')->constrained()->cascadeOnDelete();
            $t->foreignId('lesson_plan_id')->constrained()->cascadeOnDelete();
            $t->timestamps();
            $t->unique(['curriculum_competency_id', 'lesson_plan_id'], 'comp_lesson_unique');
        });

        Schema::create('competency_assessments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('student_id')->constrained();
            $t->foreignId('curriculum_competency_id')->constrained();
            $t->string('mastery_level', 20);
            $t->foreignId('assessed_by')->constrained('users');
            $t->date('assessed_at');
            $t->text('evidence')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competency_assessments');
        Schema::dropIfExists('competency_lesson_map');
        Schema::dropIfExists('curriculum_competencies');
        Schema::dropIfExists('curriculum_frameworks');
        Schema::dropIfExists('question_bank_items');
        Schema::dropIfExists('question_bank_categories');
        Schema::dropIfExists('live_class_attendances');
        Schema::dropIfExists('live_class_sessions');
        Schema::dropIfExists('video_providers');
        Schema::dropIfExists('ai_usage_logs');
        Schema::dropIfExists('ai_feature_assignments');
        Schema::dropIfExists('ai_models');
        Schema::dropIfExists('ai_providers');
        Schema::dropIfExists('kitab_kuning_progress');
        Schema::dropIfExists('ibadah_logs');
        Schema::dropIfExists('hafalan_progress');
        Schema::dropIfExists('hafalan_targets');
        Schema::dropIfExists('religious_mode_config');
        Schema::dropIfExists('canteen_orders');
        Schema::dropIfExists('canteen_menu_items');
        Schema::dropIfExists('canteen_categories');
        Schema::dropIfExists('canteen_topups');
        Schema::dropIfExists('canteen_wallets');
        Schema::dropIfExists('lesson_plan_attachments');
        Schema::dropIfExists('lesson_plans');
    }
};
