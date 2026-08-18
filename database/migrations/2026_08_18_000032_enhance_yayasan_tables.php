<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('schools', 'foundation_id')) {
            Schema::table('schools', function (Blueprint $table) {
                $table->foreignId('foundation_id')->nullable()->after('id')->constrained('foundations')->nullOnDelete();
            });
        }

        Schema::create('foundation_master_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('foundation_id')->constrained()->cascadeOnDelete();
            $table->enum('data_type', ['subject', 'class_template', 'fee_template', 'grading_scale']);
            $table->json('data_json');
            $table->boolean('is_synced')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['foundation_id', 'data_type']);
        });

        Schema::create('foundation_user_management', function (Blueprint $table) {
            $table->id();
            $table->foreignId('foundation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role', 50);
            $table->json('assigned_schools')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['foundation_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('foundation_user_management');
        Schema::dropIfExists('foundation_master_data');

        if (Schema::hasColumn('schools', 'foundation_id')) {
            Schema::table('schools', function (Blueprint $table) {
                $table->dropForeign(['foundation_id']);
                $table->dropColumn('foundation_id');
            });
        }
    }
};
