<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('letters', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('letter_template_id')->nullable()->constrained()->nullOnDelete();
            $t->string('letter_number', 100);
            $t->string('recipient_type', 20)->default('other');
            $t->unsignedBigInteger('recipient_id')->nullable();
            $t->string('recipient_name', 200)->nullable();
            $t->text('recipient_address')->nullable();
            $t->string('subject', 255)->nullable();
            $t->text('content');
            $t->string('status', 20)->default('draft');
            $t->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('issued_at')->nullable();
            $t->text('notes')->nullable();
            $t->timestamps();
            $t->softDeletes();
            $t->index(['school_id', 'status']);
            $t->index(['school_id', 'recipient_type', 'recipient_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('letters');
    }
};
