<?php

declare(strict_types=1);

namespace App\Services;

use App\Mail\EmailVerification;
use App\Mail\PasswordReset as PasswordResetMail;
use App\Models\Customer;
use App\Models\GeneralSetting;
use App\Models\MailSetting;
use App\Models\User;
use App\Traits\MailInfo;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class AuthService
 * Handles all core business logic and database interactions for Authentication.
 * Acts as the intermediary between the controllers and the database layer.
 */
class AuthService
{
    use MailInfo;

    /**
     * The storage path for user image uploads.
     */
    private const AVATARS_IMAGE_PATH = 'images/users/images';

    /**
     * AuthService constructor.
     *
     * @param  UserRolePermissionService  $userRolePermissionService  Service for user roles and permissions.
     * @param  UploadService  $uploadService  Service responsible for handling file uploads and deletions.
     */
    public function __construct(
        private readonly UserRolePermissionService $userRolePermissionService,
        private readonly UploadService $uploadService
    ) {}

    /**
     * Authenticate a user and generate a Sanctum token.
     *
     * @param  array<string, mixed>  $credentials
     * @return array<string, mixed>
     *
     * @throws HttpException When credentials are invalid, email unverified, or account deactivated.
     */
    public function login(array $credentials): array
    {
        $loginField = $credentials['identifier'];
        $fieldType = filter_var($loginField, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (! Auth::attempt([$fieldType => $loginField, 'password' => $credentials['password']])) {
            throw new HttpException(401, 'The provided credentials are incorrect.');
        }

        $user = Auth::user();

        if (! $user->hasVerifiedEmail()) {
            Auth::logout();

            try {
                $this->sendEmailVerification($user);
            } catch (Exception $e) {
                Log::error('Failed to resend verification email during login: '.$e->getMessage());
            }

            throw new HttpException(403, 'Please verify your email address before logging in. A verification email has been sent.');
        }

        if (! $user->isActive()) {
            Auth::logout();
            throw new HttpException(403, 'Your account has been deactivated. Please contact the administrator.');
        }

        $user->load(['roles', 'permissions']);

        return [
            'user' => $user,
            'token' => $user->createToken('auth-token')->plainTextToken,
        ];
    }

    /**
     * Logout the authenticated user (revoke current token).
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }

    /**
     * Send email verification notification.
     *
     * @throws HttpException
     */
    public function sendEmailVerification(User $user): void
    {
        if ($user->hasVerifiedEmail()) {
            throw new HttpException(400, 'Email is already verified.');
        }

        $mailSetting = MailSetting::default()->first();
        if (! $mailSetting) {
            throw new HttpException(500, 'Mail settings are not configured. Please contact support.');
        }

        $this->setMailInfo($mailSetting);
        $generalSetting = GeneralSetting::latest()->first();

        Mail::to($user->email)->send(new EmailVerification($user, $generalSetting));
    }

    /**
     * Register a new user.
     */
    public function register(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $data = $this->handleUploads($data);

            $user = User::query()->create([
                'name' => $data['name'],
                'username' => $data['username'] ?? null,
                'email' => $data['email'] ?? null,
                'image' => $data['image'] ?? null,
                'image_url' => $data['image_url'] ?? null,
                'phone' => $data['phone'] ?? null,
                'company_name' => $data['company_name'] ?? null,
                'biller_id' => $data['biller_id'] ?? null,
                'warehouse_id' => $data['warehouse_id'] ?? null,
                'is_active' => false,
                'password' => Hash::make($data['password']),
            ]);

            // Assuming a role name or ID is passed in the request data to assign here
            if (isset($data['role'])) {
                $user->assignRole($data['role']);
            }

            // Now checking the role works as expected
            if ($user->hasRole('Customer') || (isset($data['role']) && $data['role'] === 'Customer')) {
                Customer::query()->create([
                    'name' => $data['customer_name'] ?? $data['name'],
                    'user_id' => $user->id,
                    'customer_group_id' => $data['customer_group_id'] ?? null,
                    'email' => $data['email'] ?? null,
                    'phone_number' => $data['phone_number'] ?? null,
                    'company_name' => $data['company_name'] ?? null,
                    'is_active' => true,
                ]);
            }

            if ($user->email) {
                try {
                    $this->sendEmailVerification($user);
                } catch (Exception $e) {
                    Log::error('Failed to send verification email: '.$e->getMessage());
                }
            }

            return $user;
        });
    }

    /**
     * Handle Avatar Upload via UploadService.
     */
    private function handleUploads(array $data, ?User $user = null): array
    {
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            if ($user?->image) {
                $this->uploadService->delete($user->image);
            }
            $path = $this->uploadService->upload($data['image'], config('storage.users.images', self::AVATARS_IMAGE_PATH));
            $data['image'] = $path;
            $data['image_url'] = $this->uploadService->url($path);
        }

        return $data;
    }

    /**
     * Logout from all devices (revoke all tokens).
     */
    public function logoutAll(User $user): void
    {
        $user->tokens()->delete();
    }

    /**
     * Send password reset link to user's email.
     *
     * @throws HttpException
     */
    public function sendPasswordResetLink(array $data): string
    {
        $user = User::where('email', $data['email'])->first();

        if (! $user) {
            throw new HttpException(404, 'We can\'t find a user with that email address.');
        }

        $mailSetting = MailSetting::default()->first();
        if (! $mailSetting) {
            throw new HttpException(500, 'Mail settings are not configured. Please contact support.');
        }

        $this->setMailInfo($mailSetting);
        $token = Password::createToken($user);
        $generalSetting = GeneralSetting::latest()->first();

        Mail::to($user->email)->send(new PasswordResetMail($user, $token, $generalSetting));

        return 'We have emailed your password reset link.';
    }

    /**
     * Reset user password using token.
     *
     * @throws HttpException
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
            throw new HttpException(400, __($status));
        }

        return __($status);
    }

    /**
     * Get the authenticated user with relationships.
     */
    public function getAuthenticatedUser(User $user): User
    {
        return $user->load(['biller', 'warehouse', 'roles', 'permissions']);
    }

    /**
     * Verify user's email address.
     *
     * @throws HttpException
     */
    public function verifyEmail(User $user, string $hash): bool
    {
        if ($user->hasVerifiedEmail()) {
            throw new HttpException(400, 'Email is already verified.');
        }

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            throw new HttpException(400, 'Invalid verification link.');
        }

        if ($user->markEmailAsVerified()) {
            $user->update(['is_active' => true]);

            return true;
        }

        throw new HttpException(500, 'An unexpected error occurred while verifying your email.');
    }

    /**
     * Resend email verification notification.
     */
    public function resendEmailVerification(User $user): void
    {
        $this->sendEmailVerification($user);
    }

    /**
     * Refresh the authentication token for the user.
     */
    public function refreshToken(User $user, bool $revokeOldToken = false): array
    {
        if ($revokeOldToken) {
            $user->currentAccessToken()?->delete();
        }

        $user->load(['roles', 'permissions']);

        return [
            'user' => $user,
            'token' => $user->createToken('auth-token')->plainTextToken,
        ];
    }

    /**
     * Update user profile information.
     */
    public function updateProfile(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            $data = $this->handleUploads($data, $user);

            $allowedFields = ['name', 'username', 'email', 'image', 'image_url', 'phone', 'company_name'];
            $updateData = array_intersect_key($data, array_flip($allowedFields));

            $user->update($updateData);

            return $user->fresh();
        });
    }

    /**
     * Change user password.
     */
    public function changePassword(User $user, string $newPassword): void
    {
        DB::transaction(function () use ($user, $newPassword) {
            $user->update(['password' => Hash::make($newPassword)]);
        });
    }

    /**
     * Verify the user's password for lock screen unlock.
     *
     * @throws HttpException
     */
    public function unlock(User $user, string $password): bool
    {
        if (! Hash::check($password, $user->password)) {
            throw new HttpException(401, 'The provided password is incorrect.');
        }

        return true;
    }
}
