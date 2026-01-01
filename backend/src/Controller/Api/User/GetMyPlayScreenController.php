<?php

namespace App\Controller\Api\User;

use App\DTO\Response\Play\PlayScreenResponse;
use App\Repository\PlaythroughRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/users/me/play-screen', name: 'api_user_my_play_screen', methods: ['GET'])]
#[IsGranted('ROLE_USER')]
class GetMyPlayScreenController extends AbstractController
{
    public function __construct(
        private readonly PlaythroughRepository $playthroughRepository
    ) {
    }

    public function __invoke(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Authentication required',
                ],
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Find user's active playthrough (setup, active, or paused)
        $playthrough = $this->playthroughRepository->findActiveByUser($user);

        if (!$playthrough) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'NO_ACTIVE_PLAYTHROUGH',
                    'message' => 'No active playthrough found',
                ],
            ], Response::HTTP_NOT_FOUND);
        }

        $response = PlayScreenResponse::fromPlaythrough($playthrough);

        return $this->json($response, Response::HTTP_OK);
    }
}
