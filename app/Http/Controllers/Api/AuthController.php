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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Class AuthController
 *
 * API controller for handling authentication operations.
 * Delegates logic to AuthService.
 *
 * @group Authentication
 */
class AuthController extends Controller
{
    /**
     * AuthController constructor.
     */
    public function __construct(
        private readonly AuthService $service
    ) {
    }

    /**
     * Authenticate a user and return a token.
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
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->service->register($request->validated());

        return response()->success(
            new UserResource($user),
            'Registration successful. Please verify your email address. Your account is pending approval.',
            ResponseAlias::HTTP_CREATED
        );
    }

    /**
     * Logout the authenticated user (revoke current token).
     */
    public function logout(): JsonResponse
    {
        $this->service->logout(Auth::user());

        return response()->success(null, 'Logged out successfully');
    }

    /**
     * Get the authenticated user.
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
     */
    public function logoutAll(): JsonResponse
    {
        $this->service->logoutAll(Auth::user());

        return response()->success(null, 'Logged out from all devices successfully');
    }

    /**
     * Send password reset link to user's email.
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $message = $this->service->sendPasswordResetLink($request->validated());

        return response()->success(null, $message);
    }

    /**
     * Reset user password using token.
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $message = $this->service->resetPassword($request->validated());

        return response()->success(null, $message);
    }

    /**
     * Verify user's email address.
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
     */
    public function resendVerificationEmail(): JsonResponse
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            return response()->error('Email is already verified.', ResponseAlias::HTTP_BAD_REQUEST);
        }

        $this->service->resendEmailVerification($user);

        return response()->success(null, 'Verification email sent successfully');
    }

    /**
     * Refresh the authentication token.
     */
    public function refreshToken(RefreshTokenRequest $request): JsonResponse
    {
        $result = $this->service->refreshToken(
            Auth::user(),
            $request->validated()['revoke_old_token'] ?? false
        );

        return response()->success([
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
        ], 'Token refreshed successfully');
    }

    /**
     * Update the authenticated user's profile.
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $updatedUser = $this->service->updateProfile(Auth::user(), $request->validated());
        $updatedUser->load(['biller', 'warehouse', 'roles', 'permissions']);

        return response()->success(
            new UserResource($updatedUser),
            'Profile updated successfully'
        );
    }

    /**
     * Change the authenticated user's password.
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $this->service->changePassword(Auth::user(), $request->validated()['password']);

        return response()->success(null, 'Password changed successfully');
    }

    /**
     * Unlock the screen by verifying the user's password.
     */
    public function unlock(UnlockRequest $request): JsonResponse
    {
        $this->service->unlock(Auth::user(), $request->validated()['password']);

        return response()->success(null, 'Unlocked successfully');
    }
}
