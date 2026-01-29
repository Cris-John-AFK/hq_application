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
        // 1. Employees Masterlist (Lightweight, for listing)
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id')->unique(); // e.g., EMP-001
            $table->string('last_name');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('suffix')->nullable(); // Jr, Sr, III
            
            // Core Employment Details (Needed for lists/filtering)
            $table->foreignId('department_id')->constrained('departments')->onDelete('restrict');
            $table->string('position');
            $table->string('employment_status')->default('Regular'); // Regular, Probationary, Contractual
            $table->date('date_hired');
            $table->string('email')->nullable(); // Personal or Work email
            $table->string('contact_number')->nullable();
            $table->string('avatar')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Employee Details (Heavy details, One-to-One)
        Schema::create('employee_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            
            // Personal & Gov IDs
            $table->date('birthdate');
            $table->string('place_of_birth')->nullable();
            $table->string('gender')->nullable(); // Male, Female
            $table->string('civil_status')->nullable(); // Single, Married
            $table->text('address')->nullable();
            
            // Government Numbers
            $table->string('sss_number')->nullable();
            $table->string('philhealth_number')->nullable();
            $table->string('pagibig_number')->nullable();
            $table->string('tin_number')->nullable();
            
            // Emergency Contact
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_number')->nullable();
            $table->string('emergency_contact_relationship')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_details');
        Schema::dropIfExists('employees');
    }
};
