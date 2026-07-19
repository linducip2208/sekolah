<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->boolean('is_digital')->default(false)->after('is_active');
            $table->string('digital_file_path')->nullable()->after('is_digital');
            $table->string('file_type', 20)->nullable()->after('digital_file_path')->comment('pdf, epub');
            $table->bigInteger('file_size')->nullable()->after('file_type')->comment('bytes');
            $table->integer('page_count')->nullable()->after('file_size');
            $table->integer('preview_pages')->nullable()->default(10)->after('page_count');
            $table->boolean('is_downloadable')->default(false)->after('preview_pages');
            $table->integer('download_count')->default(0)->after('is_downloadable');
            $table->integer('read_count')->default(0)->after('download_count');
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn([
                'is_digital', 'digital_file_path', 'file_type', 'file_size',
                'page_count', 'preview_pages', 'is_downloadable',
                'download_count', 'read_count',
            ]);
        });
    }
};
