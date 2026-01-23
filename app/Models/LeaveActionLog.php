<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveActionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'leave_request_id',
        'user_id',
        'action',
        'justification',
        'snapshot_data'
    ];

    protected $casts = [
        'snapshot_data' => 'array',
    ];

    public function leaveRequest()
    {
        return $this->belongsTo(LeaveRequest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class); // The Admin who performed the action
    }
}
