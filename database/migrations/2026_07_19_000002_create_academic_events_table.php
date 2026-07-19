<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('academic_events', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->string('title', 255);
            $t->text('description')->nullable();
            $t->string('event_type', 30)->default('other');
            $t->dateTime('start_date');
            $t->dateTime('end_date')->nullable();
            $t->boolean('all_day')->default(true);
            $t->string('color', 20)->nullable();
            $t->foreignId('class_section_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->boolean('is_published')->default(true);
            $t->timestamps();
            $t->softDeletes();
            $t->index(['school_id', 'event_type']);
            $t->index(['school_id', 'start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_events');
    }
};
