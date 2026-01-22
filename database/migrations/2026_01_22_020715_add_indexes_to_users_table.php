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
            // Adding indexes for performance optimization
            $table->index('name');          // Speeds up search by name
            $table->index('id_number');     // Speeds up lookups by Employee ID
            $table->index('department');    // Speeds up filtering by department
            $table->index('status');        // Speeds up filtering by status (Active/On Leave)
            $table->index('role');          // Speeds up role-based queries
            $table->index('position');      // Speeds up filtering by position
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->dropIndex(['id_number']);
            $table->dropIndex(['department']);
            $table->dropIndex(['status']);
            $table->dropIndex(['role']);
            $table->dropIndex(['position']);
        });
    }
};
