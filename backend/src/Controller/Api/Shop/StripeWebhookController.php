<?php

namespace App\Controller\Api\Shop;

use App\Entity\DesignerEarnings;
use App\Entity\UserDesignSet;
use App\Repository\DesignSetRepository;
use App\Repository\ShopTransactionRepository;
use App\Service\ArrayTypeHelper;
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
                if (!is_array($item)) {
                    continue;
                }
                $designSetId = ArrayTypeHelper::tryGetInt($item, 'design_set_id');
                if ($designSetId === null) {
                    continue;
                }
                $designSet = $this->designSetRepository->find($designSetId);

                if ($designSet) {
                    $userDesignSet = new UserDesignSet();
                    $userDesignSet->setUserUuid($userUuid);
                    $userDesignSet->setDesignSet($designSet);
                    $price = ArrayTypeHelper::tryGetFloat($item, 'price');
                    $userDesignSet->setPricePaid($price !== null ? (string) $price : '0.00');
                    $userDesignSet->setCurrency($transaction->getCurrency());

                    $this->entityManager->persist($userDesignSet);

                    // Record designer earnings if design set has a designer and fee
                    $designer = $designSet->getDesigner();
                    $designerFee = $designSet->getDesignerFee();
                    if ($designer !== null && $designerFee !== null && $price !== null && $price > 0) {
                        // Calculate designer commission: purchasePrice * feePercentage
                        $purchasePrice = (string) $price;
                        $commissionAmount = bcmul($purchasePrice, $designerFee, 2);

                        // Only create earnings if commission is greater than 0
                        if (bccomp($commissionAmount, '0.00', 2) > 0) {
                            $earnings = new DesignerEarnings();
                            $earnings->setDesigner($designer);
                            $earnings->setDesignSet($designSet);
                            $earnings->setPurchase($userDesignSet);
                            $earnings->setAmount($commissionAmount);
                            $earnings->setFeePercentage($designerFee);
                            $earnings->setPurchasePrice($purchasePrice);
                            $earnings->setCurrency($transaction->getCurrency());

                            $this->entityManager->persist($earnings);
                        }
                    }
                }
            }

            $this->entityManager->flush();

            return new Response('Webhook handled', 200);
        }

        // Handle other event types if needed
        return new Response('Event type not handled', 200);
    }
}
