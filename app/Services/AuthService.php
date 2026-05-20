<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function register(array $data): array
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'age' => $data['age'] ?? null,
            'weight' => $data['weight'] ?? null,
            'height' => $data['height'] ?? null,
        ]);

        $token = $user->createToken("fittrack_token")->plainTextToken;

        return [
            'user' => $user,
            'token' => $token
        ];
    }

    public function login(array $data): array
    {
        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw new \Exception("Invalid credentials");
        }

        $user->tokens()->delete();

        $token = $user->createToken("fittrack_token")->plainTextToken;

        return [
            'user' => $user,
            'token' => $token
        ];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }

    public function updateProfile(User $user, array $data): User
    {
        $fillable = array_intersect_key([
            'name' => $data['name'] ?? null,
            'email' => $data['email'] ?? null,
            'age' => $data['age'] ?? null,
            'weight' => $data['weight'] ?? null,
            'height' => $data['height'] ?? null,
            'password' => isset($data['password']) ? Hash::make($data['password']) : null,
        ], array_filter($data, fn($v) => $v !== null));

        $user->update($fillable);
        return $user->fresh();
    }
}