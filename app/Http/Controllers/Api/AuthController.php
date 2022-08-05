<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use \Illuminate\Http\JsonResponse;

use App\Http\Requests\RegisterUserRequest;
use App\Http\Requests\LoginUserRequest;

class AuthController extends Controller
{

    public function registerUser(RegisterUserRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $validated['password'] = Hash::make($validated['password']);

            User::create($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'User created successfully',
                'data' => null,
            ]);
        } catch (Exception) {
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong',
                'data' => null,
            ], 500);
        }
    }

    public function loginUser(LoginUserRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $err = !Auth::attempt($validated);
            if ($err) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid credentials',
                    'data' => null,
                ], 401);
            }

            $user = User::where('email', $validated['email'])->first();

            return response()->json([
                'status' => 'success',
                'message' => null,
                'data' => [
                    'token' => $user->createToken('api_token')->plainTextToken
                ]
            ]);
        } catch (Exception) {
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong',
                'data' => null,
            ], 500);
        }
    }

    function authUser(Request $request): User
    {
        return $request->user();
    }
}
