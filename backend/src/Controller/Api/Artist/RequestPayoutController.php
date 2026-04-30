<?php

namespace App\Controller\Api\Artist;

use App\Entity\PayoutRequest;
use App\Repository\DesignerEarningsRepository;
use App\Repository\PayoutRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/artist/payout-request', name: 'api_artist_payout_request', methods: ['POST'])]
class RequestPayoutController extends AbstractController
{
    private const MINIMUM_PAYOUT_AMOUNT = '10.00'; // $10 minimum

    public function __construct(
        private readonly DesignerEarningsRepository $earningsRepository,
        private readonly PayoutRequestRepository $payoutRequestRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(
        Request $request,
        #[CurrentUser] \App\Entity\User $user
    ): JsonResponse {
        // Check if user is an artist
        if (!$user->isArtist()) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_ARTIST',
                    'message' => 'Only artists can request payouts',
                ],
            ], Response::HTTP_FORBIDDEN);
        }

        try {
            $data = json_decode($request->getContent(), true);
            if (!is_array($data)) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'INVALID_REQUEST',
                        'message' => 'Invalid request body',
                    ],
                ], Response::HTTP_BAD_REQUEST);
            }

            $requestedAmount = $data['amount'] ?? null;
            if ($requestedAmount === null || !is_numeric($requestedAmount)) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'INVALID_AMOUNT',
                        'message' => 'Amount is required and must be numeric',
                    ],
                ], Response::HTTP_BAD_REQUEST);
            }

            $requestedAmount = (string) $requestedAmount;

            // Validate minimum payout amount
            if (bccomp($requestedAmount, self::MINIMUM_PAYOUT_AMOUNT, 2) < 0) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'BELOW_MINIMUM',
                        'message' => sprintf('Minimum payout amount is $%s', self::MINIMUM_PAYOUT_AMOUNT),
                    ],
                ], Response::HTTP_BAD_REQUEST);
            }

            // Get total available earnings
            $totalEarnings = $this->earningsRepository->getTotalEarnings($user);

            // Get total pending payout requests
            $pendingPayouts = $this->payoutRequestRepository->getTotalPendingAmount($user);

            // Calculate available balance
            $availableBalance = bcsub($totalEarnings, $pendingPayouts, 2);

            // Check if requested amount is available
            if (bccomp($requestedAmount, $availableBalance, 2) > 0) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'INSUFFICIENT_BALANCE',
                        'message' => sprintf('Insufficient balance. Available: $%s', $availableBalance),
                    ],
                ], Response::HTTP_BAD_REQUEST);
            }

            // Check if there's already a pending request
            $pendingRequests = $this->payoutRequestRepository->findPendingByDesigner($user);
            if (!empty($pendingRequests)) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'PENDING_REQUEST_EXISTS',
                        'message' => 'You already have a pending payout request',
                    ],
                ], Response::HTTP_BAD_REQUEST);
            }

            // Create payout request (manual, not automated)
            $payoutRequest = new PayoutRequest();
            $payoutRequest->setDesigner($user);
            $payoutRequest->setAmount($requestedAmount);
            $payoutRequest->setCurrency($data['currency'] ?? 'USD');
            $payoutRequest->setStatus(PayoutRequest::STATUS_PENDING);
            $payoutRequest->setIsAutomated(false); // Manual request

            $this->entityManager->persist($payoutRequest);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'data' => [
                    'payoutRequest' => [
                        'id' => $payoutRequest->getId(),
                        'amount' => $payoutRequest->getAmount(),
                        'currency' => $payoutRequest->getCurrency(),
                        'status' => $payoutRequest->getStatus(),
                        'requestedAt' => $payoutRequest->getRequestedAt()?->format('c'),
                    ],
                ],
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'REQUEST_FAILED',
                    'message' => 'Failed to create payout request',
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
