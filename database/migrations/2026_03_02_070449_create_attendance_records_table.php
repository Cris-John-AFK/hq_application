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
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id_number')->nullable(); // String ID like HQI-0001
            $table->string('employee_name')->nullable();
            $table->date('date');
            $table->string('time_in')->nullable();
            $table->string('time_out')->nullable();
            $table->decimal('hours_worked', 8, 2)->default(0);
            $table->string('status')->nullable();
            $table->string('department')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};
