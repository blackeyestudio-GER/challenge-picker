<?php

namespace App\Controller\Api\Auth;

use App\Service\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/auth/refresh', name: 'api_auth_refresh', methods: ['POST'])]
class RefreshTokenController extends AbstractController
{
    public function __construct(
        private readonly AuthService $authService
    ) {
    }

    /**
     * Refresh JWT token using a refresh token.
     *
     * @return JsonResponse New JWT token and refresh token
     */
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!is_array($data)) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'INVALID_REQUEST',
                        'message' => 'Invalid request body',
                    ],
                ], Response::HTTP_BAD_REQUEST);
            }

            $refreshToken = $data['refreshToken'] ?? null;

            if (!is_string($refreshToken) || $refreshToken === '') {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'MISSING_REFRESH_TOKEN',
                        'message' => 'Refresh token is required',
                    ],
                ], Response::HTTP_BAD_REQUEST);
            }

            // Generate new JWT and refresh token
            $response = $this->authService->refreshToken($refreshToken);

            return $this->json([
                'success' => true,
                'data' => $response,
                'message' => 'Token refreshed successfully',
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'REFRESH_FAILED',
                    'message' => $e->getMessage(),
                ],
            ], Response::HTTP_UNAUTHORIZED);
        }
    }
}
