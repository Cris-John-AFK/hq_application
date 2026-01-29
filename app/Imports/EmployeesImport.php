<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\EmployeeDetail;
use App\Models\Department;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class EmployeesImport implements ToCollection, WithStartRow
{
    public function startRow(): int
    {
        return 10;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            try {
                // Correct Mapping based on Row 9 Headers:
            // [2]  => Employee ID
            // [4]  => Last Name
            // [8]  => First Name
            // [10] => MI
            // [13] => Department
            // [15] => Position
            // [17] => Birthdate
            // [21] => Gender
            // [25] => Date Hired
            // [27] => SSS #
            // [30] => Pag-IBIG
            // [32] => PhilHealth
            // [34] => TIN
            // [44] => Status
            
            $empId = $row[2] ?? null; 
            $lastName = $row[4] ?? null;
            
            if (!$empId || !$lastName) {
                // Fallback: check if indices shifted (sometimes index 1 and 3 in previous dump?)
                // Step 1842 dump showed [1]=>361, [3]=>ABADIANO.
                // Step 1869 dump showed [2]=>HEADER ID.
                // This implies Data (Row 10+) might be shifted differently than Header (Row 9)?
                // Or I misread the indices.
                // Let's try to detect valid data in 1/2 or 3/4.
                if (!empty($row[1]) && !empty($row[3])) {
                    $empId = $row[1];
                    $lastName = $row[3];
                    // Shifted Mapping:
                    // 1->ID, 3->Last, 7->First, 9->MI, 16->Hired?, 41->Status?
                    // Let's support both or prioritize the one that matches the data dump we saw (Index 1).
                    // The verified data dump in Step 1842 used: 1, 3, 7, 9.
                    // The Header dump in Step 1869 used: 2, 4, 8, 10.
                    // Conclusion: Data is SHIFTED LEFT by 1 compared to Header row.
                    // This happens if Column A is empty/ignored in data row but present in header?
                    
                    
                    $firstName = $row[7] ?? '';
                    $middleName = $row[9] ?? null;
                    $deptName = $row[12] ?? 'Unassigned'; // 13-1
                    $position = $row[14] ?? 'Staff'; // 15-1
                    $rawBirth = $row[16] ?? null; // 17-1
                    $rawGender = $row[20] ?? 'Male'; // 21-1
                    $civilStatus = $row[22] ?? 'Single'; // 23-1
                    $rawHired = $row[24] ?? null; // 25-1
                    $sss = $row[26] ?? null; // 27-1
                    $pagibig = $row[29] ?? null; // 30-1
                    $philhealth = $row[31] ?? null; // 32-1
                    $tin = $row[33] ?? null; // 34-1
                    $status = $row[41] ?? 'Regular'; // Corrected index for Regular/Probationary status
                } else {
                    // Default precise map if indices match headers
                    $firstName = $row[8] ?? '';
                    $middleName = $row[10] ?? null;
                    $deptName = $row[13] ?? 'Unassigned';
                    $position = $row[15] ?? 'Staff';
                    $rawBirth = $row[17] ?? null;
                    $rawGender = $row[21] ?? 'Male';
                    $civilStatus = $row[23] ?? 'Single';
                    $rawHired = $row[25] ?? null; // Corrected to column 25 (Date Hired)
                    $sss = $row[27] ?? null;
                    $pagibig = $row[30] ?? null;
                    $philhealth = $row[32] ?? null;
                    $tin = $row[34] ?? null;
                    $status = $row[42] ?? 'Regular';
                }
            } else {
                 // Matched Header Index map
                 $firstName = $row[8] ?? '';
                 $middleName = $row[10] ?? null;
                 $deptName = $row[13] ?? 'Unassigned';
                 $position = $row[15] ?? 'Staff';
                 $rawBirth = $row[17] ?? null;
                 $rawGender = $row[21] ?? 'Male';
                 $civilStatus = $row[23] ?? 'Single';
                 $rawHired = $row[25] ?? null; // Corrected to column 25
                 $sss = $row[27] ?? null;
                 $pagibig = $row[30] ?? null;
                 $philhealth = $row[32] ?? null;
                 $tin = $row[34] ?? null;
                 $status = $row[42] ?? 'Regular';
            }
            
            if (!$empId || !$lastName) continue;

            // Cleaning
            $status = stripos($status, 'Probation') !== false ? 'Probationary' : 'Regular';
            $dateHired = $this->extractDate($rawHired);
            // If Hired date seems invalid (default today), try picking specific column
            
            // Clean Position (remove "ACTIVE")
            $position = trim(str_ireplace(['ACTIVE', 'SEIn-charge'], ['Active', 'In-charge'], $position));
            
            // 1. Department
            $department = Department::firstOrCreate(['name' => $deptName]);

            // 2. Employee
            $employee = Employee::updateOrCreate(
                ['employee_id' => $empId],
                [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'middle_name' => $middleName,
                    'department_id' => $department->id,
                    'position' => $position,
                    'employment_status' => $status,
                    'date_hired' => $dateHired,
                ]
            );

            // 3. Details - Now with Government IDs and Gender
            $birthdate = $this->extractDate($rawBirth);
            if ($birthdate === now()->format('Y-m-d')) $birthdate = '1900-01-01'; // Default if fail
            
            // Clean Gender (normalize to Male/Female)
            $gender = 'Male'; // Default
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
                Log::error('Row error: ' . $e->getMessage());
                continue;
            }
        }
    }

    private function extractDate($value)
    {
        if (!$value) return now()->format('Y-m-d');
        
        // Extract numbers at start if mixed (e.g. "37449Transfer")
        if (preg_match('/^(\d+)/', $value, $matches)) {
            $num = $matches[1];
            // Excel date check (roughly > 10000 and < 90000)
            if ($num > 10000 && $num < 90000) {
                 return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($num)->format('Y-m-d');
            }
        }
        
        // Try parsing string
        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return now()->format('Y-m-d');
        }
    }
}
