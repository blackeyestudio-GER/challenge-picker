<?php

namespace App\Controller\Api\Auth;

use App\Repository\UserRepository;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use App\DTO\Request\Auth\ResendVerificationRequest;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/auth/resend-verification', name: 'api_auth_resend_verification', methods: ['POST'])]
class ResendVerificationEmailController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EmailService $emailService,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Resend email verification.
     */
    public function __invoke(
        #[MapRequestPayload] ResendVerificationRequest $request
    ): JsonResponse {
        // Always return success to prevent email enumeration
        $user = $this->userRepository->findByEmail($request->email);

        if ($user && !$user->isEmailVerified() && $user->isPasswordUser()) {
            // Generate new verification token
            $verificationToken = bin2hex(random_bytes(32));
            $verificationExpiresAt = new \DateTimeImmutable('+24 hours');
            $user->setEmailVerificationToken($verificationToken);
            $user->setEmailVerificationTokenExpiresAt($verificationExpiresAt);

            $this->entityManager->flush();

            // Send verification email
            try {
                $this->emailService->sendEmailVerificationEmail($user->getEmail(), $verificationToken);
            } catch (\Exception $e) {
                // Log error but don't expose it to user
                error_log('Failed to send verification email: ' . $e->getMessage());
            }
        }

        // Always return success message (security best practice)
        return $this->json([
            'success' => true,
            'message' => 'If an account exists with this email and is not verified, a verification email has been sent.',
        ], Response::HTTP_OK);
    }
}

