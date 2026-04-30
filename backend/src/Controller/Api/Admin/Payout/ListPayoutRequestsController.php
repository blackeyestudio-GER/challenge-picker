<?php

namespace App\Controller\Api\Admin\Payout;

use App\Repository\PayoutRequestRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin/payout-requests', name: 'api_admin_payout_requests', methods: ['GET'])]
class ListPayoutRequestsController extends AbstractController
{
    public function __construct(
        private readonly PayoutRequestRepository $payoutRequestRepository
    ) {
    }

    public function __invoke(): JsonResponse
    {
        try {
            $pendingRequests = $this->payoutRequestRepository->findPending();

            return $this->json([
                'success' => true,
                'data' => [
                    'payoutRequests' => array_map(function ($request) {
                        return [
                            'id' => $request->getId(),
                            'designerUuid' => $request->getDesigner()?->getUuid()?->toString(),
                            'designerUsername' => $request->getDesigner()?->getUsername(),
                            'designerEmail' => $request->getDesigner()?->getEmail(),
                            'amount' => $request->getAmount(),
                            'currency' => $request->getCurrency(),
                            'status' => $request->getStatus(),
                            'isAutomated' => $request->isAutomated(),
                            'requestedAt' => $request->getRequestedAt()?->format('c'),
                        ];
                    }, $pendingRequests),
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
