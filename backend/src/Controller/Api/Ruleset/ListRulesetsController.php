<?php

namespace App\Controller\Api\Ruleset;

use App\DTO\Response\Ruleset\RulesetListResponse;
use App\DTO\Response\Ruleset\RulesetResponse;
use App\Entity\User;
use App\Repository\GameRepository;
use App\Repository\RulesetRepository;
use App\Repository\RulesetVoteRepository;
use App\Repository\UserFavoriteRulesetRepository;
use App\Service\ArrayTypeHelper;
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
    public function __invoke(string $gameId, #[CurrentUser] ?User $user = null): JsonResponse
    {
        $gameId = (int) $gameId;

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

        // Find all rulesets assigned to this game with metadata
        $rulesetsWithMetadata = $this->rulesetRepository->findByGameWithMetadata($gameId);

        // Get user's favorite ruleset IDs and vote info if user is authenticated
        $favoriteRulesetIds = [];
        $userVoteMap = [];
        if ($user) {
            $favoriteRulesetIds = $this->favoriteRepository->getFavoriteRulesetIds($user);
            $rulesetIds = [];
            foreach ($rulesetsWithMetadata as $item) {
                $ruleset = $item['ruleset'];
                $rulesetId = $ruleset->getId();
                if ($rulesetId !== null) {
                    $rulesetIds[] = $rulesetId;
                }
            }
            $userVoteMap = $this->voteRepository->getUserVotesForRulesets($user, $rulesetIds);
        }

        $rulesetResponses = array_map(
            function ($item) use ($favoriteRulesetIds, $userVoteMap) {
                $ruleset = $item['ruleset'];
                $isFavorited = in_array($ruleset->getId(), $favoriteRulesetIds);
                $voteCount = $this->voteRepository->getVoteCount($ruleset);
                $userVoteData = $userVoteMap[$ruleset->getId()] ?? null;
                $userVoteType = is_array($userVoteData) ? ArrayTypeHelper::tryGetInt($userVoteData, 'voteType') : null;

                return RulesetResponse::fromEntity(
                    $ruleset,
                    $isFavorited,
                    $voteCount,
                    $userVoteType,
                    false, // isInherited - no longer relevant with many-to-many
                    null,  // inheritedFromCategory - no longer relevant
                    ArrayTypeHelper::tryGetBool($item, 'isGameSpecific') ?? true,
                    ArrayTypeHelper::tryGetString($item, 'categoryName'),
                    ArrayTypeHelper::tryGetInt($item, 'categoryId')
                );
            },
            $rulesetsWithMetadata
        );

        $response = RulesetListResponse::fromRulesets($rulesetResponses);

        return $this->json($response, Response::HTTP_OK);
    }
}
