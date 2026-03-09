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
        Schema::table('employees', function (Blueprint $table) {
            $table->decimal('maternity_leave', 8, 2)->default(0)->after('vawc_leave');
            $table->decimal('magna_carta_leave', 8, 2)->default(0)->after('maternity_leave');
            $table->decimal('emergency_leave', 8, 2)->default(0)->after('magna_carta_leave');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['maternity_leave', 'magna_carta_leave', 'emergency_leave']);
        });
    }
};
