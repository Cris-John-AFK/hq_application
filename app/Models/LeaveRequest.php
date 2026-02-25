<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    protected $fillable = [
        'user_id',
        'employee_id',
        'date_filed',
        'leave_type',
        'category',
        'request_type',
        'from_date',
        'to_date',
        'days_taken',
        'start_time',
        'end_time',
        'reason',
        'status',
        'is_paid',
        'days_paid',
        'admin_remarks',
        'justification',
        'attachment_path',
        'is_archived',
        'archived_at'
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'date_filed' => 'date',
        'from_date' => 'date',
        'to_date' => 'date',
        'days_taken' => 'decimal:2',
        'days_paid' => 'decimal:2',
        'is_archived' => 'boolean',
        'archived_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
