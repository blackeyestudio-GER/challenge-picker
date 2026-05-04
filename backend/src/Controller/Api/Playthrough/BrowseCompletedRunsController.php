<?php

namespace App\Controller\Api\Playthrough;

use App\DTO\Response\Playthrough\BrowsePlaythroughItem;
use App\DTO\Response\Playthrough\BrowsePlaythroughResponse;
use App\DTO\Response\Playthrough\PlaythroughResponse;
use App\Repository\PlaythroughRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/playthrough/browse', name: 'api_playthrough_browse', methods: ['GET'])]
class BrowseCompletedRunsController extends AbstractController
{
    public function __construct(
        private readonly PlaythroughRepository $playthroughRepository
    ) {
    }

    public function __invoke(): JsonResponse
    {
        // Get current user (optional - for highlighting their own runs)
        /** @var \App\Entity\User|null $currentUser */
        $currentUser = $this->getUser();

        // Build query criteria
        $criteria = [
            'status' => 'completed',
        ];

        // Fetch all completed playthroughs
        $playthroughs = $this->playthroughRepository->findBy(
            $criteria,
            ['endedAt' => 'DESC'],
            50 // Limit to 50 most recent completed runs
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
        /** @var array<int, \App\Entity\Playthrough> $playthroughs */
        $data = array_values(array_map(function ($p) use ($currentUser, $userPlayedGameIds) {
            $response = PlaythroughResponse::fromEntity($p);

            $isOwnRun = $currentUser !== null
                && $p->getUser()->getUuid()->toRfc4122() === $currentUser->getUuid()->toRfc4122();

            $hasPlayedGame = in_array($p->getGame()?->getId(), $userPlayedGameIds, true);

            return BrowsePlaythroughItem::fromPlaythrough($response, $isOwnRun, $hasPlayedGame);
        }, $playthroughs));

        return $this->json(BrowsePlaythroughResponse::fromItems($data));
    }
}
