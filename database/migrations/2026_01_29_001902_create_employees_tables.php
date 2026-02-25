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
        // 1. Employees Masterlist
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id')->unique();
            $table->string('last_name');
            $table->string('first_name');
            $table->string('middle_name')->nullable();

            $table->foreignId('department_id')->constrained('departments')->onDelete('restrict');
            $table->string('position');
            $table->string('employment_status')->default('Regular');
            $table->date('date_hired');
            $table->string('email')->nullable();
            $table->string('avatar')->nullable();
            $table->decimal('leave_credits', 8, 2)->default(0);

            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Employee Details
        Schema::create('employee_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');

            $table->date('birthdate');
            $table->string('place_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->string('civil_status')->nullable();

            $table->string('sss_number')->nullable();
            $table->string('philhealth_number')->nullable();
            $table->string('pagibig_number')->nullable();
            $table->string('tin_number')->nullable();

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
