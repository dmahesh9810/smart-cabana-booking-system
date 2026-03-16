<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

use App\Traits\ApiResponse;

class AuthController extends Controller
{
    use ApiResponse;

    public function register(RegisterRequest $request)
    {
        $role = Role::where('name', 'customer')->first();

        $user = User::create([
            'role_id' => $role ? $role->id : null,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        // Send Email Verification
        $user->sendEmailVerificationNotification();

        return $this->successResponse([
            'user' => $user->load('role'),
            'token' => $token
        ], 'User registered successfully', 201);
    }

    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->errorResponse('Invalid login credentials', 401);
        }

        if ($user->email_verified_at === null) {
            return $this->errorResponse('Please verify your email address before logging in.', 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'user' => $user->load('role'),
            'token' => $token
        ], 'Login successful');
    }


    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
            'data' => null
        ], 200);
    }

    public function user(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'User details retrieved successfully',
            'data' => [
                'user' => $request->user()->load('role')
            ]
        ], 200);
    }
}
