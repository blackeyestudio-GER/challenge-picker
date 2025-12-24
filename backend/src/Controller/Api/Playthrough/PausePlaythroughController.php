<?php

namespace App\Controller\Api\Playthrough;

use App\DTO\Response\Playthrough\PlaythroughResponse;
use App\Repository\PlaythroughRepository;
use App\Service\PlaythroughService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PausePlaythroughController extends AbstractController
{
    public function __construct(
        private readonly PlaythroughRepository $playthroughRepository,
        private readonly PlaythroughService $playthroughService
    ) {
    }

    #[Route('/api/playthroughs/{uuid}/pause', name: 'api_playthrough_pause', methods: ['PUT'])]
    public function __invoke(string $uuid): JsonResponse
    {
        // Get authenticated user
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

        $playthrough = $this->playthroughRepository->findOneBy(['uuid' => $uuid]);

        if (!$playthrough) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'PLAYTHROUGH_NOT_FOUND',
                    'message' => 'Playthrough not found',
                ],
            ], Response::HTTP_NOT_FOUND);
        }

        // Verify the playthrough belongs to the authenticated user
        if ($playthrough->getUser()->getId() !== $user->getId()) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'FORBIDDEN',
                    'message' => 'You do not have access to this playthrough',
                ],
            ], Response::HTTP_FORBIDDEN);
        }

        try {
            $playthrough = $this->playthroughService->pausePlaythrough($playthrough);

            $response = PlaythroughResponse::fromEntity($playthrough);

            return $this->json([
                'success' => true,
                'data' => $response,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'PAUSE_ERROR',
                    'message' => $e->getMessage(),
                ],
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
