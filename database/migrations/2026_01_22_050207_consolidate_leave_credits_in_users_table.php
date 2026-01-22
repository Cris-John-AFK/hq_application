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
        Schema::table('users', function (Blueprint $table) {
            // Add new unified leave_credits column
            $table->decimal('leave_credits', 8, 2)->default(0)->after('employment_status');
            
            // Drop old individual credit columns
            $table->dropColumn(['sil_credits', 'sick_credits', 'vacation_credits', 'emergency_credits']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Restore old columns
            $table->decimal('sil_credits', 8, 2)->default(0);
            $table->decimal('sick_credits', 8, 2)->default(0);
            $table->decimal('vacation_credits', 8, 2)->default(0);
            $table->decimal('emergency_credits', 8, 2)->default(0);
            
            // Drop unified column
            $table->dropColumn('leave_credits');
        });
    }
};
