<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AnnouncementSeeder extends Seeder
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

        \App\Models\Announcement::create([
            'title' => 'Welcome to the New HatQ Portal!',
            'content' => 'We are excited to launch our new internal management system. Explore the dashboard to see your performance metrics and leave balances.',
            'type' => 'success',
            'priority' => 1,
            'created_by' => $admin->id,
        ]);

        \App\Models\Announcement::create([
            'title' => 'Upcoming Holiday: Chinese New Year',
            'content' => 'Please be advised that our offices will be closed on January 29, 2026, in observance of Chinese New Year. Regular operations will resume the following day.',
            'type' => 'info',
            'priority' => 2,
            'created_by' => $admin->id,
        ]);

        \App\Models\Announcement::create([
            'title' => 'System Maintenance tonight',
            'content' => 'The system will undergo brief maintenance at 11:00 PM tonight. Please save your work before then.',
            'type' => 'warning',
            'priority' => 3,
            'created_by' => $admin->id,
        ]);
    }
}
