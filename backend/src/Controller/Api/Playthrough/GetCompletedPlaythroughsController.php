<?php

namespace App\Controller\Api\Playthrough;

use App\DTO\Response\Playthrough\CompletedPlaythroughsResponse;
use App\DTO\Response\Playthrough\PlaythroughResponse;
use App\Entity\User;
use App\Repository\PlaythroughRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/playthrough/completed', name: 'api_playthrough_completed', methods: ['GET'])]
class GetCompletedPlaythroughsController extends AbstractController
{
    public function __construct(
        private readonly PlaythroughRepository $playthroughRepository
    ) {
    }

    public function __invoke(): JsonResponse
    {
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

        $playthroughs = $this->playthroughRepository->findBy(
            ['user' => $user, 'status' => 'completed'],
            ['endedAt' => 'DESC']
        );

        $data = array_map(fn ($p) => PlaythroughResponse::fromEntity($p), $playthroughs);

        return $this->json(CompletedPlaythroughsResponse::fromPlaythroughs($data));
    }
}
