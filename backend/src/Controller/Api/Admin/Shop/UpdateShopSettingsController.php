<?php

namespace App\Controller\Api\Admin\Shop;

use App\Repository\ShopSettingsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin/shop/settings', name: 'api_admin_shop_settings_update', methods: ['PUT'])]
class UpdateShopSettingsController extends AbstractController
{
    public function __construct(
        private readonly ShopSettingsRepository $shopSettingsRepository
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data) || !isset($data['shopEnabled'])) {
            return $this->json([
                'success' => false,
                'error' => ['message' => 'Missing required field: shopEnabled'],
            ], 400);
        }

        $shopEnabled = (bool) $data['shopEnabled'];

        $this->shopSettingsRepository->setSetting('shop_enabled', $shopEnabled ? '1' : '0');

        return $this->json([
            'success' => true,
            'data' => [
                'message' => $shopEnabled ? 'Shop enabled successfully' : 'Shop disabled successfully',
                'shopEnabled' => $shopEnabled,
            ],
        ]);
    }
}
