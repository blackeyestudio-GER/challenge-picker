<?php

namespace App\Controller\Api\Challenge;

use App\DTO\Response\Challenge\ChallengeDetailsGameData;
use App\DTO\Response\Challenge\ChallengeDetailsResponse;
use App\DTO\Response\Challenge\ChallengeDetailsResponseData;
use App\DTO\Response\Challenge\ChallengeDetailsRulesetData;
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
        if ($ruleset === null) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'NO_RULESET',
                    'message' => 'Playthrough has no associated ruleset',
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

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

        $gameId = $game->getId();
        $gameName = $game->getName();
        $rulesetId = $ruleset->getId();
        $rulesetName = $ruleset->getName();

        if ($gameId === null || $gameName === null || $rulesetId === null || $rulesetName === null) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_CHALLENGE',
                    'message' => 'Challenge is missing required game or ruleset data',
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json(
            ChallengeDetailsResponse::fromData(
                new ChallengeDetailsResponseData(
                    playthroughUuid: $playthrough->getUuid()->toRfc4122(),
                    hostUsername: $host->getUsername(),
                    game: new ChallengeDetailsGameData(
                        id: $gameId,
                        name: $gameName,
                        imageBase64: $game->getImage()
                    ),
                    ruleset: new ChallengeDetailsRulesetData(
                        id: $rulesetId,
                        name: $rulesetName,
                        description: $ruleset->getDescription(),
                        difficulty: null
                    ),
                    maxConcurrentRules: $playthrough->getMaxConcurrentRules(),
                    requireAuth: $playthrough->isRequireAuth(),
                    allowViewerPicks: $playthrough->isAllowViewerPicks()
                )
            ),
            Response::HTTP_OK
        );
    }
}
