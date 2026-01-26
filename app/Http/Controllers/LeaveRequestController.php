<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeaveRequest;
use Illuminate\Support\Facades\Auth;

class LeaveRequestController extends Controller
{
    protected $leavePatternService;
    protected $complianceService;
    protected $impactService;
    protected $creditForecastingService;

    public function __construct(
        \App\Services\LeavePatternService $leavePatternService,
        \App\Services\ComplianceService $complianceService,
        \App\Services\ImpactService $impactService,
        \App\Services\CreditForecastingService $creditForecastingService
    ) {
        $this->leavePatternService = $leavePatternService;
        $this->complianceService = $complianceService;
        $this->impactService = $impactService;
        $this->creditForecastingService = $creditForecastingService;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $query = LeaveRequest::with('user:id,name,department,avatar,employment_status,leave_credits');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('id_number', 'ilike', "%{$search}%");
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

        $results = $query->latest()->paginate(10);
        
        // Attach patterns/flags for Admin view
        if ($user->role === 'admin') {
            $results->getCollection()->transform(function ($req) {
                 // Lightweight flag check
                 $flags = $this->leavePatternService->detectPatterns($req->user);
                 $req->flags = $flags;
                 return $req;
            });
        }

        return response()->json($results);
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
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120' // Max 2MB
        ]);

        $user = Auth::user();
        
        // Compliance Check (Before Saving)
        $compliance = $this->complianceService->validateRule($user, $validated['leave_type'], $validated['days_taken']);
        if (!$compliance['passed']) {
            return response()->json(['message' => 'Compliance Warning: ' . $compliance['message']], 422);
        }

        $fromDate = $validated['from_date'];
        $toDate = $validated['to_date'] ?? $validated['from_date'];

        // Check for overlaps
        $overlap = LeaveRequest::where('user_id', $user->id)
            ->whereIn('status', ['Pending', 'Approved'])
            ->where(function ($query) use ($fromDate, $toDate) {
                $query->where(function ($q) use ($fromDate, $toDate) {
                    $q->whereBetween('from_date', [$fromDate, $toDate])
                      ->orWhereBetween('to_date', [$fromDate, $toDate]);
                })->orWhere(function ($q) use ($fromDate, $toDate) {
                    $q->where('from_date', '<=', $fromDate)
                      ->where('to_date', '>=', $toDate);
                });
            })->first();

        if ($overlap) {
            $existingFrom = $overlap->from_date->format('M d, Y');
            $existingTo = $overlap->to_date ? $overlap->to_date->format('M d, Y') : $existingFrom;
            return response()->json([
                'message' => "Overlap detected: You already have a {$overlap->status} request for {$existingFrom}" . ($overlap->to_date ? " to {$existingTo}" : "") . "."
            ], 422);
        }

        // Handle File Upload
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = time() . '_' . $file->getClientOriginalName();
            // Store in 'public/attachments' so it's accessible via storage link
            $path = $file->storeAs('attachments', $filename, 'public');
            $attachmentPath = '/storage/' . $path;
        }

        $leaveRequest = $user->leaveRequests()->create(array_merge(
            $validated,
            ['attachment_path' => $attachmentPath]
        ));

        // Return the fresh model with DB defaults (like status = 'Pending')
        return response()->json($leaveRequest->fresh(), 201);
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
            'admin_remarks' => 'nullable|string',
            'justification' => 'required_if:status,Rejected|string|nullable' // Mandatory for rejection at least
        ]);

        $oldStatus = $leaveRequest->status;
        
        // Log the action if status is changing
        if (isset($validated['status']) && $validated['status'] !== $oldStatus) {
            \App\Models\LeaveActionLog::create([
                'leave_request_id' => $leaveRequest->id,
                'user_id' => Auth::id(), // Admin
                'action' => $validated['status'],
                'justification' => $validated['justification'] ?? $validated['admin_remarks'] ?? 'Status updated',
                'snapshot_data' => [
                     'old_status' => $oldStatus,
                     'credits_before' => $leaveRequest->user->leave_credits,
                     'impact' => $this->impactService->checkImpact($leaveRequest->user, $leaveRequest->from_date, $leaveRequest->to_date)
                ]
            ]);
        }

        $leaveRequest->update($validated);

        // Deduct credits when status changes to Approved
        if (isset($validated['status']) && $validated['status'] === 'Approved' && $oldStatus !== 'Approved') {
            $user = $leaveRequest->user;
            $daysToDeduct = $leaveRequest->days_taken;
            $user->decrement('leave_credits', $daysToDeduct);
        }
        
        // Restore credits if status changes from Approved to something else
        if (isset($validated['status']) && $validated['status'] !== 'Approved' && $oldStatus === 'Approved') {
            $user = $leaveRequest->user;
            $user->increment('leave_credits', $leaveRequest->days_taken);
        }

        return response()->json($leaveRequest);
    }
    
    // New Endpoints for Decision Support
    
    public function getAnalysis($id)
    {
        // Admin only
        if (Auth::user()->role !== 'admin') return response()->json(['message' => 'Unauthorized'], 403);
        
        $leaveRequest = LeaveRequest::with('user')->findOrFail($id);
        $user = $leaveRequest->user;
        
        $patterns = $this->leavePatternService->detectPatterns($user);
        $impact = $this->impactService->checkImpact($user, $leaveRequest->from_date, $leaveRequest->to_date);
        $compliance = $this->complianceService->validateRule($user, $leaveRequest->leave_type, $leaveRequest->days_taken);
        $forecast = $this->creditForecastingService->forecast($user);
        
        return response()->json([
            'patterns' => $patterns,
            'impact' => $impact,
            'compliance' => $compliance,
            'forecast' => $forecast
        ]);
    }

    public function getUserForecast($userId)
    {
        if (Auth::user()->role !== 'admin' && Auth::id() != $userId) return response()->json(['message' => 'Unauthorized'], 403);
        
        $user = User::findOrFail($userId);
        return response()->json($this->creditForecastingService->forecast($user));
    }
    
    // Existing helper methods ...

    // For tracing/reporting - get user history + credits
    public function userHistory($userId)
    {
        if (Auth::user()->role !== 'admin' && Auth::id() != $userId) {
             return response()->json(['message' => 'Unauthorized'], 403);
        }

        $history = LeaveRequest::where('user_id', $userId)->latest()->get();
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

        $query = LeaveRequest::with('user:id,name,id_number,leave_credits,department,position');

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

    public function calendarEvents(Request $request)
    {
        // 1. Fetch Approved Leaves
        $leaveQuery = LeaveRequest::with('user:id,name,avatar,department,position,id_number')
            ->where('status', 'Approved');

        if ($request->filled('month')) {
            $leaveQuery->whereMonth('from_date', $request->month);
        }

        if ($request->filled('year')) {
            $leaveQuery->whereYear('from_date', $request->year);
        }

        $leaves = $leaveQuery->get()->map(function ($leave) {
            return [
                'id' => 'leave_' . $leave->id,
                'type' => 'leave',
                'user_name' => $leave->user->name,
                'user_id_number' => $leave->user->id_number,
                'user_department' => $leave->user->department,
                'user_position' => $leave->user->position,
                'avatar' => $leave->user->avatar_url,
                'leave_type' => $leave->leave_type,
                'from_date' => $leave->from_date->format('Y-m-d'),
                'to_date' => $leave->to_date ? $leave->to_date->format('Y-m-d') : $leave->from_date->format('Y-m-d'),
                'request_type' => $leave->request_type,
                'days_taken' => $leave->days_taken,
                'title' => "{$leave->user->name} on {$leave->leave_type}",
            ];
        });

        // 2. Fetch Custom Calendar Events
        $eventQuery = \App\Models\CalendarEvent::query();
        
        if ($request->filled('month')) {
            $eventQuery->whereMonth('start_date', $request->month);
        }

        if ($request->filled('year')) {
            $eventQuery->whereYear('start_date', $request->year);
        }

        $customEvents = $eventQuery->get()->map(function ($evt) {
            return [
                'id' => 'evt_' . $evt->id,
                'type' => $evt->type, // event, holiday, meeting
                'title' => $evt->title,
                'description' => $evt->description,
                'from_date' => $evt->start_date->format('Y-m-d'),
                'to_date' => $evt->end_date ? $evt->end_date->format('Y-m-d') : $evt->start_date->format('Y-m-d'),
                'is_read' => $evt->is_read,
            ];
        });

        // 3. Merge and Return
        return response()->json($leaves->concat($customEvents));
    }
}
