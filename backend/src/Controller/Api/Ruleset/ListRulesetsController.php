<?php

namespace App\Controller\Api\Ruleset;

use App\DTO\Response\Ruleset\RulesetListResponse;
use App\DTO\Response\Ruleset\RulesetResponse;
use App\Repository\GameRepository;
use App\Repository\RulesetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ListRulesetsController extends AbstractController
{
    public function __construct(
        private readonly RulesetRepository $rulesetRepository,
        private readonly GameRepository $gameRepository
    ) {
    }

    #[Route('/api/games/{gameId}/rulesets', name: 'api_rulesets_list', methods: ['GET'])]
    public function __invoke(int $gameId): JsonResponse
    {
        // Verify game exists
        $game = $this->gameRepository->find($gameId);
        if (!$game) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'GAME_NOT_FOUND',
                    'message' => 'Game not found'
                ]
            ], Response::HTTP_NOT_FOUND);
        }

        $rulesets = $this->rulesetRepository->findByGame($gameId);

        $rulesetResponses = array_map(
            fn($ruleset) => RulesetResponse::fromEntity($ruleset),
            $rulesets
        );

        $response = RulesetListResponse::fromRulesets($rulesetResponses);

        return $this->json($response, Response::HTTP_OK);
    }
}

