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
        // Return all users/employees (maybe filter out admins if needed, but "Total Employees" usually includes everyone or just role=user)
        // Let's return all for now to populate the list.
        $users = User::orderBy('id')->get();
        return response()->json($users);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'role' => 'required|string|in:admin,user',
            'position' => 'nullable|string|max:255',
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

        // Generate ID Number (EMP-XXX)
        $lastUser = User::latest('id')->first();
        $nextId = $lastUser ? $lastUser->id + 1 : 1;
        $idNumber = 'EMP-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $email,
            'password' => Hash::make('password'), // Default password
            'department' => $validated['department'],
            'role' => $validated['role'],
            'position' => $validated['position'] ?? $validated['role'], // Default position to role if empty
            'id_number' => $idNumber,
            'status' => 'Available',
        ]);

        return response()->json([
            'message' => 'Employee created successfully',
            'user' => $user,
            'generated_email' => $email,
            'default_password' => 'password'
        ], 201);
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
}
