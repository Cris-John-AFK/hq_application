<?php

namespace App\Imports\Sheets;

use App\Models\Employee;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Support\Facades\Log;

class EmployeeLeavesSheet implements ToCollection, WithStartRow
{
    // The Working Hours schedule mapping defined by the second sheet image
    // Code A, B, C, D, E
    private const WORKING_HOURS_MAP = [
        'A' => '7:00 AM - 3:00 PM',
        'B' => '7:00 PM - 4:00 AM',
        'C' => '6:00 AM - 2:00 PM',
        'D' => '2:00 PM - 10:00 PM',
        'E' => '8:00 AM - 4:00 PM'
    ];

    public function startRow(): int
    {
        return 9; // Aligning with Masterlist sheet starting row
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            try {
                $empId = $row[1] ?? null;
                if (!$empId || !is_numeric($empId))
                    continue;

                $employee = Employee::where('employee_id', $empId)->first();

                if ($employee) {
                    $workingHoursCode = strtoupper(trim($row[13] ?? ''));
                    $workingHoursStr = self::WORKING_HOURS_MAP[$workingHoursCode] ?? null;

                    $vl = max(0, intval($row[14] ?? 0)); // Vacation Leave
                    $sl = 0;                              // Sick Leave – not in this sheet, always 0
                    $pl = max(0, intval($row[15] ?? 0)); // Paternity Leave
                    $sp = max(0, intval($row[16] ?? 0)); // Solo Parent
                    $bl = max(0, intval($row[17] ?? 0)); // Bereavement
                    $vawc = max(0, intval($row[18] ?? 0)); // VAWC

                    // Update all leave columns
                    $updateData = [
                        'vacation_leave' => $vl,
                        'sick_leave' => $sl,
                        'paternity_leave' => $pl,
                        'solo_parent_leave' => $sp,
                        'bereavement_leave' => $bl,
                        'vawc_leave' => $vawc,
                        'maternity_leave' => 0,
                        'magna_carta_leave' => 0,
                        'emergency_leave' => 0,
                    ];


                    if ($workingHoursStr) {
                        $updateData['working_hours'] = $workingHoursStr;
                    }

                    $employee->update($updateData);
                }
            } catch (\Exception $e) {
                Log::error('Row error for LEAVE DATA ID ' . ($empId ?? 'unknown') . ': ' . $e->getMessage());
                continue;
            }
        }
    }
}
