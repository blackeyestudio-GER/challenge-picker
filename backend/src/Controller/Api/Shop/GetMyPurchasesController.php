<?php

namespace App\Controller\Api\Shop;

use App\Repository\UserDesignSetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/shop/my-purchases', name: 'api_shop_my_purchases', methods: ['GET'])]
class GetMyPurchasesController extends AbstractController
{
    public function __construct(
        private readonly UserDesignSetRepository $userDesignSetRepository
    ) {
    }

    public function __invoke(): JsonResponse
    {
        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        $purchases = $this->userDesignSetRepository->findBy(
            ['userUuid' => $user->getUuid()],
            ['purchasedAt' => 'DESC']
        );

        /** @var list<array{
         *   id: int|null,
         *   designSet: array{id: int|null, name: string, type: string, theme: string|null, description: string|null, preview_image: string|null, preview_images: list<string>},
         *   purchasedAt: string,
         *   pricePaid: string|null,
         *   currency: string|null
         * }> $data
         */
        $data = array_map(function ($purchase) {
            $designSet = $purchase->getDesignSet();
            $purchasedAt = $purchase->getPurchasedAt();

            $previewImages = $designSet !== null ? $designSet->collectPreviewImageBase64s(4) : [];

            return [
                'id' => $purchase->getId(),
                'designSet' => [
                    'id' => $designSet?->getId(),
                    'name' => $designSet?->getDesignName()?->getName() ?? '',
                    'type' => $designSet?->getType() ?? '',
                    'theme' => $designSet?->getTheme(),
                    'description' => $designSet?->getDescription(),
                    'preview_image' => $previewImages[0] ?? null,
                    'preview_images' => $previewImages,
                ],
                'purchasedAt' => $purchasedAt?->format('c') ?? '',
                'pricePaid' => $purchase->getPricePaid(),
                'currency' => $purchase->getCurrency(),
            ];
        }, $purchases);

        return $this->json([
            'success' => true,
            'data' => ['purchases' => $data],
        ]);
    }
}
