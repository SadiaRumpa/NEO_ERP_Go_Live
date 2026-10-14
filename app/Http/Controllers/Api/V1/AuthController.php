<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends ApiController
{
    /**
     * Issue an API bearer token upon successful authentication.
     */
    public function token(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email'       => 'required|email',
            'password'    => 'required|string',
            'device_name' => 'nullable|string|max:255',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return $this->errorResponse('Invalid email or password.', 401);
        }

        $deviceName = $validated['device_name'] ?? 'api-token';
        $token = $user->createToken($deviceName)->plainTextToken;

        return $this->successResponse([
            'token_type'   => 'Bearer',
            'access_token' => $token,
            'user'         => new UserResource($user),
        ], 'Token issued successfully.');
    }

    /**
     * Return the currently authenticated user with roles and permissions.
     */
    public function me(Request $request): JsonResponse
    {
        return $this->successResponse(new UserResource($request->user()), 'User profile retrieved.');
    }

    /**
     * Revoke the current access token.
     */
    public function revoke(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }

        return $this->successResponse(null, 'Current token revoked successfully.');
    }

    /**
     * Revoke all access tokens for the authenticated user.
     */
    public function revokeAll(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user) {
            $user->tokens()->delete();
        }

        return $this->successResponse(null, 'All tokens revoked successfully.');
    }
}
