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
        $users = \App\Models\User::all();
        foreach ($users as $user) {
            $newId = 'HQI-' . str_pad($user->id, 4, '0', STR_PAD_LEFT);
            $user->update(['id_number' => $newId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No easy rollback for random previous IDs
    }
};
