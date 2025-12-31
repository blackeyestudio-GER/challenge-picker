<?php

namespace App\Controller\Api\Admin\Shop;

use App\Repository\ShopSettingsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin/shop/settings', name: 'api_admin_shop_settings_get', methods: ['GET'])]
class GetShopSettingsController extends AbstractController
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
                'shopEnabled' => $isEnabled,
            ],
        ]);
    }
}
