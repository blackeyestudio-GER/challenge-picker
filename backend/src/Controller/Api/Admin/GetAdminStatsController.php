<?php

namespace App\Controller\Api\Admin;

use App\Repository\CategoryRepository;
use App\Repository\GameRepository;
use App\Repository\RuleRepository;
use App\Repository\RulesetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/admin/stats', name: 'api_admin_stats', methods: ['GET'])]
#[IsGranted('ROLE_ADMIN')]
class GetAdminStatsController extends AbstractController
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository,
        private readonly GameRepository $gameRepository,
        private readonly RulesetRepository $rulesetRepository,
        private readonly RuleRepository $ruleRepository
    ) {
    }

    public function __invoke(): JsonResponse
    {
        try {
            $categoriesCount = $this->categoryRepository->count([]);
            $gamesCount = $this->gameRepository->count([]);
            $rulesetsCount = $this->rulesetRepository->count([]);
            $rulesCount = $this->ruleRepository->count([]);

            return $this->json([
                'success' => true,
                'data' => [
                    'categories' => $categoriesCount,
                    'games' => $gamesCount,
                    'rulesets' => $rulesetsCount,
                    'rules' => $rulesCount,
                ],
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'FETCH_FAILED',
                    'message' => 'Failed to fetch admin statistics',
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
