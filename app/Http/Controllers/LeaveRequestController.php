<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeaveRequest;
use Illuminate\Support\Facades\Auth;

class LeaveRequestController extends Controller
{

    public function index(Request $request)
    {
        $user = Auth::user();
        $query = LeaveRequest::with('user:id,name,department,avatar,employment_status,sil_credits');

        if ($user->role !== 'admin') {
            $query->where('user_id', $user->id);
        }

        // Filtering
        if ($request->filled('id')) {
            $query->where('id', $request->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('leave_type')) {
            $query->where('leave_type', $request->leave_type);
        }
        
        if ($request->filled('user_id') && $user->role === 'admin') {
            $query->where('user_id', $request->user_id);
        }
        
        if ($request->filled('month')) {
            $query->whereMonth('from_date', $request->month);
        }

        return response()->json($query->latest()->paginate(10));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date_filed' => 'nullable|date',
            'leave_type' => 'required|string',
            'request_type' => 'required|string',
            'from_date' => 'required|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
            'days_taken' => 'required|numeric',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'reason' => 'required|string',
        ]);

        $leaveRequest = Auth::user()->leaveRequests()->create($validated);

        return response()->json($leaveRequest, 201);
    }

    public function update(Request $request, LeaveRequest $leaveRequest)
    {
        // Only Admin can update status/paid/remarks
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'status' => 'sometimes|in:Pending,Approved,Rejected,Cancelled',
            'is_paid' => 'sometimes|boolean',
            'admin_remarks' => 'nullable|string'
        ]);

        $leaveRequest->update($validated);

        // Logic for deducting credits could go here later if automated

        return response()->json($leaveRequest);
    }
    
    // For tracing/reporting - get user history + credits
    public function userHistory($userId)
    {
        if (Auth::user()->role !== 'admin' && Auth::id() != $userId) {
             return response()->json(['message' => 'Unauthorized'], 403);
        }

        $history = LeaveRequest::where('user_id', $userId)->latest()->get();
        // Calculate usage summary here if needed
        
        return response()->json($history);
    }

    public function stats()
    {
        if (Auth::user()->role !== 'admin') {
             return response()->json(['message' => 'Unauthorized'], 403);
        }

        $pending = LeaveRequest::where('status', 'Pending')->count();
        $approved = LeaveRequest::where('status', 'Approved')->count();
        $rejected = LeaveRequest::where('status', 'Rejected')->count();
        $cancelled = LeaveRequest::where('status', 'Cancelled')->count();

        // For Manage Leaves - "Approved This Month"
        $approvedThisMonth = LeaveRequest::where('status', 'Approved')
            ->whereMonth('from_date', now()->month)
            ->whereYear('from_date', now()->year)
            ->count();
        
        // For Manage Leaves - "Scheduled Upcoming"
        $scheduled = LeaveRequest::where('status', 'Approved')
            ->where('from_date', '>', now())
            ->count();
        
        // For Dashboard "Recent"
        $recent = LeaveRequest::with('user:id,name,avatar')->latest()->take(5)->get();

        // On Leave Today
        $onLeaveToday = LeaveRequest::where('status', 'Approved')
            ->whereDate('from_date', '<=', now())
            ->whereDate('to_date', '>=', now())
            ->count();

        return response()->json([
            'pending' => $pending,
            'approved' => $approved,
            'rejected' => $rejected,
            'cancelled' => $cancelled,
            'approved_this_month' => $approvedThisMonth,
            'scheduled' => $scheduled,
            'total_all_time' => LeaveRequest::count(),
            'recent' => $recent,
            'on_leave_today' => $onLeaveToday
        ]);
    }
}
