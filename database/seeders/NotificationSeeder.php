<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = \App\Models\User::where('role', 'admin')->first();
        if (! $admin) {
            return;
        }

        \App\Models\Notification::create([
            'user_id' => $admin->id,
            'title' => 'New Leave Request',
            'message' => 'Jessica Roque has submitted a new Sick Leave request for approval.',
            'type' => 'info',
            'link' => '/manage-leaves',
        ]);

        \App\Models\Notification::create([
            'user_id' => $admin->id,
            'title' => 'System Backup Completed',
            'message' => 'The daily system backup was completed successfully at 01:00 AM.',
            'type' => 'success',
        ]);

        \App\Models\Notification::create([
            'user_id' => $admin->id,
            'title' => 'High Absenteeism Alert',
            'message' => 'The Cutting department shows a 15% drop in attendance this week.',
            'type' => 'warning',
            'link' => '/reports',
        ]);
    }
}
