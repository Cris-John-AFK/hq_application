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
            'role' => 'required|string|in:admin,user',
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
            'role' => 'sometimes|string|in:admin,user',
            'id_number' => 'sometimes|string|max:20|unique:users,id_number,' . $id,
            'leave_credits' => 'sometimes|numeric|min:0',
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
        \App\Utils\AuditLogger::log('Employees', 'Security', "Reset password for employee: {$user->name} (#{$user->id}).");

        return response()->json(['message' => 'Password updated successfully']);
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

        $amount = $validated['amount'];
        $count = User::whereIn('id', $validated['user_ids'])->increment('leave_credits', $amount);

        // Audit Log
        \App\Utils\AuditLogger::log('Employees', 'Updated', "Bulk updated leave credits (+{$amount}) for {$count} employee(s).");

        return response()->json([
            'message' => "Successfully added {$amount} credits to {$count} employees.",
            'updated_count' => $count
        ]);
    }

    public function resetAllCredits(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $count = User::query()->update(['leave_credits' => 0]);

        // Audit Log
        \App\Utils\AuditLogger::log('Employees', 'Security', "NUCLEAR RESET: All leave credits reset to 0 for {$count} employee(s).");

        return response()->json([
            'message' => "Successfully reset credits for {$count} employees.",
            'count' => $count
        ]);
    }
}
