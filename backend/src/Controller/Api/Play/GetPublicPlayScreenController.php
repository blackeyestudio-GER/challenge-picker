<?php

namespace App\Controller\Api\Play;

use App\DTO\Response\Play\PlayScreenResponse;
use App\Repository\PlaythroughRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GetPublicPlayScreenController extends AbstractController
{
    public function __construct(
        private readonly PlaythroughRepository $playthroughRepository
    ) {
    }

    #[Route('/api/play/{uuid}', name: 'api_play_screen_public', methods: ['GET'])]
    public function __invoke(string $uuid): JsonResponse
    {
        // Public endpoint - no authentication required
        $playthrough = $this->playthroughRepository->findByUuidWithRelations($uuid);

        if (!$playthrough) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'PLAYTHROUGH_NOT_FOUND',
                    'message' => 'Playthrough session not found',
                ],
            ], Response::HTTP_NOT_FOUND);
        }

        $response = PlayScreenResponse::fromPlaythrough($playthrough);

        return $this->json($response, Response::HTTP_OK);
    }
}
