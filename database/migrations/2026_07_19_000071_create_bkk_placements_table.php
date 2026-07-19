<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bkk_placements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('bkk_partner_id')->constrained('bkk_partners')->restrictOnDelete();
            $table->foreignId('job_listing_id')->nullable()->constrained('job_listings')->nullOnDelete();
            $table->string('position');
            $table->date('placement_date');
            $table->date('start_date')->nullable();
            $table->bigInteger('salary')->default(0);
            $table->string('contract_type')->default('internship');
            $table->string('status')->default('active');
            $table->string('supervisor_name')->nullable();
            $table->string('supervisor_phone')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'status']);
            $table->index(['school_id', 'contract_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bkk_placements');
    }
};
