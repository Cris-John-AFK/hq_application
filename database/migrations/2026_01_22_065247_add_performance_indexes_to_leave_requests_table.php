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
        Schema::table('leave_requests', function (Blueprint $table) {
            // Adding indexes for performance optimization based on common queries
            $table->index('created_at');      // Speeds up latest() / chronological sorting
            $table->index('request_type');   // Speeds up filtering by Leave/Halfday/Undertime
            $table->index('is_paid');        // Speeds up paid vs unpaid tracking
            $table->index('to_date');        // Helps with date range lookups alongside from_date
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['request_type']);
            $table->dropIndex(['is_paid']);
            $table->dropIndex(['to_date']);
        });
    }
};
