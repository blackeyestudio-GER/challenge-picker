<?php

namespace App\Controller\Api\Auth;

use App\DTO\Request\Auth\RequestPasswordResetRequest;
use App\Repository\UserRepository;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/api/auth/password-reset/request', name: 'api_auth_password_reset_request', methods: ['POST'])]
class RequestPasswordResetController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EmailService $emailService,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Request password reset - sends email with reset token.
     */
    public function __invoke(
        #[MapRequestPayload] RequestPasswordResetRequest $request
    ): JsonResponse {
        // Always return success to prevent email enumeration
        // Find user by email
        $user = $this->userRepository->findByEmail($request->email);

        if ($user && $user->isPasswordUser()) {
            // Generate secure reset token
            $token = bin2hex(random_bytes(32));
            $expiresAt = new \DateTimeImmutable('+1 hour');

            $user->setPasswordResetToken($token);
            $user->setPasswordResetTokenExpiresAt($expiresAt);

            $this->entityManager->flush();

            // Send reset email
            try {
                $this->emailService->sendPasswordResetEmail($user->getEmail(), $token);
            } catch (\Exception $e) {
                // Log error but don't expose it to user
                error_log('Failed to send password reset email: ' . $e->getMessage());
            }
        }

        // Always return success message (security best practice)
        return $this->json([
            'success' => true,
            'message' => 'If an account exists with this email, a password reset link has been sent.',
        ], Response::HTTP_OK);
    }
}

