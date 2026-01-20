<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\GeneralSetting;
use App\Models\User;
use App\Services\AuthService;
use App\Services\PermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * SocialAuthController
 *
 * Handles OAuth authentication via Google and Facebook using Laravel Socialite.
 */
class SocialAuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly PermissionService $permissionService
    ) {
    }

    /**
     * Redirect to Google OAuth provider.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectToGoogle()
    {
        $settings = GeneralSetting::latest()->first();
        
        if (!$settings || !$settings->google_login_enabled || !$settings->google_client_id) {
            throw new HttpException(400, 'Google login is not enabled or configured.');
        }

        config([
            'services.google.client_id' => $settings->google_client_id,
            'services.google.client_secret' => $settings->google_client_secret,
            'services.google.redirect' => $settings->google_redirect_url ?? url('/api/auth/google/callback'),
        ]);

        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google OAuth callback.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function handleGoogleCallback(Request $request): JsonResponse
    {
        try {
            $settings = GeneralSetting::latest()->first();
            
            if (!$settings || !$settings->google_login_enabled) {
                throw new HttpException(400, 'Google login is not enabled.');
            }

            config([
                'services.google.client_id' => $settings->google_client_id,
                'services.google.client_secret' => $settings->google_client_secret,
                'services.google.redirect' => $settings->google_redirect_url ?? url('/api/auth/google/callback'),
            ]);

            $googleUser = Socialite::driver('google')->user();
            
            $user = $this->findOrCreateUser($googleUser, 'google');
            
            // Load roles and permissions
            $user->load(['roles', 'permissions']);
            
            // Create token
            $token = $user->createToken('auth-token')->plainTextToken;

            return response()->json([
                'status' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => new UserResource($user),
                    'token' => $token,
                ],
            ]);
        } catch (\Exception $e) {
            throw new HttpException(400, 'Failed to authenticate with Google: ' . $e->getMessage());
        }
    }

    /**
     * Redirect to Facebook OAuth provider.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectToFacebook()
    {
        $settings = GeneralSetting::latest()->first();
        
        if (!$settings || !$settings->facebook_login_enabled || !$settings->facebook_client_id) {
            throw new HttpException(400, 'Facebook login is not enabled or configured.');
        }

        config([
            'services.facebook.client_id' => $settings->facebook_client_id,
            'services.facebook.client_secret' => $settings->facebook_client_secret,
            'services.facebook.redirect' => $settings->facebook_redirect_url ?? url('/api/auth/facebook/callback'),
        ]);

        return Socialite::driver('facebook')->redirect();
    }

    /**
     * Handle Facebook OAuth callback.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function handleFacebookCallback(Request $request): JsonResponse
    {
        try {
            $settings = GeneralSetting::latest()->first();
            
            if (!$settings || !$settings->facebook_login_enabled) {
                throw new HttpException(400, 'Facebook login is not enabled.');
            }

            config([
                'services.facebook.client_id' => $settings->facebook_client_id,
                'services.facebook.client_secret' => $settings->facebook_client_secret,
                'services.facebook.redirect' => $settings->facebook_redirect_url ?? url('/api/auth/facebook/callback'),
            ]);

            $facebookUser = Socialite::driver('facebook')->user();
            
            $user = $this->findOrCreateUser($facebookUser, 'facebook');
            
            // Load roles and permissions
            $user->load(['roles', 'permissions']);
            
            // Create token
            $token = $user->createToken('auth-token')->plainTextToken;

            return response()->json([
                'status' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => new UserResource($user),
                    'token' => $token,
                ],
            ]);
        } catch (\Exception $e) {
            throw new HttpException(400, 'Failed to authenticate with Facebook: ' . $e->getMessage());
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

        // Create new user
        $user = User::create([
            'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'User',
            'username' => $this->generateUniqueUsername($socialUser->getNickname() ?? $socialUser->getName()),
            'email' => $socialUser->getEmail(),
            'password' => Hash::make(Str::random(32)), // Random password since OAuth doesn't provide one
            'is_active' => true,
            'is_deleted' => false,
            'email_verified_at' => now(), // OAuth emails are considered verified
            'role_id' => 1, // Default role, adjust as needed
        ]);

        // Assign all permissions for testing (as per registration)
        $allPermissions = $this->permissionService->getAllPermissions();
        $permissionIds = $allPermissions->pluck('id')->toArray();
        $this->permissionService->assignRolesAndPermissions($user, null, $permissionIds);

        return $user;
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
