<?php

namespace App\Exports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\Schema;

class EmployeeExport implements FromCollection, WithHeadings, WithMapping
{
    private $leaveColumns = [];

    public function __construct()
    {
        $this->leaveColumns = array_filter(
            Schema::getColumnListing('employees'),
            fn($col) => str_ends_with($col, '_leave')
        );
    }

    public function collection()
    {
        return Employee::with(['department', 'details'])->where('is_archived', false)->get();
    }

    public function headings(): array
    {
        $headers = [
            'ID Number',
            'Last Name',
            'First Name',
            'Middle Name',
            'Department',
            'Position',
            'Employment Status',
            'Date Hired',
            'Email',
            'Working Hours',

            // Details
            'Gender',
            'Civil Status',
            'Place of Birth',
            'SSS Number',
            'PhilHealth Number',
            'Pag-IBIG Number',
            'TIN Number'
        ];

        foreach ($this->leaveColumns as $col) {
            $formatted = ucwords(str_replace('_', ' ', $col));
            $headers[] = $formatted;
        }

        return $headers;
    }

    public function map($employee): array
    {
        $row = [
            $employee->employee_id,
            $employee->last_name,
            $employee->first_name,
            $employee->middle_name,
            $employee->department ? $employee->department->name : '',
            $employee->position,
            $employee->employment_status,
            $employee->date_hired ? $employee->date_hired->format('m/d/Y') : '',
            $employee->email,
            $employee->working_hours,

            $employee->details ? $employee->details->gender : '',
            $employee->details ? $employee->details->civil_status : '',
            $employee->details ? $employee->details->place_of_birth : '',
            $employee->details ? $employee->details->sss_number : '',
            $employee->details ? $employee->details->philhealth_number : '',
            $employee->details ? $employee->details->pagibig_number : '',
            $employee->details ? $employee->details->tin_number : '',
        ];

        foreach ($this->leaveColumns as $col) {
            $row[] = (float) $employee->$col;
        }

        return $row;
    }
}
