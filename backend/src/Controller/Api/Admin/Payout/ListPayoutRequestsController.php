<?php

namespace App\Controller\Api\Admin\Payout;

use App\DTO\Response\Admin\PayoutRequestItem;
use App\DTO\Response\Admin\PayoutRequestsResponse;
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

            $items = [];
            foreach ($pendingRequests as $request) {
                $requestId = $request->getId();
                $amount = $request->getAmount();
                if ($requestId === null || $amount === null) {
                    continue;
                }

                $items[] = new PayoutRequestItem(
                    id: $requestId,
                    designerUuid: $request->getDesigner()?->getUuid()?->toString(),
                    designerUsername: $request->getDesigner()?->getUsername(),
                    designerEmail: $request->getDesigner()?->getEmail(),
                    amount: $amount,
                    currency: $request->getCurrency(),
                    status: $request->getStatus(),
                    isAutomated: $request->isAutomated(),
                    requestedAt: $request->getRequestedAt()?->format('c')
                );
            }

            return $this->json(PayoutRequestsResponse::fromItems($items), Response::HTTP_OK);
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
