<?php

namespace App\Controller\Api\Playthrough;

use App\DTO\Response\Playthrough\PlaythroughResponse;
use App\Repository\PlaythroughRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        $playthroughs = $this->playthroughRepository->findBy(
            ['user' => $user, 'status' => 'completed'],
            ['endedAt' => 'DESC']
        );

        $data = array_map(fn ($p) => PlaythroughResponse::fromEntity($p), $playthroughs);

        return $this->json([
            'success' => true,
            'data' => ['playthroughs' => $data],
        ]);
    }
}
