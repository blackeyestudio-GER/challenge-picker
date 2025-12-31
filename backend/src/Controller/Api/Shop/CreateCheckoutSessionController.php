<?php

namespace App\Controller\Api\Shop;

use App\Entity\ShopTransaction;
use App\Repository\DesignSetRepository;
use App\Repository\UserDesignSetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/shop/create-checkout-session', name: 'api_shop_create_checkout_session', methods: ['POST'])]
class CreateCheckoutSessionController extends AbstractController
{
    public function __construct(
        private readonly DesignSetRepository $designSetRepository,
        private readonly UserDesignSetRepository $userDesignSetRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        $data = json_decode($request->getContent(), true);
        $designSetIds = $data['design_set_ids'] ?? [];

        if (empty($designSetIds)) {
            return $this->json(['error' => 'No items provided'], 400);
        }

        // Fetch design sets
        $designSets = $this->designSetRepository->findBy(['id' => $designSetIds]);

        if (empty($designSets)) {
            return $this->json(['error' => 'Invalid design sets'], 400);
        }

        // Check if user already owns any of these
        $alreadyOwned = [];
        foreach ($designSets as $designSet) {
            if ($this->userDesignSetRepository->userOwnsDesignSet($user->getUuid(), $designSet->getId())) {
                $alreadyOwned[] = $designSet->getName();
            }
        }

        if (!empty($alreadyOwned)) {
            return $this->json([
                'error' => 'You already own: ' . implode(', ', $alreadyOwned),
            ], 400);
        }

        // Calculate total
        $total = 0;
        $lineItems = [];
        $items = [];

        foreach ($designSets as $designSet) {
            if (!$designSet->getIsPremium()) {
                continue; // Skip free design sets
            }

            $price = (float) $designSet->getPrice();
            $total += $price;

            $items[] = [
                'design_set_id' => $designSet->getId(),
                'name' => $designSet->getName(),
                'price' => $price,
            ];

            // Stripe line items (price in cents)
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => $designSet->getName(),
                        'description' => 'Tarot card design set for Challenge Picker',
                    ],
                    'unit_amount' => (int) ($price * 100), // Convert to cents
                ],
                'quantity' => 1,
            ];
        }

        if ($total <= 0) {
            return $this->json(['error' => 'No premium items in cart'], 400);
        }

        // Initialize Stripe
        \Stripe\Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY'] ?? '');

        try {
            // Create Stripe Checkout Session
            $session = \Stripe\Checkout\Session::create([
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => $_ENV['FRONTEND_URL'] . '/shop/success?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $_ENV['FRONTEND_URL'] . '/shop',
                'customer_email' => $user->getEmail(),
                'metadata' => [
                    'user_uuid' => (string) $user->getUuid(),
                ],
            ]);

            // Create transaction record
            $transaction = new ShopTransaction();
            $transaction->setUserUuid($user->getUuid());
            $transaction->setStripeSessionId($session->id);
            $transaction->setAmount((string) $total);
            $transaction->setCurrency('usd');
            $transaction->setItems($items);
            $transaction->setStatus('pending');

            $this->entityManager->persist($transaction);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'data' => [
                    'session_id' => $session->id,
                    'checkout_url' => $session->url,
                ],
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Failed to create checkout session: ' . $e->getMessage(),
            ], 500);
        }
    }
}
