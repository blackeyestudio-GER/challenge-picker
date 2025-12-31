<?php

namespace App\Controller\Api\Shop;

use App\Repository\DesignSetRepository;
use App\Repository\ShopTransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/shop/retry-transaction/{id}', name: 'api_shop_retry_transaction', methods: ['POST'])]
class RetryTransactionController extends AbstractController
{
    public function __construct(
        private readonly ShopTransactionRepository $shopTransactionRepository,
        private readonly DesignSetRepository $designSetRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(int $id): JsonResponse
    {
        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        // Find the failed transaction
        $transaction = $this->shopTransactionRepository->find($id);

        if (!$transaction) {
            return $this->json([
                'success' => false,
                'error' => ['message' => 'Transaction not found'],
            ], 404);
        }

        // Verify ownership
        $transactionUuid = $transaction->getUserUuid();
        if ($transactionUuid === null || $transactionUuid->toRfc4122() !== $user->getUuid()->toRfc4122()) {
            return $this->json([
                'success' => false,
                'error' => ['message' => 'Unauthorized'],
            ], 403);
        }

        // Only allow retry for failed/pending transactions
        $status = $transaction->getStatus();
        if ($status === null || !in_array($status, ['failed', 'pending'], true)) {
            return $this->json([
                'success' => false,
                'error' => ['message' => 'Cannot retry this transaction (status: ' . ($status ?? 'unknown') . ')'],
            ], 400);
        }

        // Extract design set ID from items
        $items = $transaction->getItems();
        if (empty($items) || !is_array($items[0]) || !isset($items[0]['design_set_id'])) {
            return $this->json([
                'success' => false,
                'error' => ['message' => 'Invalid transaction data'],
            ], 400);
        }

        $designSetId = (int) $items[0]['design_set_id'];
        $designSet = $this->designSetRepository->find($designSetId);

        if (!$designSet) {
            return $this->json([
                'success' => false,
                'error' => ['message' => 'Design set no longer available'],
            ], 404);
        }

        // Get environment variables
        $stripeSecretKey = $_ENV['STRIPE_SECRET_KEY'] ?? '';
        $frontendUrl = $_ENV['FRONTEND_URL'] ?? 'http://localhost:3000';

        if ($stripeSecretKey === '') {
            return $this->json([
                'success' => false,
                'error' => ['message' => 'Stripe not configured'],
            ], 500);
        }

        // Initialize Stripe
        \Stripe\Stripe::setApiKey($stripeSecretKey);

        // Create new Stripe Checkout Session
        try {
            $designSetPrice = $designSet->getPrice();
            $unitAmount = $designSetPrice !== null ? (int) ((float) $designSetPrice * 100) : 0;

            /** @var array{payment_method_types: array<string>, line_items: array<array<string, mixed>>, mode: string, success_url: string, cancel_url: string, metadata: array<string, string>} $checkoutParams */
            $checkoutParams = [
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => $designSet->getName() ?? 'Design Set',
                            'description' => $designSet->getDescription() ?? '',
                        ],
                        'unit_amount' => $unitAmount,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $frontendUrl . '/shop/success?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $frontendUrl . '/shop',
                'metadata' => [
                    'user_uuid' => $user->getUuid()->toRfc4122(),
                    'design_set_id' => (string) $designSet->getId(),
                ],
            ];

            $checkoutSession = \Stripe\Checkout\Session::create($checkoutParams);

            // Mark old transaction as 'cancelled'
            $transaction->setStatus('cancelled');
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'data' => ['checkoutUrl' => $checkoutSession->url],
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => ['message' => 'Failed to create checkout session: ' . $e->getMessage()],
            ], 500);
        }
    }
}
