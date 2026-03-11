<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SystemSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\SystemSetting::updateOrCreate(
            ['key' => 'leave_request_types'],
            ['value' => ['Leave', 'Halfday', 'Undertime', 'Official Business']]
        );

        \App\Models\SystemSetting::updateOrCreate(
            ['key' => 'leave_types'],
            ['value' => ['Sick Leave', 'Vacation Leave', 'SIL', 'Solo Parent', 'Maternity', 'VAWC', 'Paternity', 'Magna Carta', 'Emergency', 'Bereavement']]
        );

        \App\Models\SystemSetting::updateOrCreate(
            ['key' => 'attendance_categories'],
            [
                'value' => [
                    ['code' => 'UA', 'label' => 'Unauthorized Absence'],
                    ['code' => 'WMC', 'label' => 'With Medical Certificate'],
                    ['code' => 'WD', 'label' => 'With Documents'],
                    ['code' => 'UH', 'label' => 'Unauthorized Halfday']
                ]
            ]
        );

        \App\Models\SystemSetting::updateOrCreate(
            ['key' => 'leave_form_sections'],
            [
                'value' => [
                    ['id' => 'header', 'type' => 'core', 'title' => 'Personnel Leave Authorization Form', 'description' => 'Please fill out all required details accurately.', 'removable' => false, 'visible' => true],
                    ['id' => 'attendance_category', 'type' => 'core', 'title' => 'Attendance Category', 'description' => 'Classification for disciplinary action tracking.', 'removable' => true, 'visible' => true],
                    ['id' => 'request_type', 'type' => 'core', 'title' => 'Leave Request For', 'description' => 'Select the duration/nature of your request.', 'removable' => false, 'visible' => true],
                    ['id' => 'leave_type', 'type' => 'core', 'title' => 'Leave Type', 'description' => 'Select the specific leave benefit.', 'removable' => false, 'visible' => true],
                    ['id' => 'details', 'type' => 'core', 'title' => 'Details of Leave / Halfday / Undertime', 'description' => 'Specify the dates and pay configuration.', 'removable' => false, 'visible' => true],
                    ['id' => 'attachment', 'type' => 'core', 'title' => 'Supporting Document', 'description' => 'Upload necessary proof (Optional).', 'removable' => true, 'visible' => true],
                    ['id' => 'reason', 'type' => 'core', 'title' => 'Reason for Leave', 'description' => 'Describe the purpose of your request.', 'removable' => false, 'visible' => true],
                ]
            ]
        );
        \App\Models\SystemSetting::updateOrCreate(
            ['key' => 'working_hours'],
            [
                'value' => [
                    ['code' => 'A', 'time' => '07:00 AM - 03:00 PM'],
                    ['code' => 'B', 'time' => '07:00 PM - 04:00 AM'],
                    ['code' => 'C', 'time' => '06:00 AM - 02:00 PM'],
                    ['code' => 'D', 'time' => '02:00 PM - 10:00 PM'],
                    ['code' => 'E', 'time' => '08:00 AM - 04:00 PM'],
                ]
            ]
        );
    }
}
