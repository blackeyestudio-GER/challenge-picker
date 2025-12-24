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

        $rulesets = $this->rulesetRepository->findByGame($gameId);

        // Get user's favorite ruleset IDs and vote info if user is authenticated
        $favoriteRulesetIds = [];
        $userVoteMap = [];
        if ($user) {
            $favoriteRulesetIds = $this->favoriteRepository->getFavoriteRulesetIds($user);
            $rulesetIds = array_map(fn ($ruleset) => $ruleset->getId(), $rulesets);
            $userVoteMap = $this->voteRepository->getUserVotesForRulesets($user, $rulesetIds);
        }

        // Get category names for inherited rulesets
        $categoryMap = $this->getCategoryMapForInheritedRulesets($gameId, $rulesets);

        $rulesetResponses = array_map(
            function ($ruleset) use ($gameId, $favoriteRulesetIds, $userVoteMap, $categoryMap) {
                $isFavorited = in_array($ruleset->getId(), $favoriteRulesetIds);
                $voteCount = $this->voteRepository->getVoteCount($ruleset);
                $userVoteType = $userVoteMap[$ruleset->getId()]['voteType'] ?? null;

                // Check if ruleset is inherited (from a different game)
                $isInherited = $ruleset->getGame()->getId() !== $gameId;
                $inheritedFromCategory = $isInherited ? ($categoryMap[$ruleset->getGame()->getId()] ?? null) : null;

                return RulesetResponse::fromEntity(
                    $ruleset,
                    $isFavorited,
                    $voteCount,
                    $userVoteType,
                    $isInherited,
                    $inheritedFromCategory
                );
            },
            $rulesets
        );

        $response = RulesetListResponse::fromRulesets($rulesetResponses);

        return $this->json($response, Response::HTTP_OK);
    }

    /**
     * Get a map of category representative game IDs to category names
     * for rulesets that are inherited.
     */
    private function getCategoryMapForInheritedRulesets(int $gameId, array $rulesets): array
    {
        // Extract unique game IDs from rulesets that are not the current game
        $gameIds = array_unique(
            array_filter(
                array_map(fn ($ruleset) => $ruleset->getGame()->getId(), $rulesets),
                fn ($id) => $id !== $gameId
            )
        );

        if (empty($gameIds)) {
            return [];
        }

        // Query to find category names for these representative games
        $conn = $this->gameRepository->getEntityManager()->getConnection();
        $sql = '
            SELECT g.id as game_id, c.name as category_name
            FROM games g
            JOIN game_categories gc ON g.id = gc.game_id
            JOIN categories c ON gc.category_id = c.id
            WHERE g.id IN (:gameIds)
            AND g.is_category_representative = 1
        ';

        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery(['gameIds' => $gameIds]);
        $rows = $result->fetchAllAssociative();

        // Create map of game_id => category_name
        $map = [];
        foreach ($rows as $row) {
            $map[$row['game_id']] = $row['category_name'];
        }

        return $map;
    }
}
