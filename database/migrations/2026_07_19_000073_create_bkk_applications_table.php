<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bkk_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('job_listing_id')->constrained('job_listings')->cascadeOnDelete();
            $table->foreignId('bkk_partner_id')->nullable()->constrained('bkk_partners')->nullOnDelete();
            $table->date('application_date');
            $table->string('status')->default('applied');
            $table->date('interview_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['student_id', 'status']);
            $table->index(['job_listing_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bkk_applications');
    }
};
