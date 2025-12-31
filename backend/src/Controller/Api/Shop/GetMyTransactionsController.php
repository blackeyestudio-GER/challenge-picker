<?php

namespace App\Controller\Api\Shop;

use App\Repository\ShopTransactionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/shop/my-transactions', name: 'api_shop_my_transactions', methods: ['GET'])]
class GetMyTransactionsController extends AbstractController
{
    public function __construct(
        private readonly ShopTransactionRepository $shopTransactionRepository
    ) {
    }

    public function __invoke(): JsonResponse
    {
        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        $transactions = $this->shopTransactionRepository->findBy(
            ['userUuid' => $user->getUuid()],
            ['createdAt' => 'DESC']
        );

        $data = array_map(function ($transaction) {
            $createdAt = $transaction->getCreatedAt();

            return [
                'id' => $transaction->getId(),
                'stripeSessionId' => $transaction->getStripeSessionId(),
                'status' => $transaction->getStatus(),
                'amount' => $transaction->getAmount(),
                'currency' => $transaction->getCurrency(),
                'items' => $transaction->getItems(),
                'createdAt' => $createdAt?->format('c') ?? '',
                'completedAt' => $transaction->getCompletedAt()?->format('c'),
            ];
        }, $transactions);

        return $this->json([
            'success' => true,
            'data' => ['transactions' => $data],
        ]);
    }
}
