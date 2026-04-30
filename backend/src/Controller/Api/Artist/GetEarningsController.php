<?php

namespace App\Controller\Api\Artist;

use App\Repository\DesignerEarningsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/artist/earnings', name: 'api_artist_earnings', methods: ['GET'])]
class GetEarningsController extends AbstractController
{
    public function __construct(
        private readonly DesignerEarningsRepository $earningsRepository
    ) {
    }

    public function __invoke(
        #[CurrentUser] \App\Entity\User $user
    ): JsonResponse {
        try {
            $totalEarnings = $this->earningsRepository->getTotalEarnings($user);
            $earningsByDesignSet = $this->earningsRepository->getEarningsByDesignSet($user);

            // Calculate total sales count
            $totalSales = 0;
            foreach ($earningsByDesignSet as $item) {
                $totalSales += (int) $item['purchaseCount'];
            }

            return $this->json([
                'success' => true,
                'data' => [
                    'totalEarnings' => $totalEarnings,
                    'totalSales' => $totalSales,
                    'designSets' => array_map(function ($item) {
                        return [
                            'designSetId' => $item['designSetId'],
                            'designName' => $item['designName'],
                            'totalEarnings' => $item['totalEarnings'],
                            'purchaseCount' => (int) $item['purchaseCount'],
                        ];
                    }, $earningsByDesignSet),
                ],
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'FETCH_FAILED',
                    'message' => 'Failed to fetch earnings',
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
