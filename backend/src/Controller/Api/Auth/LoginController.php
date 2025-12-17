<?php

namespace App\Controller\Api\Auth;

use App\DTO\Request\Auth\LoginRequest;
use App\Service\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/auth/login', name: 'api_auth_login', methods: ['POST'])]
class LoginController extends AbstractController
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    /**
     * Authenticate user and return JWT token
     * 
     * @param LoginRequest $request Validated login credentials
     * @return JsonResponse JWT token and user data
     */
    public function __invoke(
        #[MapRequestPayload] LoginRequest $request
    ): JsonResponse {
        try {
            // Authenticate and generate token
            $response = $this->authService->login($request);

            return $this->json([
                'success' => true,
                'data' => $response,
                'message' => 'Login successful'
            ], Response::HTTP_OK);
            
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'AUTHENTICATION_FAILED',
                    'message' => $e->getMessage()
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }
    }
}

