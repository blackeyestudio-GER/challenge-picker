<?php

namespace App\Controller\Api\Challenge;

use App\Repository\PlaythroughRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/api/challenges/{uuid}/details', name: 'api_challenge_details', methods: ['GET'])]
class GetChallengeDetailsController extends AbstractController
{
    public function __construct(
        private readonly PlaythroughRepository $playthroughRepository
    ) {
    }

    public function __invoke(string $uuid): JsonResponse
    {
        // Find the playthrough
        $playthroughUuid = Uuid::fromString($uuid);
        $playthrough = $this->playthroughRepository->findOneBy(['uuid' => $playthroughUuid]);

        if (!$playthrough) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'PLAYTHROUGH_NOT_FOUND',
                    'message' => 'Challenge not found',
                ],
            ], Response::HTTP_NOT_FOUND);
        }

        $ruleset = $playthrough->getRuleset();
        $games = $ruleset->getGames();
        $game = $games->isEmpty() ? null : $games->first();
        $host = $playthrough->getUser();

        if (!$game) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'NO_GAME',
                    'message' => 'Ruleset has no associated game',
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json([
            'success' => true,
            'data' => [
                'playthroughUuid' => $playthrough->getUuid()->toRfc4122(),
                'hostUsername' => $host->getUsername(),
                'game' => [
                    'id' => $game->getId(),
                    'name' => $game->getName(),
                    'imageBase64' => $game->getImageBase64(),
                ],
                'ruleset' => [
                    'id' => $ruleset->getId(),
                    'name' => $ruleset->getName(),
                    'description' => $ruleset->getDescription(),
                    'difficulty' => $ruleset->getDifficulty(),
                ],
                'maxConcurrentRules' => $playthrough->getMaxConcurrentRules(),
                'requireAuth' => $playthrough->isRequireAuth(),
                'allowViewerPicks' => $playthrough->isAllowViewerPicks(),
            ],
        ], Response::HTTP_OK);
    }
}
