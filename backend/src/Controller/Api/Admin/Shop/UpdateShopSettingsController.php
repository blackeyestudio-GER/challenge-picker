<?php

namespace App\Controller\Api\Admin\Shop;

use App\DTO\Request\Admin\UpdateShopSettingsRequest;
use App\DTO\Response\Admin\UpdateShopSettingsResponse;
use App\Repository\ShopSettingsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/admin/shop/settings', name: 'api_admin_shop_settings_update', methods: ['PUT'])]
class UpdateShopSettingsController extends AbstractController
{
    public function __construct(
        private readonly ShopSettingsRepository $shopSettingsRepository,
        private readonly ValidatorInterface $validator
    ) {
    }

    public function __invoke(UpdateShopSettingsRequest $request): JsonResponse
    {
        $errors = $this->validator->validate($request);
        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $error) {
                $messages[] = $error->getMessage();
            }

            return $this->json([
                'success' => false,
                'error' => ['message' => implode(', ', $messages)],
            ], Response::HTTP_BAD_REQUEST);
        }

        $shopEnabled = $request->shopEnabled;
        if ($shopEnabled === null) {
            return $this->json([
                'success' => false,
                'error' => ['message' => 'Missing required field: shopEnabled'],
            ], Response::HTTP_BAD_REQUEST);
        }

        $this->shopSettingsRepository->setSetting('shop_enabled', $shopEnabled ? '1' : '0');

        return $this->json(
            UpdateShopSettingsResponse::fromValues(
                $shopEnabled ? 'Shop enabled successfully' : 'Shop disabled successfully',
                $shopEnabled
            ),
            Response::HTTP_OK
        );
    }
}
