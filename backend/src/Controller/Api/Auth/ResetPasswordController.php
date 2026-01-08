<?php

namespace App\Controller\Api\Auth;

use App\DTO\Request\Auth\ResetPasswordRequest;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/auth/password-reset', name: 'api_auth_password_reset', methods: ['POST'])]
class ResetPasswordController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Reset password using token from email.
     */
    public function __invoke(
        #[MapRequestPayload] ResetPasswordRequest $request
    ): JsonResponse {
        // Find user by reset token
        $user = $this->userRepository->findByPasswordResetToken($request->token);

        if (!$user || !$user->isPasswordResetTokenValid()) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_TOKEN',
                    'message' => 'Invalid or expired reset token',
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        // Hash and set new password
        $hashedPassword = $this->passwordHasher->hashPassword($user, $request->password);
        $user->setPassword($hashedPassword);

        // Clear reset token
        $user->setPasswordResetToken(null);
        $user->setPasswordResetTokenExpiresAt(null);

        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Password has been reset successfully',
        ], Response::HTTP_OK);
    }
}

