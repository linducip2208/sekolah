<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ppdb_applications', function (Blueprint $table) {
            $table->decimal('entrance_test_score', 5, 2)->nullable()->after('average_score');
            $table->decimal('interview_score', 5, 2)->nullable()->after('entrance_test_score');
            $table->foreignId('enrolled_student_id')->nullable()->after('form_payment_id')->constrained('students')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ppdb_applications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('enrolled_student_id');
            $table->dropColumn(['entrance_test_score', 'interview_score']);
        });
    }
};
