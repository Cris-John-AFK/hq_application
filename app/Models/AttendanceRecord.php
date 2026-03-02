<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceRecord extends Model
{
    protected $fillable = [
        'employee_id_number',
        'employee_name',
        'date',
        'time_in',
        'time_out',
        'hours_worked',
        'status',
        'department'
    ];
}
