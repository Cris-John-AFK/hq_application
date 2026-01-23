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
        Schema::create('leave_action_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leave_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained(); // The admin who performed the action
            $table->string('action'); // Approved, Rejected, Cancelled, Flagged
            $table->text('justification')->nullable();
            $table->json('snapshot_data')->nullable(); // Captures flags, credits, impact at that moment
            $table->timestamps();
            
            $table->index('action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_action_logs');
    }
};
