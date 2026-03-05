<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {

            // ── Stage 1: Dept Head Review ──────────────────────────────────────
            if (!Schema::hasColumn('leave_requests', 'dept_head_id')) {
                $table->foreignId('dept_head_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete()
                    ->after('admin_remarks');
            }

            if (!Schema::hasColumn('leave_requests', 'dept_head_remarks')) {
                $table->string('dept_head_remarks')->nullable()->after('dept_head_id');
            }

            if (!Schema::hasColumn('leave_requests', 'dept_reviewed_at')) {
                $table->timestamp('dept_reviewed_at')->nullable()->after('dept_head_remarks');
            }

            // ── Stage 2: HR / Admin Final Approval ────────────────────────────
            if (!Schema::hasColumn('leave_requests', 'hr_approved_by')) {
                $table->foreignId('hr_approved_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete()
                    ->after('dept_reviewed_at');
            }

            if (!Schema::hasColumn('leave_requests', 'hr_remarks')) {
                $table->string('hr_remarks')->nullable()->after('hr_approved_by');
            }

            if (!Schema::hasColumn('leave_requests', 'hr_approved_at')) {
                $table->timestamp('hr_approved_at')->nullable()->after('hr_remarks');
            }

            // ── Justification (may already exist from an earlier migration) ────
            if (!Schema::hasColumn('leave_requests', 'justification')) {
                $table->text('justification')->nullable()->after('admin_remarks');
            }
        });
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {

            // Drop foreign keys only if the columns exist
            if (Schema::hasColumn('leave_requests', 'dept_head_id')) {
                $table->dropForeign(['dept_head_id']);
                $table->dropColumn('dept_head_id');
            }
            if (Schema::hasColumn('leave_requests', 'dept_head_remarks')) {
                $table->dropColumn('dept_head_remarks');
            }
            if (Schema::hasColumn('leave_requests', 'dept_reviewed_at')) {
                $table->dropColumn('dept_reviewed_at');
            }

            if (Schema::hasColumn('leave_requests', 'hr_approved_by')) {
                $table->dropForeign(['hr_approved_by']);
                $table->dropColumn('hr_approved_by');
            }
            if (Schema::hasColumn('leave_requests', 'hr_remarks')) {
                $table->dropColumn('hr_remarks');
            }
            if (Schema::hasColumn('leave_requests', 'hr_approved_at')) {
                $table->dropColumn('hr_approved_at');
            }
        });
    }
};
