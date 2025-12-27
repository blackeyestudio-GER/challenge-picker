<?php

namespace App\Controller\Api\Ruleset;

use App\DTO\Response\Ruleset\RulesetListResponse;
use App\DTO\Response\Ruleset\RulesetResponse;
use App\Entity\User;
use App\Repository\GameRepository;
use App\Repository\RulesetRepository;
use App\Repository\RulesetVoteRepository;
use App\Repository\UserFavoriteRulesetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ListRulesetsController extends AbstractController
{
    public function __construct(
        private readonly RulesetRepository $rulesetRepository,
        private readonly GameRepository $gameRepository,
        private readonly UserFavoriteRulesetRepository $favoriteRepository,
        private readonly RulesetVoteRepository $voteRepository
    ) {
    }

    #[Route('/api/games/{gameId}/rulesets', name: 'api_rulesets_list', methods: ['GET'])]
    public function __invoke(int $gameId, #[CurrentUser] ?User $user = null): JsonResponse
    {
        // Verify game exists
        $game = $this->gameRepository->find($gameId);
        if (!$game) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'GAME_NOT_FOUND',
                    'message' => 'Game not found',
                ],
            ], Response::HTTP_NOT_FOUND);
        }

        // Find all rulesets assigned to this game
        $rulesets = $this->rulesetRepository->findByGame($gameId);

        // Get user's favorite ruleset IDs and vote info if user is authenticated
        $favoriteRulesetIds = [];
        $userVoteMap = [];
        if ($user) {
            $favoriteRulesetIds = $this->favoriteRepository->getFavoriteRulesetIds($user);
            $rulesetIds = array_map(fn ($ruleset) => $ruleset->getId(), $rulesets);
            $userVoteMap = $this->voteRepository->getUserVotesForRulesets($user, $rulesetIds);
        }

        $rulesetResponses = array_map(
            function ($ruleset) use ($favoriteRulesetIds, $userVoteMap) {
                $isFavorited = in_array($ruleset->getId(), $favoriteRulesetIds);
                $voteCount = $this->voteRepository->getVoteCount($ruleset);
                $userVoteType = $userVoteMap[$ruleset->getId()]['voteType'] ?? null;

                return RulesetResponse::fromEntity(
                    $ruleset,
                    $isFavorited,
                    $voteCount,
                    $userVoteType,
                    false, // isInherited - no longer relevant with many-to-many
                    null   // inheritedFromCategory - no longer relevant
                );
            },
            $rulesets
        );

        $response = RulesetListResponse::fromRulesets($rulesetResponses);

        return $this->json($response, Response::HTTP_OK);
    }
}
