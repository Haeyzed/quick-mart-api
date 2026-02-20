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
 * API Controller for authentication operations.
 * Handles authorization and delegates logic to AuthService.
 *
 * @tags Authentication
 */
class AuthController extends Controller
{
    /**
     * AuthController constructor.
     */
    public function __construct(
        private readonly AuthService $service
    ) {}

    /**
     * Login
     *
     * Authenticate a user and return a Sanctum token with user resource.
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
     * Register
     *
     * Register a new user. Account is inactive until email is verified and approved.
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
     * Logout
     *
     * Logout the authenticated user (revoke current token).
     */
    public function logout(): JsonResponse
    {
        $this->service->logout(Auth::user());

        return response()->success(null, 'Logged out successfully');
    }

    /**
     * Get Authenticated User
     *
     * Return the currently authenticated user with relationships.
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
     * Logout All Devices
     *
     * Logout from all devices (revoke all tokens for the user).
     */
    public function logoutAll(): JsonResponse
    {
        $this->service->logoutAll(Auth::user());

        return response()->success(null, 'Logged out from all devices successfully');
    }

    /**
     * Forgot Password
     *
     * Send a password reset link to the user's email.
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $message = $this->service->sendPasswordResetLink($request->validated());

        return response()->success(null, $message);
    }

    /**
     * Reset Password
     *
     * Reset user password using the token from the reset link.
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $message = $this->service->resetPassword($request->validated());

        return response()->success(null, $message);
    }

    /**
     * Verify Email
     *
     * Verify the user's email address via the signed link.
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
     * Resend Verification Email
     *
     * Resend the email verification notification to the authenticated user.
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
     * Refresh Token
     *
     * Refresh the authentication token. Optionally revoke the previous token.
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
     * Update Profile
     *
     * Update the authenticated user's profile information.
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
     * Change Password
     *
     * Change the authenticated user's password (requires current password).
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $this->service->changePassword(Auth::user(), $request->validated()['password']);

        return response()->success(null, 'Password changed successfully');
    }

    /**
     * Unlock
     *
     * Unlock the screen by verifying the user's password.
     */
    public function unlock(UnlockRequest $request): JsonResponse
    {
        $this->service->unlock(Auth::user(), $request->validated()['password']);

        return response()->success(null, 'Unlocked successfully');
    }
}
