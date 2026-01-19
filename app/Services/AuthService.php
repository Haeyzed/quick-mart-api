<?php

declare(strict_types=1);

namespace App\Services;

use App\Mail\EmailVerification;
use App\Mail\PasswordReset as PasswordResetMail;
use App\Models\Customer;
use App\Models\GeneralSetting;
use App\Models\MailSetting;
use App\Models\User;
use App\Services\PermissionService;
use App\Traits\MailInfo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;

/**
 * AuthService
 *
 * Handles authentication-related business logic including login, registration,
 * password reset, and token management using Laravel Sanctum.
 */
class AuthService extends BaseService
{
    use MailInfo;

    public function __construct(
        private readonly PermissionService $permissionService
    ) {
    }

    /**
     * Authenticate a user and generate a Sanctum token.
     *
     * @param array<string, mixed> $credentials
     * @return array<string, mixed>
     * @throws ValidationException
     */
    public function login(array $credentials): array
    {
        // Determine if login is by email or name
        $fieldType = filter_var($credentials['name'], FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

        // Attempt authentication
        if (!Auth::attempt([$fieldType => $credentials['name'], 'password' => $credentials['password']])) {
            throw ValidationException::withMessages([
                'name' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = Auth::user();

        // Check if email is verified first
        if (!$user->hasVerifiedEmail()) {
            Auth::logout();
            
            // Try to resend verification email
            try {
                $this->sendEmailVerification($user);
            } catch (\Exception $e) {
                // Log error but continue with the response
                $this->logError('Failed to resend verification email during login: ' . $e->getMessage());
            }
            
            throw ValidationException::withMessages([
                'email' => ['Please verify your email address before logging in. A verification email has been sent to your email address.'],
            ]);
        }

        // If email is verified, check if user is active and not deleted
        if (!$user->isActive() || $user->isDeleted()) {
            Auth::logout();
            throw ValidationException::withMessages([
                'name' => ['Your account has been deactivated. Please contact the administrator.'],
            ]);
        }

        // Revoke all existing tokens (optional - for single device login)
        // $user->tokens()->delete();

        // Load roles and permissions for frontend consumption
        $user->load(['roles', 'permissions']);

        // Create new token
        $token = $user->createToken('auth-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Register a new user.
     *
     * @param array<string, mixed> $data
     * @return User
     */
    public function register(array $data): User
    {
        return $this->transaction(function () use ($data) {
            // Create user
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'] ?? null,
                'phone' => $data['phone_number'] ?? null,
                'company_name' => $data['company_name'] ?? null,
                'role_id' => $data['role_id'],
                'biller_id' => $data['biller_id'] ?? null,
                'warehouse_id' => $data['warehouse_id'] ?? null,
                'is_active' => false, // New users are inactive by default
                'is_deleted' => false,
                'password' => Hash::make($data['password']),
            ]);

            // If role is customer (role_id = 5), create customer record
            if ($data['role_id'] == 5) {
                Customer::create([
                    'name' => $data['customer_name'] ?? $data['name'],
                    'user_id' => $user->id,
                    'customer_group_id' => $data['customer_group_id'] ?? null,
                    'email' => $data['email'] ?? null,
                    'phone_number' => $data['phone_number'] ?? null,
                    'company_name' => $data['company_name'] ?? null,
                    'is_active' => true,
                ]);
            }

            return $user;
        });
    }

    /**
     * Logout the authenticated user (revoke current token).
     *
     * @param User $user
     * @return void
     */
    public function logout(User $user): void
    {
        // Revoke the current access token
        $user->currentAccessToken()?->delete();
    }

    /**
     * Logout from all devices (revoke all tokens).
     *
     * @param User $user
     * @return void
     */
    public function logoutAll(User $user): void
    {
        $user->tokens()->delete();
    }

    /**
     * Send password reset link to user's email.
     *
     * @param array<string, mixed> $data
     * @return string
     * @throws ValidationException
     */
    public function sendPasswordResetLink(array $data): string
    {
        $user = User::where('email', $data['email'])->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['We can\'t find a user with that email address.'],
            ]);
        }

        // Get mail settings
        $mailSetting = MailSetting::latest()->first();
        if (!$mailSetting) {
            throw ValidationException::withMessages([
                'email' => ['Mail settings are not configured. Please contact the administrator.'],
            ]);
        }

        // Set mail info
        $this->setMailInfo($mailSetting);

        // Generate reset token
        $token = Password::createToken($user);

        // Get general settings
        $generalSetting = GeneralSetting::latest()->first();

        // Send custom password reset email
        Mail::to($user->email)->send(
            new PasswordResetMail($user, $token, $generalSetting)
        );

        return 'We have emailed your password reset link.';
    }

    /**
     * Reset user password using token.
     *
     * @param array<string, mixed> $data
     * @return string
     * @throws ValidationException
     */
    public function resetPassword(array $data): string
    {
        $status = Password::reset(
            [
                'email' => $data['email'],
                'password' => $data['password'],
                'password_confirmation' => $data['password_confirmation'],
                'token' => $data['token'],
            ],
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return __($status);
    }

    /**
     * Get the authenticated user with relationships and permissions.
     *
     * Loads roles and permissions for frontend consumption.
     *
     * @param User $user
     * @return User
     */
    public function getAuthenticatedUser(User $user): User
    {
        return $user->load(['biller', 'warehouse', 'roles', 'permissions']);
    }

    /**
     * Send email verification notification.
     *
     * @param User $user
     * @return void
     * @throws ValidationException
     */
    public function sendEmailVerification(User $user): void
    {
        if ($user->hasVerifiedEmail()) {
            throw ValidationException::withMessages([
                'email' => ['Email is already verified.'],
            ]);
        }

        // Get mail settings
        $mailSetting = MailSetting::latest()->first();
        if (!$mailSetting) {
            throw ValidationException::withMessages([
                'email' => ['Mail settings are not configured. Please contact the administrator.'],
            ]);
        }

        // Set mail info
        $this->setMailInfo($mailSetting);

        // Get general settings
        $generalSetting = GeneralSetting::latest()->first();

        // Send custom verification email
        Mail::to($user->email)->send(
            new EmailVerification($user, $generalSetting)
        );
    }

    /**
     * Verify user's email address.
     *
     * @param User $user
     * @param string $hash
     * @return bool
     * @throws ValidationException
     */
    public function verifyEmail(User $user, string $hash): bool
    {
        if ($user->hasVerifiedEmail()) {
            throw ValidationException::withMessages([
                'email' => ['Email is already verified.'],
            ]);
        }

        // Verify the hash matches
        if (!hash_equals((string)$hash, sha1($user->getEmailForVerification()))) {
            throw ValidationException::withMessages([
                'email' => ['Invalid verification link.'],
            ]);
        }

        // Mark email as verified and activate the user
        if ($user->markEmailAsVerified()) {
            // Set user as active when email is verified
            $user->is_active = true;
            $user->save();
            
            return true;
        }

        throw ValidationException::withMessages([
            'email' => ['Failed to verify email.'],
        ]);
    }

    /**
     * Resend email verification notification.
     *
     * @param User $user
     * @return void
     * @throws ValidationException
     */
    public function resendEmailVerification(User $user): void
    {
        $this->sendEmailVerification($user);
    }

    /**
     * Refresh the authentication token for the user.
     *
     * @param User $user
     * @param bool $revokeOldToken Whether to revoke the old token
     * @return array<string, mixed>
     */
    public function refreshToken(User $user, bool $revokeOldToken = false): array
    {
        // Revoke the current token if requested
        if ($revokeOldToken) {
            $user->currentAccessToken()?->delete();
        }

        // Load roles and permissions for frontend consumption
        $user->load(['roles', 'permissions']);

        // Create a new token
        $token = $user->createToken('auth-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Update user profile information.
     *
     * @param User $user
     * @param array<string, mixed> $data
     * @return User
     */
    public function updateProfile(User $user, array $data): User
    {
        return $this->transaction(function () use ($user, $data) {
            // Only update fields that are provided and allowed
            $allowedFields = ['name', 'email', 'phone', 'company_name'];
            
            $updateData = [];
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updateData[$field] = $data[$field];
                }
            }

            $user->update($updateData);
            $user->refresh();

            return $user;
        });
    }

    /**
     * Change user password.
     *
     * @param User $user
     * @param string $newPassword
     * @return void
     */
    public function changePassword(User $user, string $newPassword): void
    {
        $this->transaction(function () use ($user, $newPassword) {
            $user->password = Hash::make($newPassword);
            $user->save();
        });
    }
}

