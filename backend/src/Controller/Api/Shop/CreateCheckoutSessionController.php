<?php

namespace App\Controller\Api\Shop;

use App\Entity\ShopTransaction;
use App\Repository\DesignSetRepository;
use App\Repository\UserDesignSetRepository;
use App\Service\ArrayTypeHelper;
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
        if (!is_array($data)) {
            return $this->json(['error' => 'Invalid request body'], 400);
        }

        /** @var array<string, mixed> $data */
        try {
            $designSetIdsRaw = ArrayTypeHelper::getArray($data, 'design_set_ids');
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => 'design_set_ids is required'], 400);
        }

        if (empty($designSetIdsRaw)) {
            return $this->json(['error' => 'No items provided'], 400);
        }

        /** @var list<int> $designSetIds */
        $designSetIds = array_values(array_filter($designSetIdsRaw, static fn (mixed $id): bool => is_int($id)));
        if (empty($designSetIds)) {
            return $this->json(['error' => 'Invalid design set IDs'], 400);
        }

        // Fetch design sets
        $designSets = $this->designSetRepository->findBy(['id' => $designSetIds]);

        if (empty($designSets)) {
            return $this->json(['error' => 'Invalid design sets'], 400);
        }

        // Check if user already owns any of these
        $alreadyOwned = [];
        foreach ($designSets as $designSet) {
            $designSetId = $designSet->getId();
            if ($designSetId !== null && $this->userDesignSetRepository->userOwnsDesignSet($user->getUuid(), $designSetId)) {
                $alreadyOwned[] = $designSet->getDesignName()?->getName() ?? 'Design set';
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
            if (!$designSet->isPremium()) {
                continue; // Skip free design sets
            }

            $price = (float) $designSet->getPrice();
            $total += $price;

            $designDisplayName = $designSet->getDesignName()?->getName() ?? 'Design set';

            $items[] = [
                'design_set_id' => $designSet->getId(),
                'name' => $designDisplayName,
                'price' => $price,
            ];

            // Stripe line items (price in cents)
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => $designDisplayName,
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

        $stripeSecretKey = isset($_ENV['STRIPE_SECRET_KEY']) && is_string($_ENV['STRIPE_SECRET_KEY'])
            ? $_ENV['STRIPE_SECRET_KEY']
            : '';
        $frontendUrl = isset($_ENV['FRONTEND_URL']) && is_string($_ENV['FRONTEND_URL'])
            ? $_ENV['FRONTEND_URL']
            : 'http://localhost:3000';
        $customerEmail = $user->getEmail();

        if ($stripeSecretKey === '' || $customerEmail === '') {
            return $this->json(['error' => 'Stripe checkout is not configured'], 500);
        }

        // Initialize Stripe
        \Stripe\Stripe::setApiKey($stripeSecretKey);

        try {
            // Create Stripe Checkout Session
            $session = \Stripe\Checkout\Session::create([
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => $frontendUrl . '/shop/success?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $frontendUrl . '/shop',
                'customer_email' => $customerEmail,
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
