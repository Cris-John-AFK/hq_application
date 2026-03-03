<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = ['name'];

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * Remove departments that have no active employees.
     */
    public static function cleanup()
    {
        // Delete departments that have zero employees
        self::whereDoesntHave('employees')->delete();
    }
}
