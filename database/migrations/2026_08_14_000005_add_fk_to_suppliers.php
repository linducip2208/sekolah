<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $t) {
            if (!Schema::hasColumn('suppliers', 'school_id')) {
                $t->unsignedBigInteger('school_id')->nullable()->after('id');
            }
            // Ensure index exists before adding the FK.
            $t->index('school_id');
            $t->foreign('school_id')->references('id')->on('schools')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $t) {
            $t->dropForeign(['school_id']);
        });
    }
};
