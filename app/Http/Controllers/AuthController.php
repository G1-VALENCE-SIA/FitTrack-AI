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
            'weight' => 'nullable|numeric',
            'height' => 'nullable|numeric',
        ]);

        $data = $this->authService->register($request->all());

        return response()->json([
            "message" => "User registered successfully",
            ...$data
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            "email" => "required|email",
            "password" => "required"
        ]);

        try {
            $data = $this->authService->login($request->all());

            return response()->json([
                "message" => "Login successful",
                ...$data
            ]);
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                "email" => [$e->getMessage()]
            ]);
        }
    }

    public function profile(Request $request)
    {
        return response()->json($request->user());
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|unique:users,email,' . $user->id,
            'password' => 'sometimes|required|string|min:6|confirmed',
            'age' => 'sometimes|nullable|integer',
            'weight' => 'sometimes|nullable|numeric',
            'height' => 'sometimes|nullable|numeric',
        ]);

        $user = $this->authService->updateProfile($user, $request->all());

        return response()->json([
            "message" => "Profile updated successfully",
            "user" => $user
        ]);
    }

    public function logout(Request $request)
    {
        $this->authService->logout($request->user());

        return response()->json([
            "message" => "Logout successful"
        ]);
    }
}