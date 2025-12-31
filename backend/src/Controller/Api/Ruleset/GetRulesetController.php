<?php

namespace App\Controller\Api\Ruleset;

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

class GetRulesetController extends AbstractController
{
    public function __construct(
        private readonly RulesetRepository $rulesetRepository,
        private readonly GameRepository $gameRepository,
        private readonly UserFavoriteRulesetRepository $favoriteRepository,
        private readonly RulesetVoteRepository $voteRepository
    ) {
    }

    #[Route('/api/rulesets/{rulesetId}', name: 'api_ruleset_get', methods: ['GET'])]
    public function __invoke(int $rulesetId, #[CurrentUser] ?User $user = null): JsonResponse
    {
        $ruleset = $this->rulesetRepository->find($rulesetId);
        if (!$ruleset) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'RULESET_NOT_FOUND',
                    'message' => 'Ruleset not found',
                ],
            ], Response::HTTP_NOT_FOUND);
        }

        // Get user's favorite and vote info if authenticated
        $isFavorited = false;
        $voteCount = 0;
        $userVoteType = null;
        if ($user) {
            $favoriteRulesetIds = $this->favoriteRepository->getFavoriteRulesetIds($user);
            $isFavorited = in_array($rulesetId, $favoriteRulesetIds);
            $voteCount = $this->voteRepository->getVoteCount($ruleset);
            $userVoteMap = $this->voteRepository->getUserVotesForRulesets($user, [$rulesetId]);
            $userVoteType = $userVoteMap[$rulesetId]['voteType'] ?? null;
        }

        // Determine if this is game-specific or category-based
        // Check if any game connected to this ruleset is a category representative
        $isGameSpecific = true;
        $categoryName = null;
        $categoryId = null;

        $em = $this->gameRepository->getEntityManager();
        $gamesWithRuleset = $ruleset->getGames()->toArray();

        if (!empty($gamesWithRuleset)) {
            $gameIds = array_map(fn ($g) => $g->getId(), $gamesWithRuleset);

            // Find categories where any of these games is the representative
            $categoryInfo = $em->createQueryBuilder()
                ->select('c.id', 'c.name')
                ->from('App\Entity\Category', 'c')
                ->where('IDENTITY(c.representativeGame) IN (:gameIds)')
                ->setParameter('gameIds', $gameIds)
                ->getQuery()
                ->getResult();

            if (!empty($categoryInfo)) {
                // This ruleset is category-based
                $isGameSpecific = false;
                $categoryName = $categoryInfo[0]['name'];
                $categoryId = $categoryInfo[0]['id'];
            }
        }

        $response = RulesetResponse::fromEntity(
            $ruleset,
            $isFavorited,
            $voteCount,
            $userVoteType,
            false,
            null,
            $isGameSpecific,
            $categoryName,
            $categoryId
        );

        return $this->json([
            'success' => true,
            'data' => $response,
        ], Response::HTTP_OK);
    }
}
