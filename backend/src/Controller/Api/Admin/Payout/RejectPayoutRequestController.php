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

#[Route('/api/admin/payout-requests/{id}/reject', name: 'api_admin_payout_reject', methods: ['POST'])]
class RejectPayoutRequestController extends AbstractController
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
                    'message' => 'Only pending payout requests can be rejected',
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $data = json_decode($request->getContent(), true);
            $adminNotes = $data['adminNotes'] ?? null;

            if ($adminNotes === null || trim($adminNotes) === '') {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'NOTES_REQUIRED',
                        'message' => 'Admin notes are required when rejecting a payout request',
                    ],
                ], Response::HTTP_BAD_REQUEST);
            }

            $payoutRequest->setStatus(PayoutRequest::STATUS_REJECTED);
            $payoutRequest->setProcessedAt(new \DateTimeImmutable());
            $payoutRequest->setProcessedBy($admin);
            $payoutRequest->setAdminNotes($adminNotes);

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
                    'code' => 'REJECTION_FAILED',
                    'message' => 'Failed to reject payout request',
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
