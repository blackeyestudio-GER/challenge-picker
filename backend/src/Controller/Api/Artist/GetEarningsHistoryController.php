<?php

namespace App\Controller\Api\Artist;

use App\Repository\DesignerEarningsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/artist/earnings/history', name: 'api_artist_earnings_history', methods: ['GET'])]
class GetEarningsHistoryController extends AbstractController
{
    public function __construct(
        private readonly DesignerEarningsRepository $earningsRepository
    ) {
    }

    public function __invoke(
        Request $request,
        #[CurrentUser] \App\Entity\User $user
    ): JsonResponse {
        try {
            $limit = min((int) $request->query->get('limit', 50), 100); // Max 100
            $offset = max((int) $request->query->get('offset', 0), 0);

            $earnings = $this->earningsRepository->createQueryBuilder('de')
                ->where('de.designer = :designer')
                ->setParameter('designer', $user)
                ->orderBy('de.earnedAt', 'DESC')
                ->setMaxResults($limit)
                ->setFirstResult($offset)
                ->getQuery()
                ->getResult();

            $totalCount = $this->earningsRepository->createQueryBuilder('de')
                ->select('COUNT(de.id)')
                ->where('de.designer = :designer')
                ->setParameter('designer', $user)
                ->getQuery()
                ->getSingleScalarResult();

            return $this->json([
                'success' => true,
                'data' => [
                    'earnings' => array_map(function ($earning) {
                        return [
                            'id' => $earning->getId(),
                            'designSetId' => $earning->getDesignSet()?->getId(),
                            'designSetName' => $earning->getDesignSet()?->getDesignName()?->getName(),
                            'amount' => $earning->getAmount(),
                            'purchasePrice' => $earning->getPurchasePrice(),
                            'feePercentage' => $earning->getFeePercentage(),
                            'currency' => $earning->getCurrency(),
                            'earnedAt' => $earning->getEarnedAt()?->format('c'),
                        ];
                    }, $earnings),
                    'total' => (int) $totalCount,
                    'limit' => $limit,
                    'offset' => $offset,
                ],
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'FETCH_FAILED',
                    'message' => 'Failed to fetch earnings history',
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
