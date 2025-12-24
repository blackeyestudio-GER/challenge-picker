<?php

namespace App\Controller\Api\User;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class DisconnectTwitchController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/api/user/disconnect/twitch', name: 'api_user_disconnect_twitch', methods: ['POST'])]
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

        if (!$user->hasTwitchConnected()) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'TWITCH_NOT_CONNECTED',
                    'message' => 'Twitch is not connected',
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        // Remove Twitch connection
        $user->setTwitchId(null);
        $user->setTwitchUsername(null);
        $user->setTwitchAvatar(null);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Twitch disconnected successfully',
        ], Response::HTTP_OK);
    }
}
