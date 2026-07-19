<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('letter_templates', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->string('name', 200);
            $t->string('code', 50);
            $t->text('content');
            $t->json('variables')->nullable();
            $t->string('category', 40)->default('sk');
            $t->boolean('is_active')->default(true);
            $t->timestamps();
            $t->softDeletes();
            $t->unique(['school_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('letter_templates');
    }
};
