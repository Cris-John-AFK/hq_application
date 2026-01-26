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
        Schema::create('system_logs', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $blueprint->string('module'); // e.g., 'Leaves', 'Users', 'Announcements'
            $blueprint->string('action'); // e.g., 'Created', 'Updated', 'Deleted', 'Login'
            $blueprint->text('description');
            $blueprint->json('old_data')->nullable();
            $blueprint->json('new_data')->nullable();
            $blueprint->string('ip_address')->nullable();
            $blueprint->string('user_agent')->nullable();
            $blueprint->timestamps();

            // Index for faster searching
            $blueprint->index(['module', 'action']);
            $blueprint->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_logs');
    }
};
