<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adiwiyata_evidences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('adiwiyata_indicator_id')->constrained('adiwiyata_indicators')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('file_path')->nullable();
            $table->integer('score')->default(0);
            $table->string('status')->default('draft');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('adiwiyata_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('achieved_level');
            $table->date('achieved_date');
            $table->string('certificate_number')->nullable();
            $table->string('certificate_file')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adiwiyata_levels');
        Schema::dropIfExists('adiwiyata_evidences');
    }
};
