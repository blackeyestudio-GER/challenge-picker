<?php

namespace App\Controller\Api\User;

use App\Entity\User;
use App\Repository\GameCategoryVoteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/users/me/stats', name: 'api_user_stats', methods: ['GET'])]
class GetUserStatsController extends AbstractController
{
    public function __construct(
        private readonly GameCategoryVoteRepository $voteRepository
    ) {
    }

    public function __invoke(
        #[CurrentUser] User $user
    ): JsonResponse {
        try {
            // Count total votes by this user
            $voteCount = $this->voteRepository->count(['user' => $user]);

            return $this->json([
                'success' => true,
                'data' => [
                    'totalVotes' => $voteCount,
                ],
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            error_log('Failed to fetch user stats: ' . $e->getMessage());

            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'STATS_FAILED',
                    'message' => 'Failed to fetch user stats',
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
