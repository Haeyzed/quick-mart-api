<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RefreshTokenRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\UnlockRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\AuthService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * AuthController
 *
 * API controller for handling authentication operations including login, registration,
 * password reset, and user management using Laravel Sanctum.
 */
class AuthController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param AuthService $service
     */
    public function __construct(
        private readonly AuthService $service
    )
    {
    }

    /**
     * Authenticate a user and return a token.
     *
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->service->login($request->validated());

        return response()->success([
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
        ], 'Login successful');
    }

    /**
     * Register a new user.
     *
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->service->register($request->validated());

        // Send email verification if email is provided
        if ($user->email) {
            try {
                $this->service->sendEmailVerification($user);
            } catch (Exception $e) {
                // Log error but don't fail registration
                Log::error('Failed to send verification email: ' . $e->getMessage());
            }
        }

        return response()->success(
            new UserResource($user),
            'Registration successful. Please verify your email address. Your account is pending approval.',
            201
        );
    }

    /**
     * Logout the authenticated user (revoke current token).
     *
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        $this->service->logout(Auth::user());

        return response()->success(
            null,
            'Logged out successfully'
        );
    }

    /**
     * Get the authenticated user.
     *
     * @return JsonResponse
     */
    public function user(): JsonResponse
    {
        $user = $this->service->getAuthenticatedUser(Auth::user());

        return response()->success(
            new UserResource($user),
            'User retrieved successfully'
        );
    }

    /**
     * Logout from all devices (revoke all tokens).
     *
     * @return JsonResponse
     */
    public function logoutAll(): JsonResponse
    {
        $this->service->logoutAll(Auth::user());

        return response()->success(
            null,
            'Logged out from all devices successfully'
        );
    }

    /**
     * Send password reset link to user's email.
     *
     * @param ForgotPasswordRequest $request
     * @return JsonResponse
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $message = $this->service->sendPasswordResetLink($request->validated());

        return response()->success(
            null,
            $message
        );
    }

    /**
     * Reset user password using token.
     *
     * @param ResetPasswordRequest $request
     * @return JsonResponse
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $message = $this->service->resetPassword($request->validated());

        return response()->success(
            null,
            $message
        );
    }

    /**
     * Verify user's email address.
     *
     * @param Request $request
     * @param int $id
     * @param string $hash
     * @return JsonResponse
     */
    public function verifyEmail(Request $request, int $id, string $hash): JsonResponse
    {
        $user = User::findOrFail($id);

        $this->service->verifyEmail($user, $hash);

        return response()->success(
            new UserResource($user),
            'Email verified successfully'
        );
    }

    /**
     * Resend email verification notification.
     *
     * @return JsonResponse
     */
    public function resendVerificationEmail(): JsonResponse
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => 'Email is already verified.',
            ], 400);
        }

        $this->service->resendEmailVerification($user);

        return response()->success(
            null,
            'Verification email sent successfully'
        );
    }

    /**
     * Refresh the authentication token.
     *
     * @param RefreshTokenRequest $request
     * @return JsonResponse
     */
    public function refreshToken(RefreshTokenRequest $request): JsonResponse
    {
        $user = Auth::user();
        $revokeOldToken = $request->validated()['revoke_old_token'] ?? false;

        $result = $this->service->refreshToken($user, $revokeOldToken);

        return response()->success([
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
        ], 'Token refreshed successfully');
    }

    /**
     * Update the authenticated user's profile.
     *
     * @param UpdateProfileRequest $request
     * @return JsonResponse
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = Auth::user();
        $updatedUser = $this->service->updateProfile($user, $request->validated());
        $updatedUser->load(['biller', 'warehouse', 'roles', 'permissions']);

        return response()->success(
            new UserResource($updatedUser),
            'Profile updated successfully'
        );
    }

    /**
     * Change the authenticated user's password.
     *
     * @param ChangePasswordRequest $request
     * @return JsonResponse
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = Auth::user();
        $this->service->changePassword($user, $request->validated()['password']);

        return response()->success(
            null,
            'Password changed successfully'
        );
    }

    /**
     * Unlock the screen by verifying the user's password.
     * Requires authentication; used after lock screen / idle.
     *
     * @param UnlockRequest $request
     * @return JsonResponse
     */
    public function unlock(UnlockRequest $request): JsonResponse
    {
        $user = Auth::user();
        $this->service->unlock($user, $request->validated()['password']);

        return response()->success(
            null,
            'Unlocked successfully'
        );
    }
}

