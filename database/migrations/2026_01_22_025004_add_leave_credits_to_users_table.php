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
            $table->decimal('sick_credits', 5, 2)->default(0)->after('sil_credits');
            $table->decimal('vacation_credits', 5, 2)->default(0)->after('sick_credits');
            $table->decimal('emergency_credits', 5, 2)->default(0)->after('vacation_credits');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['sick_credits', 'vacation_credits', 'emergency_credits']);
        });
    }
};
