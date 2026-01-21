<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Services\SocialAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * SocialAuthController
 *
 * Handles OAuth authentication via Google and Facebook using Laravel Socialite.
 * Delegates business logic to SocialAuthService.
 */
class SocialAuthController extends Controller
{
    public function __construct(
        private readonly SocialAuthService $service
    ) {
    }

    /**
     * Redirect to Google OAuth provider.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectToGoogle()
    {
        return $this->service->redirectToProvider('google');
    }

    /**
     * Handle Google OAuth callback.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function handleGoogleCallback(Request $request): JsonResponse
    {
        $result = $this->service->handleProviderCallback('google');

        return response()->success([
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
        ], 'Login successful');
    }

    /**
     * Redirect to Facebook OAuth provider.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectToFacebook()
    {
        return $this->service->redirectToProvider('facebook');
    }

    /**
     * Handle Facebook OAuth callback.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function handleFacebookCallback(Request $request): JsonResponse
    {
        $result = $this->service->handleProviderCallback('facebook');

        return response()->success([
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
        ], 'Login successful');
    }
}