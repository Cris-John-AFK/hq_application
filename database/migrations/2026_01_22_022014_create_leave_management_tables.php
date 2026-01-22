<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add fields to Users table
        Schema::table('users', function (Blueprint $table) {
            $table->string('employment_status')->default('Probationary'); // Probationary, Regular
            $table->decimal('sil_credits', 5, 2)->default(0); // Service Incentive Leave credits
        });

        // Create Leave Requests table
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            
            // Leave Details
            $table->string('leave_type'); // SIL, Maternity, Paternity, Emergency, Solo Parent, VAWS, MAGNA CARTA, Others
            $table->string('request_type'); // Leave, Halfday, Undertime, Official Business
            
            // Dates & Duration
            $table->date('from_date');
            $table->date('to_date')->nullable();
            $table->decimal('days_taken', 5, 2); // e.g., 0.5, 1.0, 2.5
            
            // For Undertime/Halfday
            $table->time('start_time')->nullable(); 
            $table->time('end_time')->nullable();
            
            $table->text('reason')->nullable();
            
            // Status & Admin Fields
            $table->string('status')->default('Pending'); // Pending, Approved, Rejected, Cancelled
            $table->boolean('is_paid')->default(false); // Helper for Admin/HR logic
            $table->text('admin_remarks')->nullable();
            
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
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['employment_status', 'sil_credits']);
        });
    }
};
