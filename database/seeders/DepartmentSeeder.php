<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    public function run()
    {
        $departments = [
            'Production',
            'Quality Control',
            'Embroidery',
            'Packing',
            'Warehouse',
            'Cutting',
            'PPIC',
            'PRD',
            'Sample Line',
            'Shipping',
            'Maintenance & Engineering',
            'HR & Admin',
            'Accounting'
        ];

        foreach ($departments as $name) {
            Department::firstOrCreate(['name' => $name]);
        }
    }
}
