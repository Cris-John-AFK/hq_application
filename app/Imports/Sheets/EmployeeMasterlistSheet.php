<?php

namespace App\Imports\Sheets;

use App\Models\Employee;
use App\Models\EmployeeDetail;
use App\Models\Department;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class EmployeeMasterlistSheet implements ToCollection, WithStartRow
{
    public function startRow(): int
    {
        return 9; // Data row starts at 9 (Header is usually on 8)
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            try {
                // Sniffer logic to handle both "Wide/Spaced" (old) and "Dense" (new) formats
                // Dense: [1]=ID, [2]=Last, [3]=First
                // Spaced: [2]=ID, [4]=Last, [8]=First (This was the previous assumed mapping)

                $empId = null;
                $lastName = null;
                $firstName = null;
                $middleName = null;
                $deptName = 'Unassigned';
                $position = 'Staff';
                $rawBirth = null;
                $rawGender = 'Male';
                $civilStatus = 'Single';
                $rawHired = null;
                $sss = null;
                $pagibig = null;
                $philhealth = null;
                $tin = null;
                $status = 'Regular';

                // Corrected Mapping for Native Dense Format (Report Sheet)
                // This corresponds to the user's masterlist export structure
                $empId = $row[1] ?? null;
                $lastName = $row[2] ?? '';
                $firstName = $row[3] ?? '';
                $middleName = $row[4] ?? null;

                // Ensure we skip the header row even if startRow doesn't catch it
                if (strtoupper($row[2] ?? '') === 'LAST NAME') {
                    continue;
                }

                $deptName = trim($row[5] ?? 'Unassigned');
                $position = trim($row[6] ?? 'Staff');
                $rawBirth = $row[7] ?? null;
                $rawGender = $row[9] ?? 'Male';
                $civilStatus = $row[10] ?? 'Single';
                $rawHired = $row[11] ?? null;

                // Government IDs (Confirmed indices based on exact JSON parsing of your file)
                $sss = $row[12] ?? null;
                $pagibig = $row[13] ?? null;
                $philhealth = $row[14] ?? null;
                $tin = $row[15] ?? null;
                $status = 'Regular';



                if (!$empId || !$lastName)
                    continue;

                $status = stripos($status, 'Probation') !== false ? 'Probationary' : 'Regular';
                $dateHired = $this->extractDate($rawHired);

                $position = trim(str_ireplace(['ACTIVE', 'SEIn-charge'], ['Active', 'In-charge'], $position));

                $department = Department::firstOrCreate(['name' => $deptName]);

                $employee = Employee::where('employee_id', $empId)->first();
                if ($employee) {
                    $employee->update([
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'middle_name' => $middleName,
                        'department_id' => $department->id,
                        'position' => $position,
                        'employment_status' => $status,
                        'date_hired' => $dateHired,
                    ]);
                } else {
                    $employee = Employee::create([
                        'employee_id' => $empId,
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'middle_name' => $middleName,
                        'department_id' => $department->id,
                        'position' => $position,
                        'employment_status' => $status,
                        'date_hired' => $dateHired,
                        'vacation_leave' => 5, // Default for new hires
                        'is_archived' => false,
                    ]);
                }

                $birthdate = $this->extractDate($rawBirth);
                if ($birthdate === now()->format('Y-m-d'))
                    $birthdate = '1900-01-01';

                $gender = 'Male';
                if (!empty($rawGender)) {
                    $genderLower = strtolower(trim($rawGender));
                    if (in_array($genderLower, ['female', 'f', 'fem'])) {
                        $gender = 'Female';
                    }
                }

                EmployeeDetail::updateOrCreate(
                    ['employee_id' => $employee->id],
                    [
                        'birthdate' => $birthdate,
                        'gender' => $gender,
                        'civil_status' => $civilStatus ?? 'Single',
                        'sss_number' => $sss ?? null,
                        'pagibig_number' => $pagibig ?? null,
                        'philhealth_number' => $philhealth ?? null,
                        'tin_number' => $tin ?? null,
                    ]
                );
            } catch (\Exception $e) {
                Log::error('Row error for ID ' . ($empId ?? 'unknown') . ': ' . $e->getMessage());
                continue;
            }
        }

        \App\Models\Department::cleanup();
        \App\Models\AttendanceRecord::syncDepartments();
    }

    private function extractDate($value)
    {
        if (!$value)
            return now()->format('Y-m-d');

        if (preg_match('/^(\d+)/', $value, $matches)) {
            $num = $matches[1];
            if ($num > 10000 && $num < 90000) {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($num)->format('Y-m-d');
            }
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return now()->format('Y-m-d');
        }
    }
}
