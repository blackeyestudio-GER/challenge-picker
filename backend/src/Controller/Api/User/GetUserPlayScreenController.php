<?php

namespace App\Controller\Api\User;

use App\DTO\Response\Play\PlayScreenResponse;
use App\Repository\PlaythroughRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class GetUserPlayScreenController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly PlaythroughRepository $playthroughRepository,
    ) {
    }

    #[Route('/api/user/{uuid}/play-screen', name: 'get_user_play_screen', methods: ['GET'])]
    public function __invoke(string $uuid): JsonResponse
    {
        // Find user by UUID
        $user = $this->userRepository->findOneBy(['uuid' => $uuid]);

        if (!$user) {
            return new JsonResponse([
                'success' => false,
                'error' => ['message' => 'User not found'],
            ], 404);
        }

        // Find user's active playthrough (setup, active, or paused)
        $playthrough = $this->playthroughRepository->findOneBy([
            'user' => $user,
            'status' => ['setup', 'active', 'paused'],
        ], ['createdAt' => 'DESC']);

        if (!$playthrough) {
            return new JsonResponse([
                'success' => false,
                'error' => ['message' => 'No active game session'],
            ], 404);
        }

        return new JsonResponse(PlayScreenResponse::fromPlaythrough($playthrough), 200);
    }
}
