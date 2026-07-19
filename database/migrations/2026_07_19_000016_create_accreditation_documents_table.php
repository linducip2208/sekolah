<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accreditation_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('accreditation_instrument_id')->constrained('accreditation_instruments')->restrictOnDelete();
            $table->string('file_path');
            $table->text('description')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('reviewer_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'accreditation_instrument_id'], 'accred_docs_school_instrument_idx');
            $table->index(['school_id', 'status'], 'accred_docs_school_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accreditation_documents');
    }
};
