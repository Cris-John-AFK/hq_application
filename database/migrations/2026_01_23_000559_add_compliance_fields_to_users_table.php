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
            $table->string('gender')->nullable()->after('status');
            $table->string('civil_status')->nullable()->after('gender');
            $table->boolean('is_solo_parent')->default(false)->after('civil_status');
            $table->integer('children_count')->default(0)->after('is_solo_parent');
            
            // Add indexes for compliance queries
            $table->index('gender');
            $table->index('is_solo_parent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['gender', 'civil_status', 'is_solo_parent', 'children_count']);
        });
    }
};
