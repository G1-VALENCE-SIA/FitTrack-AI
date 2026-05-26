<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AuthService;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(protected AuthService $authService)
    {
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'age' => 'nullable|integer',
            'gender' => 'nullable|in:male,female,other',
            'weight' => 'nullable|numeric',
            'height' => 'nullable|numeric',
        ]);

        $data = $this->authService->register($request->all());
        return $this->successResponse($data, 'User registered successfully', 201);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|unique:users,email,' . $user->id,
            'password' => 'sometimes|required|string|min:6|confirmed',
            'age' => 'sometimes|nullable|integer',
            'gender' => 'sometimes|nullable|in:male,female,other',
            'weight' => 'sometimes|nullable|numeric',
            'height' => 'sometimes|nullable|numeric',
        ]);

        $user = $this->authService->updateProfile($user, $request->all());
        return $this->successResponse($user, 'Profile updated successfully');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $data = $this->authService->login($request->all());
        return $this->successResponse($data, 'Login successful');
    }

    public function profile(Request $request)
    {
        return $this->successResponse($request->user(), 'Profile retrieved');
    }

    public function logout(Request $request)
    {
        $this->authService->logout($request->user());
        return $this->successResponse(null, 'Logout successful');
    }

    public function deleteAccount(Request $request)
    {
        $this->authService->deleteAccount($request->user());
        return $this->successResponse(null, 'Account deleted successfully');
    }
}