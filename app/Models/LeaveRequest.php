<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    protected $fillable = [
        'user_id',
        'date_filed',
        'leave_type',
        'request_type',
        'from_date',
        'to_date',
        'days_taken',
        'start_time',
        'end_time',
        'reason',
        'status',
        'is_paid',
        'admin_remarks'
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'date_filed' => 'date',
        'from_date' => 'date',
        'to_date' => 'date',
        'days_taken' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
