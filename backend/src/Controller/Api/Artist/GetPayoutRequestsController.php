<?php

namespace App\Controller\Api\Artist;

use App\Repository\PayoutRequestRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/artist/payout-requests', name: 'api_artist_payout_requests', methods: ['GET'])]
class GetPayoutRequestsController extends AbstractController
{
    public function __construct(
        private readonly PayoutRequestRepository $payoutRequestRepository
    ) {
    }

    public function __invoke(
        Request $request,
        #[CurrentUser] \App\Entity\User $user
    ): JsonResponse {
        try {
            $limit = min((int) $request->query->get('limit', 50), 100);
            $offset = max((int) $request->query->get('offset', 0), 0);

            $payoutRequests = $this->payoutRequestRepository->findByDesigner($user, $limit, $offset);

            // Get pending amount
            $pendingAmount = $this->payoutRequestRepository->getTotalPendingAmount($user);

            return $this->json([
                'success' => true,
                'data' => [
                    'payoutRequests' => array_map(function ($request) {
                        return [
                            'id' => $request->getId(),
                            'amount' => $request->getAmount(),
                            'currency' => $request->getCurrency(),
                            'status' => $request->getStatus(),
                            'isAutomated' => $request->isAutomated(),
                            'requestedAt' => $request->getRequestedAt()?->format('c'),
                            'processedAt' => $request->getProcessedAt()?->format('c'),
                            'adminNotes' => $request->getAdminNotes(),
                        ];
                    }, $payoutRequests),
                    'pendingAmount' => $pendingAmount,
                ],
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'FETCH_FAILED',
                    'message' => 'Failed to fetch payout requests',
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
