<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'birthdate',
        'place_of_birth',
        'gender',
        'civil_status',
        'sss_number',
        'philhealth_number',
        'pagibig_number',
        'tin_number',
    ];

    protected $hidden = [
        // 'birthdate', // Un-hiding so it shows up on the admin forms
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
