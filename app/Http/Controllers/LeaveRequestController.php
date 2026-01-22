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
        $query = LeaveRequest::with('user:id,name,department,avatar,employment_status,leave_credits');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('employee_id', 'ilike', "%{$search}%");
            });
        }

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
            'days_paid' => 'nullable|numeric|min:0',
            'admin_remarks' => 'nullable|string'
        ]);

        $oldStatus = $leaveRequest->status;
        $leaveRequest->update($validated);

        // Deduct credits when status changes to Approved
        if (isset($validated['status']) && $validated['status'] === 'Approved' && $oldStatus !== 'Approved') {
            $user = $leaveRequest->user;
            $daysToDeduct = $leaveRequest->days_taken;
            
            // Deduct credits (allow negative balance if insufficient)
            $user->decrement('leave_credits', $daysToDeduct);
        }
        
        // Restore credits if status changes from Approved to something else
        if (isset($validated['status']) && $validated['status'] !== 'Approved' && $oldStatus === 'Approved') {
            $user = $leaveRequest->user;
            $user->increment('leave_credits', $leaveRequest->days_taken);
        }

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
        $recent = LeaveRequest::with('user:id,name,id_number,avatar')->latest()->take(20)->get();

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
    public function export(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $query = LeaveRequest::with('user:id,name,id_number,leave_credits');

        // Apply filters if provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('leave_type')) {
            $query->where('leave_type', $request->leave_type);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('id_number', 'ilike', "%{$search}%");
            });
        }

        $requests = $query->latest()->get();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=leave_report_" . now()->format('Y-m-d_His') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = [
            'Request ID', 'Employee ID', 'Employee Name', 'Department', 'Position', 
            'Leave Type', 'Request Type', 'From Date', 'To Date', 'Days Taken', 
            'Status', 'Is Paid', 'No. of Days Paid', 'Reason', 'Admin Remarks', 'Latest SIL Balance'
        ];

        $callback = function() use($requests, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($requests as $req) {
                fputcsv($file, [
                    $req->id,
                    $req->user->id_number ?? 'N/A',
                    $req->user->name,
                    $req->user->department ?? 'N/A',
                    $req->user->position ?? 'N/A',
                    $req->leave_type,
                    $req->request_type,
                    $req->from_date ? $req->from_date->format('Y-m-d') : '',
                    $req->to_date ? $req->to_date->format('Y-m-d') : ($req->from_date ? $req->from_date->format('Y-m-d') : ''),
                    $req->days_taken,
                    $req->status,
                    $req->is_paid ? 'YES' : 'NO',
                    $req->days_paid,
                    $req->reason,
                    $req->admin_remarks,
                    $req->user->leave_credits
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
