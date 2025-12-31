<?php

namespace App\Controller\Api\Shop;

use App\Entity\UserDesignSet;
use App\Repository\DesignSetRepository;
use App\Repository\ShopTransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/webhooks/stripe', name: 'api_webhooks_stripe', methods: ['POST'])]
class StripeWebhookController extends AbstractController
{
    public function __construct(
        private readonly ShopTransactionRepository $transactionRepository,
        private readonly DesignSetRepository $designSetRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $payload = $request->getContent();
        $sigHeader = $request->headers->get('stripe-signature');
        $webhookSecret = $_ENV['STRIPE_WEBHOOK_SECRET'] ?? '';

        if (!$webhookSecret) {
            return new Response('Webhook secret not configured', 500);
        }

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sigHeader,
                $webhookSecret
            );
        } catch (\UnexpectedValueException $e) {
            return new Response('Invalid payload', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return new Response('Invalid signature', 400);
        }

        // Handle the event
        if ('checkout.session.completed' === $event->type) {
            $session = $event->data->object;

            // Find transaction
            $transaction = $this->transactionRepository->findByStripeSessionId($session->id);

            if (!$transaction) {
                return new Response('Transaction not found', 404);
            }

            // Update transaction
            $transaction->setStatus('completed');
            $transaction->setStripePaymentIntentId($session->payment_intent);
            $transaction->setCompletedAt(new \DateTimeImmutable());

            // Unlock design sets for user
            $userUuid = $transaction->getUserUuid();
            $items = $transaction->getItems();

            foreach ($items as $item) {
                $designSet = $this->designSetRepository->find($item['design_set_id']);

                if ($designSet) {
                    $userDesignSet = new UserDesignSet();
                    $userDesignSet->setUserUuid($userUuid);
                    $userDesignSet->setDesignSet($designSet);
                    $userDesignSet->setPricePaid((string) $item['price']);
                    $userDesignSet->setCurrency($transaction->getCurrency());

                    $this->entityManager->persist($userDesignSet);
                }
            }

            $this->entityManager->flush();

            return new Response('Webhook handled', 200);
        }

        // Handle other event types if needed
        return new Response('Event type not handled', 200);
    }
}
