<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incoming_mails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('mail_no', 50);
            $table->string('sender_name', 200);
            $table->string('sender_address')->nullable();
            $table->string('subject', 300);
            $table->date('received_date');
            $table->foreignId('disposition_to')->nullable()->constrained('users')->nullOnDelete();
            $table->text('disposition_notes')->nullable();
            $table->enum('status', ['received', 'dispositioned', 'archived'])->default('received');
            $table->string('document_path')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'status']);
        });

        Schema::create('outgoing_mails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('mail_no', 50);
            $table->string('recipient_name', 200);
            $table->string('recipient_address')->nullable();
            $table->string('subject', 300);
            $table->date('sent_date');
            $table->foreignId('letter_template_id')->nullable()->constrained('letter_templates')->nullOnDelete();
            $table->enum('status', ['draft', 'sent', 'archived'])->default('draft');
            $table->string('document_path')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'status']);
        });

        Schema::create('meeting_agendas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('title', 300);
            $table->text('description')->nullable();
            $table->date('meeting_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('location', 200)->nullable();
            $table->foreignId('organizer_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['planned', 'in_progress', 'completed', 'cancelled'])->default('planned');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'meeting_date']);
        });

        Schema::create('meeting_minutes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('agenda_id')->constrained('meeting_agendas')->cascadeOnDelete();
            $table->text('content');
            $table->json('attendees')->nullable();
            $table->json('decisions')->nullable();
            $table->json('follow_up_items')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'agenda_id']);
        });

        Schema::create('staff_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('title', 300);
            $table->text('description')->nullable();
            $table->foreignId('assigned_to')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_by')->constrained('users')->cascadeOnDelete();
            $table->date('due_date')->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['todo', 'in_progress', 'done', 'overdue'])->default('todo');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'assigned_to', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_tasks');
        Schema::dropIfExists('meeting_minutes');
        Schema::dropIfExists('meeting_agendas');
        Schema::dropIfExists('outgoing_mails');
        Schema::dropIfExists('incoming_mails');
    }
};
