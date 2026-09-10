<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('attendance_locks')) {
            Schema::create('attendance_locks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->constrained()->cascadeOnDelete();
                $table->foreignId('class_section_id')->constrained()->cascadeOnDelete();
                $table->date('date');
                $table->boolean('is_locked')->default(true);
                $table->foreignId('locked_by')->constrained('users');
                $table->timestamp('locked_at');
                $table->foreignId('reopened_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('reopened_at')->nullable();
                $table->text('reopen_reason')->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->unique(['school_id', 'class_section_id', 'date'], 'attendance_lock_scope_unique');
                $table->index(['school_id', 'is_locked']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_locks');
    }
};
