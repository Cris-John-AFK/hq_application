<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create Leave Requests table
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();

            // Leave Details
            $table->string('leave_type');
            $table->string('request_type');
            $table->string('category')->nullable();

            // Dates & Duration
            $table->date('from_date');
            $table->date('to_date')->nullable();
            $table->date('date_filed')->nullable();
            $table->decimal('days_taken', 5, 2);
            $table->decimal('days_paid', 5, 2)->default(0);

            // For Undertime/Halfday
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            $table->text('reason')->nullable();
            $table->string('attachment_path')->nullable();

            // Status & Admin Fields
            $table->string('status')->default('Pending');
            $table->boolean('is_paid')->default(false);
            $table->text('admin_remarks')->nullable();

            // Archiving
            $table->boolean('is_archived')->default(false)->index();
            $table->timestamp('archived_at')->nullable();

            $table->unsignedBigInteger('employee_id')->nullable()->index();

            $table->timestamps();

            // Indexes for sorting/filtering
            $table->index('status');
            $table->index('leave_type');
            $table->index('from_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
