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
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class AuthService
 * * Handles all core business logic and database interactions for Authentication.
 * Acts as the intermediary between the controllers and the database layer.
 */
class AuthService
{
    use MailInfo;

    /**
     * The storage path for user avatar uploads.
     */
    private const AVATARS_IMAGE_PATH = 'images/users/avatars';

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
     * Validates credentials (email or username + password), ensures email is verified and account is active.
     *
     * @param  array<string, mixed>  $credentials  Contains 'identifier' (email or username) and 'password'.
     * @return array<string, mixed> Array with 'user' and 'token' keys.
     *
     * @throws HttpException When credentials are invalid or account is deactivated.
     * @throws ValidationException When email is not verified.
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

            throw ValidationException::withMessages([
                'email' => ['Please verify your email address before logging in. A verification email has been sent.'],
            ]);
        }

        if (! $user->isActive() || $user->isDeleted()) {
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
     *
     * @param  User  $user  The authenticated user whose current token should be revoked.
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }

    /**
     * Register a new user.
     *
     * Processes avatar upload if provided, creates the user and optionally a Customer record,
     * and sends email verification within a database transaction.
     *
     * @param  array<string, mixed>  $data  The validated request data for the new user.
     * @return User The newly created User model instance.
     */
    public function register(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $data = $this->handleUploads($data);

            $user = User::query()->create([
                'name' => $data['name'],
                'username' => $data['username'] ?? null,
                'email' => $data['email'] ?? null,
                'avatar' => $data['avatar'] ?? null,
                'avatar_url' => $data['avatar_url'] ?? null,
                'phone' => $data['phone'] ?? null,
                'company_name' => $data['company_name'] ?? null,
                'biller_id' => $data['biller_id'] ?? null,
                'warehouse_id' => $data['warehouse_id'] ?? null,
                'is_active' => false,
                'is_deleted' => false,
                'password' => Hash::make($data['password']),
            ]);

            if ($user->hasRole('Customer')) {
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
     *
     * Checks if an avatar file is present in the data array. If so, it deletes the
     * old avatar (if updating) and uploads the new one, injecting the paths into the data array.
     *
     * @param  array<string, mixed>  $data  The input data potentially containing an 'avatar' file.
     * @param  User|null  $user  The existing user model if performing an update.
     * @return array<string, mixed> The modified data array with uploaded file paths.
     */
    private function handleUploads(array $data, ?User $user = null): array
    {
        if (isset($data['avatar']) && $data['avatar'] instanceof UploadedFile) {
            if ($user?->avatar) {
                $this->uploadService->delete($user->avatar);
            }
            $path = $this->uploadService->upload($data['avatar'], config('storage.users.avatars', self::AVATARS_IMAGE_PATH));
            $data['avatar'] = $path;
            $data['avatar_url'] = $this->uploadService->url($path);
        }

        return $data;
    }

    /**
     * Send email verification notification.
     *
     * @param  User  $user  The user to send the verification email to.
     *
     * @throws \Illuminate\Validation\ValidationException When email is already verified or mail settings are missing.
     */
    public function sendEmailVerification(User $user): void
    {
        if ($user->hasVerifiedEmail()) {
            throw ValidationException::withMessages(['email' => ['Email is already verified.']]);
        }

        $mailSetting = MailSetting::default()->first();
        if (! $mailSetting) {
            throw ValidationException::withMessages(['email' => ['Mail settings are not configured.']]);
        }

        $this->setMailInfo($mailSetting);
        $generalSetting = GeneralSetting::latest()->first();

        Mail::to($user->email)->send(new EmailVerification($user, $generalSetting));
    }

    /**
     * Logout from all devices (revoke all tokens).
     *
     * @param  User  $user  The authenticated user whose tokens should be revoked.
     */
    public function logoutAll(User $user): void
    {
        $user->tokens()->delete();
    }

    /**
     * Send password reset link to user's email.
     *
     * @param  array<string, mixed>  $data  The request data containing 'email'.
     * @return string Success message.
     *
     * @throws \Illuminate\Validation\ValidationException When user not found or mail settings missing.
     */
    public function sendPasswordResetLink(array $data): string
    {
        $user = User::where('email', $data['email'])->first();

        if (! $user) {
            throw ValidationException::withMessages(['email' => ['We can\'t find a user with that email address.']]);
        }

        $mailSetting = MailSetting::default()->first();
        if (! $mailSetting) {
            throw ValidationException::withMessages(['email' => ['Mail settings are not configured.']]);
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
     * @param  array<string, mixed>  $data  The request data (email, token, password, password_confirmation).
     * @return string Success message from Password facade.
     *
     * @throws \Illuminate\Validation\ValidationException When token is invalid or reset fails.
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
            throw ValidationException::withMessages(['email' => [__($status)]]);
        }

        return __($status);
    }

    /**
     * Get the authenticated user with relationships.
     *
     * @param  User  $user  The authenticated user model.
     * @return User The user with biller, warehouse, roles, and permissions loaded.
     */
    public function getAuthenticatedUser(User $user): User
    {
        return $user->load(['biller', 'warehouse', 'roles', 'permissions']);
    }

    /**
     * Verify user's email address.
     *
     * @param  User  $user  The user whose email is being verified.
     * @param  string  $hash  The verification hash from the link.
     * @return bool True if verification succeeded.
     *
     * @throws \Illuminate\Validation\ValidationException When already verified, hash invalid, or verification fails.
     */
    public function verifyEmail(User $user, string $hash): bool
    {
        if ($user->hasVerifiedEmail()) {
            throw ValidationException::withMessages(['email' => ['Email is already verified.']]);
        }

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            throw ValidationException::withMessages(['email' => ['Invalid verification link.']]);
        }

        if ($user->markEmailAsVerified()) {
            $user->update(['is_active' => true]);

            return true;
        }

        throw ValidationException::withMessages(['email' => ['Failed to verify email.']]);
    }

    /**
     * Resend email verification notification.
     *
     * @param  User  $user  The user to resend the verification email to.
     */
    public function resendEmailVerification(User $user): void
    {
        $this->sendEmailVerification($user);
    }

    /**
     * Refresh the authentication token for the user.
     *
     * @param  User  $user  The authenticated user.
     * @param  bool  $revokeOldToken  Whether to revoke the current token before issuing a new one.
     * @return array<string, mixed> Array with 'user' and 'token' keys.
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
     *
     * Processes avatar upload if provided and updates allowed profile fields within a transaction.
     *
     * @param  User  $user  The authenticated user to update.
     * @param  array<string, mixed>  $data  The validated update data.
     * @return User The freshly updated User model instance.
     */
    public function updateProfile(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            $data = $this->handleUploads($data, $user);

            $allowedFields = ['name', 'username', 'email', 'avatar', 'avatar_url', 'phone', 'company_name'];
            $updateData = array_intersect_key($data, array_flip($allowedFields));

            $user->update($updateData);

            return $user->fresh();
        });
    }

    /**
     * Change user password.
     *
     * @param  User  $user  The authenticated user.
     * @param  string  $newPassword  The new hashed password will be stored.
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
     * @param  User  $user  The authenticated user.
     * @param  string  $password  The password to verify.
     * @return bool True if the password matches.
     *
     * @throws \Illuminate\Validation\ValidationException When password is incorrect.
     */
    public function unlock(User $user, string $password): bool
    {
        if (! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages(['password' => ['The provided password is incorrect.']]);
        }

        return true;
    }
}
