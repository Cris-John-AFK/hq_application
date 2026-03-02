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
            ['value' => ['SIL', 'Solo Parent', 'Maternity', 'VAWS', 'Paternity', 'Magna Carta', 'Emergency']]
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
    }
}
