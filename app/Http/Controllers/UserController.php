<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index()
    {
        // Return paginated users/employees for scalability
        $users = User::orderBy('id')->paginate(20);
        return response()->json($users);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'role' => 'required|string|in:admin,user,dept_head',
            'position' => 'nullable|string|max:255',
            'id_number' => 'required|string|unique:users,id_number|max:20',
            'employment_status' => 'required|string|in:Probationary,Regular',
        ]);

        // Generate Email: firstname@hq.app
        $firstName = explode(' ', trim($validated['name']))[0];
        $email = Str::lower($firstName) . '@hq.app';

        // Ensure email is unique (simple append if exists)
        $count = 0;
        $originalEmail = $email;
        while (User::where('email', $email)->exists()) {
            $count++;
            $email = Str::lower($firstName) . $count . '@hq.app';
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $email,
            'password' => Hash::make('password'), // Default password
            'department' => $validated['department'],
            'role' => $validated['role'],
            'position' => $validated['position'] ?? $validated['role'], // Default position to role if empty
            'id_number' => $validated['id_number'],
            'status' => 'Available',
            'employment_status' => $validated['employment_status'],
        ]);

        // Audit Log
        \App\Utils\AuditLogger::log('Employees', 'Created', "Registered a new employee: {$user->name} ({$user->id_number}).", null, $user->toArray());

        return response()->json([
            'message' => 'Employee created successfully',
            'user' => $user,
            'generated_email' => $email,
            'default_password' => 'password'
        ], 201);
    }

    public function createFromEmployee(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'role' => 'required|string|in:admin,user,dept_head',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        $employee = \App\Models\Employee::with('department')->findOrFail($request->employee_id);

        $targetDepartmentId = $request->department_id ?? $employee->department_id;
        $targetDepartment = \App\Models\Department::find($targetDepartmentId);

        // Check if user already exists
        if (User::where('id_number', $employee->employee_id)->exists()) {
            return response()->json(['message' => 'User account already exists for this employee.'], 422);
        }

        // Generate Name
        $name = "{$employee->first_name} {$employee->last_name}";

        // Generate Email: first.last@hq.app
        $email = Str::lower($employee->first_name) . '.' . Str::lower($employee->last_name) . '@hq.app';

        // Ensure email is unique
        $count = 0;
        while (User::where('email', $email)->exists()) {
            $count++;
            $email = Str::lower($employee->first_name) . '.' . Str::lower($employee->last_name) . $count . '@hq.app';
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make('password'),
            'role' => $request->role,
            'id_number' => $employee->employee_id,
            'position' => $employee->position,
            'department' => $targetDepartment?->name,
            'department_id' => $targetDepartmentId,
            'employment_status' => $employee->employment_status,
            'status' => 'Available',
        ]);

        // Audit Log
        \App\Utils\AuditLogger::log('Security', 'Created', "Generated system account for employee: {$name} (#{$employee->employee_id}) as {$request->role}.", null, $user->toArray());

        return response()->json([
            'message' => 'Account created successfully',
            'user' => $user,
            'email' => $email,
            'password' => 'password'
        ]);
    }

    public function updateEmployee(Request $request, $id)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'department' => 'sometimes|string',
            'position' => 'sometimes|string',
            'employment_status' => 'sometimes|string|in:Probationary,Regular',
            'role' => 'sometimes|string|in:admin,user,dept_head',
            'id_number' => 'sometimes|string|max:20|unique:users,id_number,' . $id,
        ]);

        $oldData = $user->toArray();
        $user->update($validated);

        // Audit Log
        \App\Utils\AuditLogger::log('Employees', 'Updated', "Updated profile of employee: {$user->name} (#{$user->id}).", $oldData, $user->toArray());

        return response()->json($user);
    }
    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240',
        ]);

        $user = Auth::user();

        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $path = $request->file('avatar')->store('avatars', 'public');
            $user->update(['avatar' => $path]);

            return response()->json([
                'message' => 'Avatar uploaded successfully',
                'avatar_url' => asset('storage/' . $path),
            ]);
        }

        return response()->json(['message' => 'No file uploaded'], 400);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if (!empty($validated['password'])) {
            $userData['password'] = Hash::make($validated['password']);
        }

        $user->update($userData);

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user
        ]);
    }
    public function changeUserPassword(Request $request, $id)
    {
        // Only allow admin to do this
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user = User::findOrFail($id);

        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
        ]);

        // Audit Log
        \App\Utils\AuditLogger::log('Employees', 'Security', "Reset password (forced) for employee: {$user->name} (#{$user->id}).");

        return response()->json(['message' => 'Password updated successfully']);
    }

    public function changeOwnPassword(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json(['message' => 'The provided password does not match your current password.'], 422);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        // Audit Log
        \App\Utils\AuditLogger::log('Security', 'Updated', "User changed their own password.");

        return response()->json(['message' => 'Password changed successfully.']);
    }

    public function bulkAddCredits(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'amount' => 'required|numeric',
        ]);

        // Skip for now, feature being deprecated.
        return response()->json([
            'message' => "Bulk credits not supported with new leave balance system.",
            'updated_count' => 0
        ]);
    }

    public function resetAllCredits(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Skip for now, feature being deprecated.
        return response()->json([
            'message' => "Credit reset not supported with new leave balance system.",
            'count' => 0
        ]);
    }
}
