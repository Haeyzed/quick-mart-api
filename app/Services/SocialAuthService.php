<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\GeneralSetting;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * SocialAuthService
 *
 * Handles OAuth authentication business logic via Google and Facebook using Laravel Socialite.
 */
class SocialAuthService extends BaseService
{
    public function __construct(
        private readonly UserRolePermissionService $userRolePermissionService
    )
    {
    }

    /**
     * Redirect to OAuth provider.
     *
     * @param string $provider Provider name ('google', 'facebook', or 'github')
     * @return string Redirect URL
     * @throws HttpException
     */
    public function redirectToProvider(string $provider): string
    {
        $this->configureSocialite($provider);

        return Socialite::driver($provider)->stateless()->redirect()->getTargetUrl();
    }

    /**
     * Configure Socialite for a provider.
     *
     * @param string $provider Provider name ('google' or 'facebook')
     * @return void
     * @throws HttpException
     */
    public function configureSocialite(string $provider): void
    {
        $config = $this->getOAuthConfig($provider);

        config([
            "services.{$provider}.client_id" => $config['client_id'],
            "services.{$provider}.client_secret" => $config['client_secret'],
            "services.{$provider}.redirect" => $config['redirect'],
        ]);
    }

    /**
     * Get OAuth configuration for a provider.
     *
     * @param string $provider Provider name ('google' or 'facebook')
     * @return array<string, string>
     * @throws HttpException
     */
    public function getOAuthConfig(string $provider): array
    {
        $settings = GeneralSetting::latest()->first();

        if (!$settings) {
            throw new HttpException(400, ucfirst($provider) . ' login is not configured.');
        }

        $enabledField = $provider . '_login_enabled';
        $clientIdField = $provider . '_client_id';
        $clientSecretField = $provider . '_client_secret';
        $redirectUrlField = $provider . '_redirect_url';

        if (!$settings->$enabledField || !$settings->$clientIdField) {
            throw new HttpException(400, ucfirst($provider) . ' login is not enabled or configured.');
        }

        // Default redirect URL should point to frontend callback page
        $frontendUrl = config('app.frontend_url', 'http://localhost:3000');
        $redirectUrl = $settings->$redirectUrlField ?? "{$frontendUrl}/auth/callback/{$provider}";

        return [
            'client_id' => $settings->$clientIdField,
            'client_secret' => $settings->$clientSecretField,
            'redirect' => $redirectUrl,
        ];
    }

    /**
     * Handle OAuth callback and authenticate user.
     *
     * @param string $provider Provider name ('google' or 'facebook')
     * @return array<string, mixed>
     * @throws HttpException
     */
    public function handleProviderCallback(string $provider): array
    {
        try {
            // Verify provider is enabled
            $this->getOAuthConfig($provider);

            // Configure Socialite
            $this->configureSocialite($provider);

            // Get user from provider
            $socialUser = Socialite::driver($provider)->stateless()->user();

            // Find or create user
            $user = $this->findOrCreateUser($socialUser, $provider);

            // Load roles and permissions
            $user->load(['roles', 'permissions']);

            // Create token
            $token = $user->createToken('auth-token')->plainTextToken;

            return [
                'user' => $user,
                'token' => $token,
            ];
        } catch (Exception $e) {
            throw new HttpException(400, "Failed to authenticate with {$provider}: " . $e->getMessage());
        }
    }

    /**
     * Find or create a user from social provider.
     *
     * @param \Laravel\Socialite\Contracts\User $socialUser
     * @param string $provider
     * @return User
     */
    private function findOrCreateUser($socialUser, string $provider): User
    {
        return $this->transaction(function () use ($socialUser, $provider) {
            // Try to find user by email
            $user = User::where('email', $socialUser->getEmail())->first();

            if ($user) {
                // Update email verification if not already verified
                if (!$user->hasVerifiedEmail() && $socialUser->getEmail()) {
                    $user->markEmailAsVerified();
                }

                // Activate user if not active
                if (!$user->isActive()) {
                    $user->is_active = true;
                    $user->save();
                }

                return $user;
            }

            // Get avatar URL from social provider (store directly, no download needed)
            $avatarUrl = $socialUser->getAvatar();

            // Create new user
            $user = User::create([
                'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'User',
                'username' => $this->generateUniqueUsername($socialUser->getNickname() ?? $socialUser->getName()),
                'email' => $socialUser->getEmail(),
                'avatar' => null, // No local path for social provider avatars
                'avatar_url' => $avatarUrl, // Store provider URL directly
                'password' => Hash::make(Str::random(32)), // Random password since OAuth doesn't provide one
                'is_active' => true,
                'is_deleted' => false,
                'email_verified_at' => now(), // OAuth emails are considered verified
            ]);

            // Assign default role (Admin) and permissions
            $defaultRoleName = 'Admin';
            $allPermissions = $this->userRolePermissionService->getAllPermissions();
            $permissionIds = $allPermissions->pluck('id')->toArray();
            $this->userRolePermissionService->assignRolesAndPermissions($user, [$defaultRoleName], $permissionIds);

            return $user;
        });
    }

    /**
     * Generate a unique username from social provider data.
     *
     * @param string|null $baseUsername
     * @return string
     */
    private function generateUniqueUsername(?string $baseUsername): string
    {
        if (!$baseUsername) {
            $baseUsername = 'user_' . Str::random(8);
        }

        // Clean username (only alphanumeric, underscore, hyphen)
        $username = preg_replace('/[^a-zA-Z0-9_-]/', '', Str::slug($baseUsername));

        // Ensure it's not empty
        if (empty($username)) {
            $username = 'user_' . Str::random(8);
        }

        // Check if username exists, append number if needed
        $originalUsername = $username;
        $counter = 1;
        while (User::where('username', $username)->exists()) {
            $username = $originalUsername . '_' . $counter;
            $counter++;
        }

        return $username;
    }
}
