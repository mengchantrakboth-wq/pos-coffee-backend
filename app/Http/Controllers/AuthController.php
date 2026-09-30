<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'full_name' => 'required|string|max:255',
                'username'  => 'required|string|max:100|unique:users,username',
                'email'     => 'required|email|unique:users,email',
                'phone'     => 'required|string|max:20',
                'password'  => 'required|string|min:6|confirmed',
                'role_id'   => 'required|integer|exists:roles,role_id',
            ]);

            $user = User::create([
                'full_name'     => $data['full_name'],
                'username'      => $data['username'],
                'email'         => $data['email'],
                'phone'         => $data['phone'],
                'password_hash' => Hash::make($data['password']),
                'role_id'       => $data['role_id'],
            ]);

            return response()->json(['message' => 'Account created successfully', 'user' => $user], 201);
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            return $this->errorResponse($e, 'Failed to create account');
        }
    }

    public function login(Request $request): JsonResponse
    {
        try {
            $credentials = $request->validate([
                'username' => 'required|string',
                'password' => 'required|string',
            ]);

            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }

            return $this->respondWithToken($token);
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            return $this->errorResponse($e, 'Login failed');
        }
    }

    public function me(): JsonResponse
    {
        return response()->json(['user' => JWTAuth::user()?->load('role')]);
    }

    public function refresh(): JsonResponse
    {
        try {
            return $this->respondWithToken(JWTAuth::refresh());
        } catch (Throwable $e) {
            return response()->json(['error' => 'Token cannot be refreshed'], 401);
        }
    }

    public function logout(): JsonResponse
    {
        JWTAuth::logout();

        return response()->json(['message' => 'Logged out successfully']);
    }

    private function respondWithToken(string $token): JsonResponse
    {
        return response()->json([
            'token'      => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
            'user'       => JWTAuth::user()?->load('role'),
        ]);
    }

    private function errorResponse(Throwable $e, string $message): JsonResponse
    {
        Log::error($message, ['error' => $e->getMessage()]);

        return response()->json([
            'error'   => $message,
            'message' => config('app.debug') ? $e->getMessage() : null,
        ], 500);
    }
}
