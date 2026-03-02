<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        session_write_close();
        $user = Auth::user();
        $query = LeaveRequest::with([
            'user:id,name,department,avatar,employment_status,leave_credits',
            'employee.department'
        ]);

        // Default: only show non-archived
        if ($request->boolean('archived', false)) {
            $query->where('is_archived', true);
        } else {
            $query->where('is_archived', false);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($uq) use ($search) {
                    $uq->where('name', 'ilike', "%{$search}%")
                        ->orWhere('id_number', 'ilike', "%{$search}%");
                })->orWhereHas('employee', function ($eq) use ($search) {
                    $eq->where('first_name', 'ilike', "%{$search}%")
                        ->orWhere('last_name', 'ilike', "%{$search}%")
                        ->orWhere('employee_id', 'ilike', "%{$search}%");
                });
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

        if ($request->filled('request_type')) {
            $query->where('request_type', $request->request_type);
        }

        if ($request->filled('department')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('department', $request->department);
            });
        }

        if ($request->filled('from_date')) {
            $query->where('from_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->where('from_date', '<=', $request->to_date);
        }

        if ($request->filled('user_id') && $user->role === 'admin') {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('year')) {
            $query->whereYear('from_date', $request->year);
        }

        if ($request->filled('month')) {
            $query->whereMonth('from_date', $request->month);
        }

        $results = $query->latest()->paginate(10);

        return response()->json($results);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'nullable|exists:employees,id',
            'date_filed' => 'nullable|date',
            'leave_type' => 'required|string',
            'category' => 'nullable|string',
            'request_type' => 'required|string',
            'from_date' => 'required|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
            'days_taken' => 'required|numeric',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'reason' => 'required|string',
            'is_paid' => 'nullable|boolean',
            'days_paid' => 'nullable|numeric',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120', // Max 5MB
            'additional_details' => 'nullable|array'
        ]);

        $currentUser = Auth::user();
        $targetUserId = $currentUser->id;
        $targetEmployeeId = $request->employee_id;
        $targetObject = $currentUser; // For compliance check
        $status = 'Pending';

        // If admin is filing for someone else
        if ($request->filled('employee_id') && $currentUser->role === 'admin') {
            $employee = \App\Models\Employee::find($request->employee_id);
            // Try to link to a regular user if they exist
            $userMatched = \App\Models\User::where('id_number', $employee->employee_id)->first();
            if ($userMatched) {
                $targetUserId = $userMatched->id;
                $targetObject = $userMatched;
            } else {
                $targetUserId = null;
                $targetObject = $employee;
            }
            $status = 'Approved'; // Admins don't need to approve their own manual filings
        }

        // Compliance Check
        $compliance = $this->complianceService->validateRule($targetObject, $validated['leave_type'], $validated['days_taken']);
        if (!$compliance['passed'] && $currentUser->role !== 'admin') {
            return response()->json(['message' => 'Compliance Warning: ' . $compliance['message']], 422);
        }

        $fromDate = $validated['from_date'];
        $toDate = $validated['to_date'] ?? $validated['from_date'];

        // Check for overlaps (using either user_id or employee_id)
        $overlapQuery = LeaveRequest::whereIn('status', ['Pending', 'Approved']);

        if ($targetUserId) {
            $overlapQuery->where('user_id', $targetUserId);
        } else {
            $overlapQuery->where('employee_id', $targetEmployeeId);
        }

        $overlap = $overlapQuery->where(function ($query) use ($fromDate, $toDate) {
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
            $msgPart = $targetUserId ? "You already have" : "This employee already has";

            return response()->json([
                'message' => "Overlap detected: {$msgPart} a {$overlap->status} request for {$existingFrom}" . ($overlap->to_date ? " to {$existingTo}" : '') . '.',
            ], 422);
        }

        // Handle File Upload
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('attachments', $filename, 'public');
            $attachmentPath = '/storage/' . $path;
        }

        $leaveRequest = LeaveRequest::create(array_merge(
            $validated,
            [
                'user_id' => $targetUserId,
                'employee_id' => $targetEmployeeId,
                'status' => $status,
                'attachment_path' => $attachmentPath,
                'additional_details' => $validated['additional_details'] ?? []
            ]
        ));

        // Trigger Notification for Admin (Notify other admins if needed, or notify the targeted user)
        $targetName = $targetObject->name ?? ($targetObject->first_name . ' ' . $targetObject->last_name);
        $admins = \App\Models\User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            if ($admin->id === $currentUser->id && $targetUserId !== $currentUser->id)
                continue;
            \App\Models\Notification::create([
                'user_id' => $admin->id,
                'title' => 'New Leave Request Filed',
                'message' => "{$targetName} has a new " . ($leaveRequest->category ?? '') . " {$leaveRequest->leave_type} request for {$leaveRequest->days_taken} day(s).",
                'type' => 'info',
                'link' => "/manage-leaves?search={$targetEmployeeId}",
            ]);
        }

        if ($targetUserId && $targetUserId !== $currentUser->id) {
            \App\Models\Notification::create([
                'user_id' => $targetUserId,
                'title' => 'Leave Request Filed for You',
                'message' => "An administrator has filed a {$leaveRequest->leave_type} request on your behalf.",
                'type' => 'info',
                'link' => "/leave-management",
            ]);
        }

        // Audit Log
        \App\Utils\AuditLogger::log('Leaves', 'Created', "Submitted a new {$leaveRequest->leave_type} request for {$leaveRequest->days_taken} day(s).", null, $leaveRequest->toArray());

        // Return the fresh model with DB defaults (like status = 'Pending')
        return response()->json($leaveRequest->fresh(), 201);
    }

    public function update(Request $request, LeaveRequest $leaveRequest)
    {
        $user = Auth::user();
        $isAdmin = $user->role === 'admin';
        $isOwner = $leaveRequest->user_id === $user->id;

        if (!$isAdmin && !$isOwner) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $oldStatus = $leaveRequest->status;
        $oldIsPaid = $leaveRequest->is_paid;

        // Validation for Admin
        if ($isAdmin) {
            $validated = $request->validate([
                'status' => 'sometimes|in:Pending,Approved,Rejected,Cancelled',
                'is_paid' => 'sometimes|boolean',
                'days_paid' => 'nullable|numeric|min:0',
                'admin_remarks' => 'nullable|string',
                'justification' => 'sometimes|string|nullable',
                'additional_details' => 'nullable|array'
            ]);
        } else {
            // Validation for User (Owner)
            // Users can only cancel or edit if Pending
            if ($request->has('status') && $request->status === 'Cancelled') {
                $validated = ['status' => 'Cancelled'];
            } else {
                if ($oldStatus !== 'Pending') {
                    return response()->json(['message' => 'Only pending requests can be edited.'], 422);
                }
                $validated = $request->validate([
                    'leave_type' => 'sometimes|required|string',
                    'request_type' => 'sometimes|required|string',
                    'from_date' => 'sometimes|required|date',
                    'to_date' => 'nullable|date|after_or_equal:from_date',
                    'days_taken' => 'sometimes|required|numeric',
                    'reason' => 'sometimes|required|string',
                    'additional_details' => 'nullable|array'
                ]);
            }
        }

        return \Illuminate\Support\Facades\DB::transaction(function () use ($leaveRequest, $validated, $oldStatus, $oldIsPaid, $isAdmin) {
            $subject = $leaveRequest->user ?? $leaveRequest->employee;
            $subjectName = $leaveRequest->user->name ?? ($leaveRequest->employee->first_name . ' ' . $leaveRequest->employee->last_name);

            // Log the action if status is changing
            if (isset($validated['status']) && $validated['status'] !== $oldStatus) {
                \App\Models\LeaveActionLog::create([
                    'leave_request_id' => $leaveRequest->id,
                    'user_id' => Auth::id(),
                    'action' => $validated['status'],
                    'justification' => $validated['justification'] ?? $validated['admin_remarks'] ?? ($isAdmin ? 'Status updated by Admin' : 'Cancelled by Employee'),
                    'snapshot_data' => [
                        'old_status' => $oldStatus,
                        'credits_before' => $subject->leave_credits ?? $subject->sil_credits ?? 0,
                        'impact' => $this->impactService->checkImpact($subject, $leaveRequest->from_date, $leaveRequest->to_date),
                    ],
                ]);
            }

            $leaveRequest->update($validated);
            $leaveRequest->refresh(); // Get updated attributes

            // Notifications
            if (isset($validated['status']) && $validated['status'] !== $oldStatus) {
                if ($isAdmin) {
                    // Notify Employee IF they are a real user
                    if ($leaveRequest->user_id) {
                        \App\Models\Notification::create([
                            'user_id' => $leaveRequest->user_id,
                            'title' => 'Leave Request Update',
                            'message' => "Your {$leaveRequest->leave_type} request for " . $leaveRequest->from_date->format('M d') . " has been marked as **{$validated['status']}**.",
                            'type' => $validated['status'] === 'Approved' ? 'success' : ($validated['status'] === 'Rejected' ? 'urgent' : 'warning'),
                            'link' => '/leave-requests',
                        ]);
                    }
                } elseif ($validated['status'] === 'Cancelled') {
                    // Notify Admins
                    $admins = \App\Models\User::where('role', 'admin')->get();
                    foreach ($admins as $admin) {
                        \App\Models\Notification::create([
                            'user_id' => $admin->id,
                            'title' => 'Leave Request Cancelled',
                            'message' => "{$subjectName} has cancelled their {$leaveRequest->leave_type} request.",
                            'type' => 'warning',
                            'link' => '/manage-leaves',
                        ]);
                    }
                }
            }

            // CREDIT ADJUSTMENT LOGIC
            $newStatus = $leaveRequest->status;
            $newIsPaid = $leaveRequest->is_paid;
            $days = $leaveRequest->days_taken;

            if ($subject) {
                $creditField = ($subject instanceof \App\Models\User) ? 'leave_credits' : 'leave_credits'; // Both use leave_credits now after our migrations

                // Scenario A: Transitioning to Approved
                if ($newStatus === 'Approved' && $oldStatus !== 'Approved') {
                    if ($newIsPaid) {
                        $subject->decrement($creditField, (float) $days);
                    }
                }
                // Scenario B: Transitioning out of Approved
                elseif ($newStatus !== 'Approved' && $oldStatus === 'Approved') {
                    if ($oldIsPaid) {
                        $subject->increment($creditField, (float) $days);
                    }
                }
                // Scenario C: Already Approved, but toggling Pay Status
                elseif ($newStatus === 'Approved' && $oldStatus === 'Approved') {
                    if (!$oldIsPaid && $newIsPaid) {
                        $subject->decrement($creditField, (float) $days);
                    } elseif ($oldIsPaid && !$newIsPaid) {
                        $subject->increment($creditField, (float) $days);
                    }
                }
            }

            // AUDIT LOG
            $changeDesc = "Updated leave request #{$leaveRequest->id}.";
            $changes = [];
            if ($oldStatus !== $newStatus) {
                $changes[] = "Status: [{$oldStatus}] to [{$newStatus}]";
            }
            if ($oldIsPaid !== $newIsPaid) {
                $changes[] = 'Pay Status: [' . ($oldIsPaid ? 'Paid' : 'Unpaid') . '] to [' . ($newIsPaid ? 'Paid' : 'Unpaid') . ']';
            }
            if (isset($validated['additional_details']) && $validated['additional_details'] !== $leaveRequest->additional_details) {
                $changes[] = 'Additional Details updated';
            }

            if (!empty($changes)) {
                $changeDesc .= ' Changed ' . implode(', ', $changes) . '.';
            }

            $finalRemarks = $validated['justification'] ?? '';
            if ($finalRemarks) {
                $changeDesc .= " Remarks: \"{$finalRemarks}\"";
            }

            \App\Utils\AuditLogger::log(
                'Leaves',
                'Updated',
                $changeDesc,
                ['status' => $oldStatus, 'is_paid' => $oldIsPaid, 'additional_details' => $leaveRequest->additional_details],
                ['status' => $newStatus, 'is_paid' => $newIsPaid, 'additional_details' => $validated['additional_details'] ?? $leaveRequest->additional_details]
            );

            return response()->json($leaveRequest);
        });
    }

    // New Endpoints for Decision Support

    public function getAnalysis($id)
    {
        // Admin only
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $leaveRequest = LeaveRequest::with(['user', 'employee.department', 'employee.details'])->findOrFail($id);
        $subject = $leaveRequest->user ?? $leaveRequest->employee;

        if (!$subject) {
            return response()->json(['message' => 'No subject found for this request'], 404);
        }

        $patterns = $this->leavePatternService->detectPatterns($subject);
        $impact = $this->impactService->checkImpact($subject, $leaveRequest->from_date, $leaveRequest->to_date);
        $compliance = $this->complianceService->validateRule($subject, $leaveRequest->leave_type, (float) $leaveRequest->days_taken);
        $forecast = $this->creditForecastingService->forecast($subject);

        return response()->json([
            'patterns' => $patterns,
            'impact' => $impact,
            'compliance' => $compliance,
            'forecast' => $forecast,
        ]);
    }

    public function getUserForecast($userId)
    {
        if (Auth::user()->role !== 'admin' && Auth::id() != $userId) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user = User::findOrFail($userId);

        return response()->json($this->creditForecastingService->forecast($user));
    }

    // Leave Analytics — filtered aggregation for the dashboard analytics panel
    public function analyticsData(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $year = $request->get('year', now()->year);
        $month = $request->get('month', null);   // null = all months
        $status = $request->get('status', null);
        $type = $request->get('leave_type', null);

        $base = LeaveRequest::where('is_archived', '!=', true)
            ->whereYear('from_date', $year);

        if ($month)
            $base->whereMonth('from_date', $month);
        if ($status)
            $base->where('status', $status);
        if ($type)
            $base->where('leave_type', $type);

        $total = (clone $base)->count();
        $approved = (clone $base)->where('status', 'Approved')->count();
        $pending = (clone $base)->where('status', 'Pending')->count();
        $rejected = (clone $base)->where('status', 'Rejected')->count();
        $cancelled = (clone $base)->where('status', 'Cancelled')->count();
        $daysTaken = (clone $base)->sum('days_taken');
        $avgDays = $total > 0 ? round($daysTaken / $total, 1) : 0;

        $approvedPaid = (clone $base)->where('status', 'Approved')->where('is_paid', true)->count();
        $approvedUnpaid = (clone $base)->where('status', 'Approved')->where('is_paid', false)->count();

        // By Leave Type
        $byType = (clone $base)
            ->select('leave_type as name', \DB::raw('count(*) as count'))
            ->groupBy('leave_type')
            ->orderByDesc(\DB::raw('count(*)'))
            ->get();

        // By Department (employee or user)
        $byDept = \DB::table('leave_requests')
            ->leftJoin('employees', 'leave_requests.employee_id', '=', 'employees.id')
            ->leftJoin('departments as ed', 'employees.department_id', '=', 'ed.id')
            ->leftJoin('users', 'leave_requests.user_id', '=', 'users.id')
            ->leftJoin('departments as ud', 'users.department_id', '=', 'ud.id')
            ->where('leave_requests.is_archived', '!=', true)
            ->whereYear('leave_requests.from_date', $year)
            ->when($month, fn($q) => $q->whereMonth('leave_requests.from_date', $month))
            ->when($status, fn($q) => $q->where('leave_requests.status', $status))
            ->when($type, fn($q) => $q->where('leave_requests.leave_type', $type))
            ->select(\DB::raw("COALESCE(ed.name, ud.name, users.department, 'General') as name"), \DB::raw('count(*) as count'))
            ->groupBy(\DB::raw("COALESCE(ed.name, ud.name, users.department, 'General')"))
            ->orderByDesc(\DB::raw('count(*)'))
            ->get();

        // Monthly Trend (for bar chart)
        $monthlyTrend = \DB::table('leave_requests')
            ->select(
                \DB::raw('EXTRACT(MONTH FROM from_date)::int as month'),
                \DB::raw("SUM(CASE WHEN status = 'Approved'  THEN 1 ELSE 0 END) as approved"),
                \DB::raw("SUM(CASE WHEN status = 'Pending'   THEN 1 ELSE 0 END) as pending"),
                \DB::raw("SUM(CASE WHEN status = 'Rejected'  THEN 1 ELSE 0 END) as rejected"),
                \DB::raw("SUM(CASE WHEN status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled"),
                \DB::raw('count(*) as total')
            )
            ->where('is_archived', '!=', true)
            ->whereYear('from_date', $year)
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($type, fn($q) => $q->where('leave_type', $type))
            ->groupBy(\DB::raw('EXTRACT(MONTH FROM from_date)'))
            ->orderBy('month')
            ->get();

        return response()->json([
            'total' => $total,
            'approved' => $approved,
            'pending' => $pending,
            'rejected' => $rejected,
            'cancelled' => $cancelled,
            'days_taken' => $daysTaken,
            'avg_days' => $avgDays,
            'approved_paid' => $approvedPaid,
            'approved_unpaid' => $approvedUnpaid,
            'by_type' => $byType,
            'by_department' => $byDept,
            'monthly_trend' => $monthlyTrend,
        ]);
    }

    public function analyticsExport(Request $request)
    {
        if (Auth::user()->role !== 'admin' && Auth::user()->role !== 'hr') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $filters = $request->only(['year', 'month', 'status', 'leave_type', 'department']);

        // Audit Log
        \App\Utils\AuditLogger::log('Leaves', 'Exported', "Exported leave analytics Excel report for " . ($filters['year'] ?? now()->year) . ".");

        return (new \App\Exports\LeaveAnalyticsExport($filters))->download('Leave_Analytics_Report_' . now()->format('Y_m_d') . '.xlsx');
    }

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
        session_write_close();
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $pending = LeaveRequest::where('status', 'Pending')->where('is_archived', false)->count();
        $approved = LeaveRequest::where('status', 'Approved')->where('is_archived', false)->count();
        $rejected = LeaveRequest::where('status', 'Rejected')->where('is_archived', false)->count();
        $cancelled = LeaveRequest::where('status', 'Cancelled')->where('is_archived', false)->count();

        // For Manage Leaves - "Approved This Month"
        $approvedThisMonth = LeaveRequest::where('status', 'Approved')
            ->where('is_archived', false)
            ->whereMonth('from_date', now()->month)
            ->whereYear('from_date', now()->year)
            ->count();

        // For Manage Leaves - "Scheduled Upcoming"
        $scheduled = LeaveRequest::where('status', 'Approved')
            ->where('is_archived', false)
            ->where('from_date', '>', now())
            ->count();

        // For Dashboard "Recent"
        $recent = LeaveRequest::with(['user:id,name,id_number,avatar', 'employee:id,first_name,last_name,avatar,employee_id'])
            ->where('is_archived', false)
            ->latest()
            ->take(20)
            ->get();

        // On Leave Today
        $onLeaveToday = LeaveRequest::where('status', 'Approved')
            ->where('is_archived', false)
            ->whereDate('from_date', '<=', now())
            ->whereDate('to_date', '>=', now())
            ->count();

        // Paid vs Unpaid (Approved)
        $approvedPaid = LeaveRequest::where('status', 'Approved')
            ->where('is_archived', '!=', true)
            ->where('is_paid', true)
            ->count();
        $approvedUnpaid = LeaveRequest::where('status', 'Approved')
            ->where('is_archived', '!=', true)
            ->where('is_paid', false)
            ->count();

        // By Leave Type
        $byType = LeaveRequest::select('leave_type as name', \DB::raw('count(*) as count'))
            ->where('is_archived', '!=', true)
            ->groupBy('leave_type')
            ->orderBy(\DB::raw('count(*)'), 'desc')
            ->take(5)
            ->get();

        // By Department
        $byDept = \DB::table('leave_requests')
            ->leftJoin('employees', 'leave_requests.employee_id', '=', 'employees.id')
            ->leftJoin('departments as ed', 'employees.department_id', '=', 'ed.id')
            ->leftJoin('users', 'leave_requests.user_id', '=', 'users.id')
            ->leftJoin('departments as ud', 'users.department_id', '=', 'ud.id')
            ->where('leave_requests.is_archived', '!=', true)
            ->select(\DB::raw('COALESCE(ed.name, ud.name, users.department, \'General\') as name'), \DB::raw('count(*) as count'))
            ->groupBy(\DB::raw('COALESCE(ed.name, ud.name, users.department, \'General\')'))
            ->orderBy(\DB::raw('count(*)'), 'desc')
            ->take(5)
            ->get();

        return response()->json([
            'pending' => $pending,
            'approved' => $approved,
            'rejected' => $rejected,
            'cancelled' => $cancelled,
            'approved_this_month' => $approvedThisMonth,
            'scheduled' => $scheduled,
            'total_all_time' => LeaveRequest::count(),
            'total_employees' => \App\Models\Employee::count(),
            'recent' => $recent,
            'on_leave_today' => $onLeaveToday,
            'by_type' => $byType,
            'by_department' => $byDept,
            'approved_paid' => $approvedPaid,
            'approved_unpaid' => $approvedUnpaid,
        ]);
    }

    public function export(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $query = LeaveRequest::with(['user', 'employee.department']);

        // Apply filters if provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('leave_type')) {
            $query->where('leave_type', $request->leave_type);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('from_date')) {
            $query->whereDate('from_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('to_date', '<=', $request->to_date);
        }
        // Allow overriding is_archived if explicitly requested (e.g. from Archive Registry)
        if ($request->has('is_archived')) {
            $query->where('is_archived', filter_var($request->is_archived, FILTER_VALIDATE_BOOLEAN));
        } else {
            $query->where('is_archived', false);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($uq) use ($search) {
                    $uq->where('name', 'ilike', "%{$search}%")
                        ->orWhere('id_number', 'ilike', "%{$search}%");
                })->orWhereHas('employee', function ($eq) use ($search) {
                    $eq->where('first_name', 'ilike', "%{$search}%")
                        ->orWhere('last_name', 'ilike', "%{$search}%")
                        ->orWhere('employee_id', 'ilike', "%{$search}%");
                });
            });
        }

        $requests = $query->latest()->get();

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=leave_report_' . now()->format('Y-m-d_His') . '.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $columns = [
            'Request ID',
            'Employee ID',
            'Employee Name',
            'Department',
            'Position',
            'Leave Type',
            'Request Type',
            'From Date',
            'To Date',
            'Days Taken',
            'Status',
            'Is Paid',
            'No. of Days Paid',
            'Reason',
            'Attendance Category',
        ];

        $callback = function () use ($requests, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($requests as $req) {
                $name = $req->user?->name ?? ($req->employee ? $req->employee->name : 'Unknown');
                $idNumber = $req->user?->id_number ?? ($req->employee?->employee_id ?? 'N/A');
                $dept = $req->user?->department ?? ($req->employee?->department?->name ?? 'N/A');
                $position = $req->user?->position ?? ($req->employee?->position ?? 'N/A');

                fputcsv($file, [
                    $req->id,
                    $idNumber,
                    $name,
                    $dept,
                    $position,
                    $req->leave_type,
                    $req->request_type,
                    $req->from_date ? $req->from_date->format('Y-m-d') : '',
                    $req->to_date ? $req->to_date->format('Y-m-d') : ($req->from_date ? $req->from_date->format('Y-m-d') : ''),
                    $req->days_taken,
                    $req->status,
                    $req->is_paid ? 'YES' : 'NO',
                    $req->days_paid,
                    $req->reason,
                    $req->category ?? '',
                ]);
            }

            fclose($file);
        };

        // Audit Log
        \App\Utils\AuditLogger::log('Leaves', 'Exported', "Exported leave report CSV file.");

        return response()->stream($callback, 200, $headers);
    }

    public function calendarEvents(Request $request)
    {
        // 1. Fetch Approved, Non-Archived Leaves
        $leaveQuery = LeaveRequest::with(['user:id,name,avatar,department,position,id_number', 'employee.department'])
            ->where('status', 'Approved')
            ->where('is_archived', '!=', true);

        if ($request->filled('month')) {
            $leaveQuery->whereMonth('from_date', $request->month);
        }

        if ($request->filled('year')) {
            $leaveQuery->whereYear('from_date', $request->year);
        }

        $leaves = $leaveQuery->get()->map(function ($leave) {
            $subject = $leave->user ?? $leave->employee;
            if (!$subject)
                return null;

            $name = $leave->user ? $leave->user->name : ($leave->employee ? $leave->employee->name : 'Unknown');
            $idNumber = $leave->user ? $leave->user->id_number : ($leave->employee ? $leave->employee->employee_id : 'N/A');
            $dept = $leave->user ? $leave->user->department : ($leave->employee && $leave->employee->department ? $leave->employee->department->name : 'N/A');
            $pos = $leave->user ? $leave->user->position : ($leave->employee ? $leave->employee->position : 'N/A');
            $avatar = $leave->user ? $leave->user->avatar_url : ($leave->employee && $leave->employee->avatar ? \Illuminate\Support\Facades\Storage::disk('public')->url($leave->employee->avatar) : null);

            return [
                'id' => 'leave_' . $leave->id,
                'type' => 'leave',
                'status' => $leave->status,
                'user_name' => $name,
                'user_id_number' => $idNumber,
                'user_department' => $dept,
                'user_position' => $pos,
                'avatar' => $avatar,
                'leave_type' => $leave->leave_type,
                'from_date' => $leave->from_date->format('Y-m-d'),
                'to_date' => $leave->to_date ? $leave->to_date->format('Y-m-d') : $leave->from_date->format('Y-m-d'),
                'request_type' => $leave->request_type,
                'days_taken' => $leave->days_taken,
                'title' => "{$name} on {$leave->leave_type}",
            ];
        })->filter();

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
                'from_date' => \Carbon\Carbon::parse($evt->start_date)->format('Y-m-d'),
                'to_date' => $evt->end_date ? \Carbon\Carbon::parse($evt->end_date)->format('Y-m-d') : \Carbon\Carbon::parse($evt->start_date)->format('Y-m-d'),
                'is_read' => $evt->is_read,
            ];
        });

        // 3. Merge and Return
        return response()->json($leaves->concat($customEvents));
    }

    public function archive(string $id)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $leave = LeaveRequest::with(['user', 'employee'])->findOrFail($id);
        $leave->update([
            'is_archived' => true,
            'archived_at' => now(),
        ]);

        $subjectName = $leave->user->name ?? ($leave->employee ? $leave->employee->first_name . ' ' . $leave->employee->last_name : 'Unknown');
        \App\Utils\AuditLogger::log('Leaves', 'Archived', "Archived leave request #{$leave->id} for {$subjectName}.");

        return response()->json(['message' => 'Leave request archived successfully']);
    }

    public function unarchive(string $id)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $leave = LeaveRequest::with(['user', 'employee'])->findOrFail($id);
        $leave->update([
            'is_archived' => false,
            'archived_at' => null,
        ]);

        $subjectName = $leave->user->name ?? ($leave->employee ? $leave->employee->first_name . ' ' . $leave->employee->last_name : 'Unknown');
        \App\Utils\AuditLogger::log('Leaves', 'Restored', "Restored leave request #{$leave->id} for {$subjectName} from archive.");

        return response()->json(['message' => 'Leave request restored from archive']);
    }

    public function bulkArchive(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'before_date' => 'required' // 'all' or a valid date string
        ]);

        $query = LeaveRequest::where('is_archived', false)
            ->whereIn('status', ['Approved', 'Rejected', 'Cancelled']);

        if ($request->before_date !== 'all') {
            $query->where('from_date', '<', $request->before_date);
        }

        $count = $query->update([
            'is_archived' => true,
            'archived_at' => now(),
            'updated_at' => now()
        ]);

        $logDesc = $request->before_date === 'all'
            ? "Performed bulk archive for all eligible records."
            : "Performed bulk archive for records before {$request->before_date}.";

        \App\Utils\AuditLogger::log('Leaves', 'Bulk Archived', "{$logDesc} Total requests affected: {$count}.");

        return response()->json([
            'message' => "Successfully archived $count leave requests.",
            'count' => $count
        ]);
    }

    public function getArchiveIndex()
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // 1. Leave Requests Structure
        $leaveStructure = LeaveRequest::where('is_archived', true)
            ->selectRaw('EXTRACT(YEAR FROM from_date) as year, EXTRACT(MONTH FROM from_date) as month, COUNT(*) as count')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        $leaveGrouped = [];
        foreach ($leaveStructure as $item) {
            $year = (int) $item->year;
            $month = (int) $item->month;
            $monthName = date('F', mktime(0, 0, 0, $month, 10));

            if (!isset($leaveGrouped[$year])) {
                $leaveGrouped[$year] = [
                    'year' => $year,
                    'total' => 0,
                    'months' => []
                ];
            }
            $leaveGrouped[$year]['months'][] = [
                'month' => $month,
                'month_name' => $monthName,
                'count' => (int) $item->count
            ];
            $leaveGrouped[$year]['total'] += (int) $item->count;
        }

        // 2. Employees Structure
        $employeeCount = \App\Models\Employee::where('is_archived', true)->count();

        return response()->json([
            'leaves' => array_values($leaveGrouped),
            'employees' => [
                'count' => $employeeCount
            ]
        ]);
    }
}
