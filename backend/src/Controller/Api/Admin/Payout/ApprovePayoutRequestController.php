<?php

namespace App\Controller\Api\Admin\Payout;

use App\Entity\PayoutRequest;
use App\Repository\PayoutRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/admin/payout-requests/{id}/approve', name: 'api_admin_payout_approve', methods: ['POST'])]
class ApprovePayoutRequestController extends AbstractController
{
    public function __construct(
        private readonly PayoutRequestRepository $payoutRequestRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(
        int $id,
        Request $request,
        #[CurrentUser] \App\Entity\User $admin
    ): JsonResponse {
        $payoutRequest = $this->payoutRequestRepository->find($id);

        if (!$payoutRequest) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Payout request not found',
                ],
            ], Response::HTTP_NOT_FOUND);
        }

        if ($payoutRequest->getStatus() !== PayoutRequest::STATUS_PENDING) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_STATUS',
                    'message' => 'Only pending payout requests can be approved',
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $data = json_decode($request->getContent(), true);
            $adminNotes = $data['adminNotes'] ?? null;

            $payoutRequest->setStatus(PayoutRequest::STATUS_APPROVED);
            $payoutRequest->setProcessedAt(new \DateTimeImmutable());
            $payoutRequest->setProcessedBy($admin);
            if ($adminNotes !== null) {
                $payoutRequest->setAdminNotes($adminNotes);
            }

            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'data' => [
                    'payoutRequest' => [
                        'id' => $payoutRequest->getId(),
                        'status' => $payoutRequest->getStatus(),
                        'processedAt' => $payoutRequest->getProcessedAt()?->format('c'),
                    ],
                ],
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'APPROVAL_FAILED',
                    'message' => 'Failed to approve payout request',
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
