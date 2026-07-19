<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Module 14 — Library
        Schema::create('book_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
            $table->unique(['school_id', 'name']);
        });

        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_category_id')->constrained();
            $table->string('title');
            $table->string('author')->nullable();
            $table->string('isbn')->nullable();
            $table->string('publisher')->nullable();
            $table->year('publish_year')->nullable();
            $table->string('edition')->nullable();
            $table->unsignedSmallInteger('total_quantity')->default(1);
            $table->unsignedSmallInteger('available_quantity')->default(1);
            $table->string('cover')->nullable();
            $table->string('barcode')->nullable();
            $table->text('description')->nullable();
            $table->string('rack_location')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['school_id', 'title']);
            $table->index(['school_id', 'barcode']);
        });

        Schema::create('book_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained();
            $table->foreignId('issued_to')->constrained('users');
            $table->foreignId('issued_by')->constrained('users');
            $table->foreignId('returned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->date('issue_date');
            $table->date('due_date');
            $table->date('return_date')->nullable();
            $table->enum('status', ['issued', 'returned', 'overdue', 'lost'])->default('issued');
            $table->unsignedInteger('fine_amount')->default(0);
            $table->boolean('fine_paid')->default(false);
            $table->string('note')->nullable();
            $table->timestamps();
            $table->index(['school_id', 'issued_to', 'status']);
            $table->index(['school_id', 'book_id', 'status']);
            $table->index(['school_id', 'due_date', 'status']);
        });

        // Module 15 — Hostel
        Schema::create('hostels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->enum('type', ['boys', 'girls', 'mixed'])->default('boys');
            $table->string('warden_name')->nullable();
            $table->timestamps();
        });

        Schema::create('hostel_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hostel_id')->constrained()->cascadeOnDelete();
            $table->string('room_no');
            $table->unsignedSmallInteger('capacity')->default(4);
            $table->unsignedSmallInteger('occupied')->default(0);
            $table->enum('status', ['available', 'partial', 'full'])->default('available');
            $table->unsignedInteger('fee_per_month')->default(0); // cents
            $table->timestamps();
            $table->unique(['hostel_id', 'room_no']);
        });

        Schema::create('hostel_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('hostel_room_id')->constrained();
            $table->date('from_date');
            $table->date('to_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['student_id', 'is_active']); // max 1 active per student
        });

        // Module 16 — Transport
        Schema::create('transport_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('fee_per_month')->default(0); // cents
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('transport_route_stops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transport_route_id')->constrained()->cascadeOnDelete();
            $table->string('stop_name');
            $table->time('pickup_time')->nullable();
            $table->unsignedSmallInteger('order')->default(0);
            $table->timestamps();
        });

        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('registration_no')->unique();
            $table->string('make_model')->nullable();
            $table->unsignedSmallInteger('capacity')->default(40);
            $table->string('driver_name')->nullable();
            $table->string('driver_phone', 30)->nullable();
            $table->timestamps();
        });

        Schema::create('student_transports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('transport_route_id')->constrained();
            $table->foreignId('transport_route_stop_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_transports');
        Schema::dropIfExists('vehicles');
        Schema::dropIfExists('transport_route_stops');
        Schema::dropIfExists('transport_routes');
        Schema::dropIfExists('hostel_allocations');
        Schema::dropIfExists('hostel_rooms');
        Schema::dropIfExists('hostels');
        Schema::dropIfExists('book_issues');
        Schema::dropIfExists('books');
        Schema::dropIfExists('book_categories');
    }
};
