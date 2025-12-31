<?php

namespace App\Controller\Api\Shop;

use App\Repository\ShopSettingsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/shop/status', name: 'api_shop_status', methods: ['GET'])]
class GetShopStatusController extends AbstractController
{
    public function __construct(
        private readonly ShopSettingsRepository $shopSettingsRepository
    ) {
    }

    public function __invoke(): JsonResponse
    {
        $isEnabled = $this->shopSettingsRepository->isShopEnabled();

        return $this->json([
            'success' => true,
            'data' => [
                'enabled' => $isEnabled,
                'message' => $isEnabled ? 'Shop is open' : 'Shop is temporarily unavailable',
            ],
        ]);
    }
}
