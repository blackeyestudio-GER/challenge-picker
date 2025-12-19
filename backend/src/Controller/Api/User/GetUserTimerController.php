<?php

namespace App\Controller\Api\User;

use App\Repository\UserRepository;
use App\Repository\PlaythroughRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class GetUserTimerController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly PlaythroughRepository $playthroughRepository,
    ) {}

    #[Route('/api/user/{uuid}/timer', name: 'get_user_timer', methods: ['GET'])]
    public function __invoke(string $uuid): JsonResponse
    {
        // Find user by UUID
        $user = $this->userRepository->findOneBy(['uuid' => $uuid]);

        if (!$user) {
            return new JsonResponse([
                'success' => false,
                'error' => ['message' => 'User not found']
            ], 404);
        }

        // Find user's active playthrough
        $playthrough = $this->playthroughRepository->findOneBy([
            'user' => $user,
            'status' => ['setup', 'active', 'paused']
        ], ['createdAt' => 'DESC']);

        if (!$playthrough) {
            return new JsonResponse([
                'success' => false,
                'error' => ['message' => 'No active game session']
            ], 404);
        }

        return new JsonResponse([
            'success' => true,
            'data' => [
                'startedAt' => $playthrough->getStartedAt()?->format('c'),
                'status' => $playthrough->getStatus(),
                'pausedAt' => $playthrough->getPausedAt()?->format('c'),
                'totalPausedDuration' => $playthrough->getTotalPausedDuration(),
            ]
        ], 200);
    }
}

