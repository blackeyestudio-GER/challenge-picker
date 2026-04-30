<?php

namespace App\Controller\Api\Admin\Shop;

use App\Repository\ShopSettingsRepository;
use App\Service\ArrayTypeHelper;
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
        if (!is_array($data)) {
            return $this->json([
                'success' => false,
                'error' => ['message' => 'Invalid request body'],
            ], 400);
        }

        /* @var array<string, mixed> $data */
        try {
            $shopEnabled = ArrayTypeHelper::getBool($data, 'shopEnabled');
        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'success' => false,
                'error' => ['message' => 'Missing required field: shopEnabled'],
            ], 400);
        }

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
