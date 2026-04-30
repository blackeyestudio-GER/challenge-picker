<?php

namespace App\Controller\Api\Playthrough;

use App\DTO\Response\Playthrough\ActivePlaythroughResponse;
use App\DTO\Response\Playthrough\PlaythroughResponse;
use App\Entity\User;
use App\Repository\PlaythroughRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GetActivePlaythroughController extends AbstractController
{
    public function __construct(
        private readonly PlaythroughRepository $playthroughRepository
    ) {
    }

    #[Route('/api/users/me/playthrough/active', name: 'api_user_active_playthrough', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        // Get authenticated user
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Authentication required',
                ],
            ], Response::HTTP_UNAUTHORIZED);
        }

        $activePlaythrough = $this->playthroughRepository->findActiveByUser($user);

        if (!$activePlaythrough) {
            return $this->json(ActivePlaythroughResponse::fromPlaythrough(null), Response::HTTP_OK);
        }

        return $this->json(
            ActivePlaythroughResponse::fromPlaythrough(PlaythroughResponse::fromEntity($activePlaythrough)),
            Response::HTTP_OK
        );
    }
}
