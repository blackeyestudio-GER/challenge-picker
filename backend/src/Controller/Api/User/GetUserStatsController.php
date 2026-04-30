<?php

namespace App\Controller\Api\User;

use App\Entity\Playthrough;
use App\Entity\User;
use App\Repository\GameCategoryVoteRepository;
use App\Repository\PlaythroughRepository;
use App\Repository\PlaythroughRuleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/users/me/stats', name: 'api_user_stats', methods: ['GET'])]
class GetUserStatsController extends AbstractController
{
    public function __construct(
        private readonly GameCategoryVoteRepository $voteRepository,
        private readonly PlaythroughRepository $playthroughRepository,
        private readonly PlaythroughRuleRepository $playthroughRuleRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(
        #[CurrentUser] User $user
    ): JsonResponse {
        try {
            // Count total votes by this user
            $voteCount = $this->voteRepository->count(['user' => $user]);

            // Count completed playthroughs
            $completedPlaythroughsCount = $this->playthroughRepository->count([
                'user' => $user,
                'status' => Playthrough::STATUS_COMPLETED,
            ]);

            // Count total rules played (rules that were activated in any playthrough)
            $rulesPlayedQuery = $this->entityManager->createQueryBuilder()
                ->select('COUNT(DISTINCT pr.rule)')
                ->from(\App\Entity\PlaythroughRule::class, 'pr')
                ->innerJoin('pr.playthrough', 'p')
                ->where('p.user = :user')
                ->andWhere('p.status = :status')
                ->setParameter('user', $user)
                ->setParameter('status', Playthrough::STATUS_COMPLETED)
                ->getQuery();

            $rulesPlayedCount = (int) $rulesPlayedQuery->getSingleScalarResult();

            // Count total active rules across all completed playthroughs
            $totalActiveRulesQuery = $this->entityManager->createQueryBuilder()
                ->select('COUNT(pr.id)')
                ->from(\App\Entity\PlaythroughRule::class, 'pr')
                ->innerJoin('pr.playthrough', 'p')
                ->where('p.user = :user')
                ->andWhere('p.status = :status')
                ->andWhere('pr.isActive = :isActive')
                ->setParameter('user', $user)
                ->setParameter('status', Playthrough::STATUS_COMPLETED)
                ->setParameter('isActive', true)
                ->getQuery();

            $totalActiveRulesCount = (int) $totalActiveRulesQuery->getSingleScalarResult();

            return $this->json([
                'success' => true,
                'data' => [
                    'totalVotes' => $voteCount,
                    'completedPlaythroughs' => $completedPlaythroughsCount,
                    'rulesPlayed' => $rulesPlayedCount,
                    'totalActiveRules' => $totalActiveRulesCount,
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
