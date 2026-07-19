<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('school_data_exports', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('requested_by')->constrained('users');
            $t->string('format', 10)->default('zip');           // zip, csv-only, json-only
            $t->string('status', 20)->default('queued');         // queued, processing, completed, failed
            $t->string('file_path', 500)->nullable();
            $t->unsignedBigInteger('file_size_bytes')->nullable();
            $t->text('error')->nullable();
            $t->json('included_tables')->nullable();             // list of table names
            $t->unsignedInteger('row_count')->nullable();
            $t->timestamp('started_at')->nullable();
            $t->timestamp('completed_at')->nullable();
            $t->timestamp('expires_at')->nullable();
            $t->timestamps();
            $t->index(['school_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_data_exports');
    }
};
