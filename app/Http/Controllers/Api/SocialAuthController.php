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
     * Get Google OAuth redirect URL.
     *
     * @return JsonResponse
     */
    public function redirectToGoogle(): JsonResponse
    {
        $url = $this->service->redirectToProvider('google');
        return response()->success(['url' => $url], 'OAuth URL generated successfully');
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
     * Get Facebook OAuth redirect URL.
     *
     * @return JsonResponse
     */
    public function redirectToFacebook(): JsonResponse
    {
        $url = $this->service->redirectToProvider('facebook');
        return response()->success(['url' => $url], 'OAuth URL generated successfully');
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

    /**
     * Get GitHub OAuth redirect URL.
     *
     * @return JsonResponse
     */
    public function redirectToGithub(): JsonResponse
    {
        $url = $this->service->redirectToProvider('github');
        return response()->success(['url' => $url], 'OAuth URL generated successfully');
    }

    /**
     * Handle GitHub OAuth callback.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function handleGithubCallback(Request $request): JsonResponse
    {
        $result = $this->service->handleProviderCallback('github');

        return response()->success([
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
        ], 'Login successful');
    }
}