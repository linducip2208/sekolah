<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bkk_partners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('company_name');
            $table->string('industry_type')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('mou_status')->default('draft');
            $table->string('mou_file_path')->nullable();
            $table->date('mou_start_date')->nullable();
            $table->date('mou_end_date')->nullable();
            $table->string('partnership_level')->default('bronze');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'mou_status']);
            $table->index(['school_id', 'partnership_level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bkk_partners');
    }
};
