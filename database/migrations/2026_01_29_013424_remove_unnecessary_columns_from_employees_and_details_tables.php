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
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['suffix', 'contact_number']);
        });

        Schema::table('employee_details', function (Blueprint $table) {
            $table->dropColumn([
                'address', 
                'emergency_contact_name', 
                'emergency_contact_number', 
                'emergency_contact_relationship'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('suffix')->nullable()->after('middle_name');
            $table->string('contact_number')->nullable()->after('email');
        });

        Schema::table('employee_details', function (Blueprint $table) {
            $table->text('address')->nullable()->after('civil_status');
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_number')->nullable();
            $table->string('emergency_contact_relationship')->nullable();
        });
    }
};
