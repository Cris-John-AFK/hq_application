<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'last_name',
        'first_name',
        'middle_name',
        'department_id',
        'position',
        'employment_status',
        'date_hired',
        'email',
        'avatar'
    ];

    // Relationships
    public function details()
    {
        return $this->hasOne(EmployeeDetail::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
    
    // Accessors
    public function getNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
