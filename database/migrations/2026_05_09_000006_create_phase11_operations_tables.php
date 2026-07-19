<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Module 32 — Dapodik
        Schema::create('dapodik_config', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->unique()->constrained()->cascadeOnDelete();
            $t->string('npsn', 15);
            $t->string('username_encrypted', 500)->nullable();
            $t->string('password_encrypted', 500)->nullable();
            $t->string('endpoint_url', 500)->nullable();
            $t->json('field_mappings')->nullable();
            $t->timestamp('last_sync_at')->nullable();
            $t->timestamps();
        });

        Schema::create('dapodik_sync_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->enum('direction', ['import', 'export']);
            $t->string('entity', 30);
            $t->unsignedInteger('records_total')->default(0);
            $t->unsignedInteger('records_success')->default(0);
            $t->unsignedInteger('records_failed')->default(0);
            $t->json('errors')->nullable();
            $t->enum('status', ['running','completed','failed'])->default('running');
            $t->foreignId('triggered_by')->constrained('users');
            $t->timestamps();
        });

        // Module 33 — Visitor
        Schema::create('visitor_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->string('visitor_name');
            $t->string('id_number')->nullable();
            $t->string('phone')->nullable();
            $t->string('photo_path')->nullable();
            $t->string('purpose');
            $t->foreignId('host_user_id')->nullable()->constrained('users');
            $t->string('badge_no', 20)->nullable();
            $t->timestamp('checked_in_at');
            $t->timestamp('checked_out_at')->nullable();
            $t->foreignId('logged_by')->constrained('users');
            $t->json('items_carried')->nullable();
            $t->boolean('is_blacklisted')->default(false);
            $t->text('note')->nullable();
            $t->timestamps();
            $t->index(['school_id', 'checked_in_at']);
        });

        Schema::create('visitor_blacklist', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->string('id_number')->nullable();
            $t->string('full_name');
            $t->text('reason');
            $t->foreignId('added_by')->constrained('users');
            $t->timestamps();
        });

        // Module 34 — Inventory
        Schema::create('asset_categories', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('icon')->nullable();
            $t->timestamps();
        });

        Schema::create('assets', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('asset_category_id')->constrained();
            $t->string('asset_code', 50)->unique();
            $t->string('name');
            $t->text('description')->nullable();
            $t->string('serial_number')->nullable();
            $t->date('purchased_at')->nullable();
            $t->unsignedInteger('purchase_price')->nullable();
            $t->date('warranty_until')->nullable();
            $t->string('location')->nullable();
            $t->string('photo_path')->nullable();
            $t->enum('condition', ['excellent','good','fair','damaged','disposed'])->default('good');
            $t->enum('status', ['available','borrowed','maintenance','disposed'])->default('available');
            $t->json('specs')->nullable();
            $t->timestamps(); $t->softDeletes();
        });

        Schema::create('asset_loans', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('asset_id')->constrained();
            $t->foreignId('borrower_id')->constrained('users');
            $t->foreignId('approved_by')->nullable()->constrained('users');
            $t->date('borrowed_at');
            $t->date('due_at');
            $t->date('returned_at')->nullable();
            $t->enum('status', ['pending','active','overdue','returned','lost'])->default('pending');
            $t->text('note')->nullable();
            $t->timestamps();
        });

        Schema::create('maintenance_requests', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('asset_id')->nullable()->constrained();
            $t->string('location_text')->nullable();
            $t->foreignId('reported_by')->constrained('users');
            $t->text('issue_description');
            $t->json('photos')->nullable();
            $t->enum('priority', ['low','medium','high','critical'])->default('medium');
            $t->enum('status', ['reported','assigned','in_progress','resolved','rejected'])->default('reported');
            $t->foreignId('assigned_to')->nullable()->constrained('users');
            $t->text('resolution_note')->nullable();
            $t->timestamp('resolved_at')->nullable();
            $t->unsignedInteger('cost')->nullable();
            $t->timestamps();
        });

        // Module 41 — Yayasan / Foundation
        Schema::create('foundations', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->string('logo_path')->nullable();
            $t->text('address')->nullable();
            $t->string('npwp', 30)->nullable();
            $t->json('contact')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps(); $t->softDeletes();
        });

        Schema::create('foundation_school_links', function (Blueprint $t) {
            $t->id();
            $t->foreignId('foundation_id')->constrained()->cascadeOnDelete();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->date('joined_at');
            $t->boolean('is_primary_school')->default(false);
            $t->timestamps();
            $t->unique(['foundation_id', 'school_id']);
        });

        Schema::create('foundation_admins', function (Blueprint $t) {
            $t->id();
            $t->foreignId('foundation_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->enum('role', ['ketua_yayasan','pengurus','bendahara','sekretaris']);
            $t->timestamps();
        });

        // Module 45 — Learning Analytics
        Schema::create('student_risk_scores', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('student_id')->constrained();
            $t->date('snapshot_date');
            $t->decimal('attendance_score', 5, 2);
            $t->decimal('academic_score', 5, 2);
            $t->decimal('behavior_score', 5, 2);
            $t->decimal('engagement_score', 5, 2);
            $t->decimal('overall_risk', 5, 2);
            $t->enum('risk_level', ['low','medium','high','critical'])->default('low');
            $t->json('top_risk_factors')->nullable();
            $t->json('recommendations')->nullable();
            $t->timestamps();
            $t->unique(['student_id', 'snapshot_date']);
        });

        Schema::create('learning_analytics_reports', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->enum('scope', ['school','class','subject','student']);
            $t->foreignId('class_section_id')->nullable()->constrained();
            $t->foreignId('subject_id')->nullable()->constrained();
            $t->foreignId('student_id')->nullable()->constrained();
            $t->date('period_start');
            $t->date('period_end');
            $t->json('metrics');
            $t->text('narrative')->nullable();
            $t->foreignId('generated_by')->constrained('users');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_analytics_reports');
        Schema::dropIfExists('student_risk_scores');
        Schema::dropIfExists('foundation_admins');
        Schema::dropIfExists('foundation_school_links');
        Schema::dropIfExists('foundations');
        Schema::dropIfExists('maintenance_requests');
        Schema::dropIfExists('asset_loans');
        Schema::dropIfExists('assets');
        Schema::dropIfExists('asset_categories');
        Schema::dropIfExists('visitor_blacklist');
        Schema::dropIfExists('visitor_logs');
        Schema::dropIfExists('dapodik_sync_logs');
        Schema::dropIfExists('dapodik_config');
    }
};
