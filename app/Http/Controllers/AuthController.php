<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['register', 'login']);
    }

    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'role_id' => 'required|integer',
            'status' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8',
        ]);

        if (User::where('email', $validatedData['email'])->exists()) {
            return response()->json(['message' => 'Email already exists'], 409);
        }

        $validatedData['password'] = Hash::make($validatedData['password']); // Hash the password

        try {
            $user = User::create($validatedData);
            return response()->json(['message' => 'User created successfully', 'user' => $user], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'User creation failed', 'error' => $e->getMessage()], 500);
        }
    }

public function login(Request $request)
{
    \Log::info('Login request data: ', $request->all());

    $credentials = $request->validate([
        'email' => 'required|string|email',
        'password' => 'required|string|min:8',
    ]);

    $user = User::where('email', $credentials['email'])->first();

    if (!$user || !Hash::check($credentials['password'], $user->password)) {
        \Log::info('Invalid credentials for email: ' . $credentials['email']);
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    if ($user->status !== 'is_active') {
        \Log::info('Inactive account for email: ' . $credentials['email']);
        return response()->json(['message' => 'Account is not active'], 403);
    }

    try {
        $token = $user->createToken('authToken', [], Carbon::now()->addHours(8))->plainTextToken;
        $cookie = cookie('auth_token', $token, 480);

        \Log::info('Login successful for email: ' . $credentials['email']);

        return response()->json([
            'user' => $user,
            'token' => $token,
            'role_id' => $user->role_id
        ], 200)->cookie($cookie);
    } catch (\Exception $e) {
        \Log::error('Error during login: ' . $e->getMessage());
        return response()->json(['message' => 'An error occurred', 'error' => $e->getMessage()], 500);
    }
}



    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        $cookie = cookie()->forget('auth_token');

        return response()->json(['message' => 'Logged out successfully'])->cookie($cookie);
    }

    public function getLoggedUserProfile(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'user_id' => $user->user_id,
            'email' => $user->email,
            'name' => $user->name,
            'role_id' => $user->role_id,
            'status' => $user->status,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();

        if ($request->email !== $user->email) {
            return response()->json(['message' => 'Email does not match the logged-in user.'], 403);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['message' => 'Password has been reset successfully.']);
    }

    public function getLoggedUserName(Request $request)
    {
        $user = $request->user();

        return response()->json(['email' => $user->email]);
    }

    public function getLoggedUserID(Request $request)
    {
        $user = $request->user();

        return response()->json(['user_id' => $user->user_id]);
    }



  public function users(Request $request)
{
    // Eager load the 'role' relationship and return the category instead of role_id
    $users = User::with('role') // Load the related Role model
                 ->orderBy('user_id', 'desc')
                 ->get();

    // Modify the response to include the role category
    $users = $users->map(function ($user) {
        return [
            'user_id' => $user->user_id,
            'name' => $user->name,
            'email' => $user->email,
            'status' => $user->status,
            'role' => $user->role->category, // Return the 'category' from the Role model
        ];
    });

    return response()->json(['users' => $users]);
}




    // Update user
    public function updateUser(Request $request, $user_id)
    {
        $user = User::find($user_id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'role_id' => 'required|integer',
            'status' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'nullable|string|min:8',
        ]);

        if ($request->filled('password')) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        } else {
            unset($validatedData['password']);
        }

        $user->update($validatedData);

        return response()->json(['message' => 'User updated successfully', 'user' => $user], 200);
    }

    // Delete user
    public function deleteUser($user_id)
    {
        $user = User::find($user_id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully'], 200);
    }

    public function logUserActivity(Request $request)
    {
        try {
            // Log the activity for debugging purposes
            \Log::info("Logging activity for user_id: {$request->user_id}, action: {$request->action}");

            // Create a new log entry
            UserLog::create([
                'user_id' => $request->user_id,
                'role_id' => Auth::user()->role_id,
                'action' => $request->action,
            ]);

            return response()->json(['message' => 'Activity logged successfully'], 200);
        } catch (\Exception $e) {
            // Log the error
            \Log::error('Failed to log user activity: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to log activity'], 500);
        }
    }


}
