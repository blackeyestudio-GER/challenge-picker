<?php

namespace App\Controller\Api\User;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class DisconnectDiscordController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/api/user/disconnect/discord', name: 'api_user_disconnect_discord', methods: ['POST'])]
    public function __invoke(#[CurrentUser] ?User $user = null): JsonResponse
    {
        if (!$user) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'User must be logged in',
                ],
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (!$user->hasDiscordConnected()) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'DISCORD_NOT_CONNECTED',
                    'message' => 'Discord is not connected',
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        // Remove Discord connection
        $user->setDiscordId(null);
        $user->setDiscordUsername(null);
        $user->setDiscordAvatar(null);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Discord disconnected successfully',
        ], Response::HTTP_OK);
    }
}
