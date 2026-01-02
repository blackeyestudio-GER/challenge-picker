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
        // Public endpoint - authentication optional
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

        // Check if playthrough requires authentication
        if ($playthrough->isRequireAuth()) {
            $user = $this->getUser();
            if (!$user) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'AUTH_REQUIRED',
                        'message' => 'This session requires you to be logged in to view',
                    ],
                ], Response::HTTP_UNAUTHORIZED);
            }
        }

        $response = PlayScreenResponse::fromPlaythrough($playthrough);

        return $this->json($response, Response::HTTP_OK);
    }
}
