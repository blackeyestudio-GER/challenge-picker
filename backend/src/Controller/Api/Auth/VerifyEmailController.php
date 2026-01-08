<?php

namespace App\Controller\Api\Auth;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/auth/verify-email', name: 'api_auth_verify_email', methods: ['GET', 'POST'])]
class VerifyEmailController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Verify email address using token.
     * Supports both GET (query param) and POST (body) for flexibility.
     */
    public function __invoke(Request $request): JsonResponse
    {
        // Try query param first (for GET requests), then request body (for POST)
        $token = $request->query->get('token');
        if (!$token) {
            $data = json_decode($request->getContent(), true);
            $token = $data['token'] ?? null;
        }

        if (!$token) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'MISSING_TOKEN',
                    'message' => 'Verification token is required',
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        // Find user by verification token
        $user = $this->userRepository->findByEmailVerificationToken($token);

        if (!$user || !$user->isEmailVerificationTokenValid()) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_TOKEN',
                    'message' => 'Invalid or expired verification token',
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        // Verify email
        $user->setEmailVerified(true);
        $user->setEmailVerificationToken(null);
        $user->setEmailVerificationTokenExpiresAt(null);

        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Email verified successfully',
        ], Response::HTTP_OK);
    }
}

