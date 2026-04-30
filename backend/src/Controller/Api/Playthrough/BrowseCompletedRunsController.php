<?php

namespace App\Controller\Api\Playthrough;

use App\DTO\Response\Playthrough\BrowsePlaythroughItem;
use App\DTO\Response\Playthrough\BrowsePlaythroughResponse;
use App\DTO\Response\Playthrough\PlaythroughResponse;
use App\Repository\PlaythroughRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/playthrough/browse', name: 'api_playthrough_browse', methods: ['GET'])]
class BrowseCompletedRunsController extends AbstractController
{
    public function __construct(
        private readonly PlaythroughRepository $playthroughRepository
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        // Get current user (optional - for highlighting their own runs)
        /** @var \App\Entity\User|null $currentUser */
        $currentUser = $this->getUser();

        // Get filters from query parameters
        $gameId = $request->query->get('gameId');
        $rulesetId = $request->query->get('rulesetId');

        // Build query criteria
        $criteria = [
            'status' => 'completed',
        ];

        // Only show runs with video URLs
        // We'll filter these in PHP since we need IS NOT NULL

        if ($gameId) {
            $criteria['game'] = (int) $gameId;
        }

        if ($rulesetId) {
            $criteria['ruleset'] = (int) $rulesetId;
        }

        // Fetch all completed playthroughs
        $playthroughs = $this->playthroughRepository->findBy(
            $criteria,
            ['endedAt' => 'DESC'],
            25 // Limit to 25 most recent
        );

        // Filter to only those with video URLs
        /** @var array<int, \App\Entity\Playthrough> $playthroughs */
        $playthroughsWithVideos = array_filter(
            $playthroughs,
            fn ($p) => $p->getVideoUrl() !== null
        );

        // Get games the current user has played (for highlighting)
        $userPlayedGameIds = [];
        if ($currentUser !== null) {
            $userPlaythroughs = $this->playthroughRepository->findBy([
                'user' => $currentUser,
                'status' => 'completed',
            ]);
            /** @var array<int, \App\Entity\Playthrough> $userPlaythroughs */
            $userPlayedGameIds = array_unique(
                array_filter(
                    array_map(fn ($p) => $p->getGame()?->getId(), $userPlaythroughs),
                    fn ($id) => $id !== null
                )
            );
        }

        // Map to response DTOs
        /** @var array<int, \App\Entity\Playthrough> $playthroughsWithVideos */
        $data = array_values(array_map(function ($p) use ($currentUser, $userPlayedGameIds) {
            $response = PlaythroughResponse::fromEntity($p);

            $isOwnRun = $currentUser !== null
                && $p->getUser()->getUuid()->toRfc4122() === $currentUser->getUuid()->toRfc4122();

            $hasPlayedGame = in_array($p->getGame()?->getId(), $userPlayedGameIds, true);

            return BrowsePlaythroughItem::fromPlaythrough($response, $isOwnRun, $hasPlayedGame);
        }, $playthroughsWithVideos));

        return $this->json(BrowsePlaythroughResponse::fromItems($data));
    }
}
