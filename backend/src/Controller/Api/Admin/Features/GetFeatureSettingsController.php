<?php

namespace App\Controller\Api\Admin\Features;

use App\Repository\FeatureSettingsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin/features/settings', name: 'api_admin_features_settings_get', methods: ['GET'])]
class GetFeatureSettingsController extends AbstractController
{
    public function __construct(
        private readonly FeatureSettingsRepository $featureSettingsRepository
    ) {
    }

    public function __invoke(): JsonResponse
    {
        // Define all available features with defaults
        $availableFeatures = [
            'browse_community_runs' => [
                'key' => 'browse_community_runs',
                'name' => 'Browse Community Runs',
                'description' => 'Allow users to browse and watch completed challenge runs from the community',
                'defaultEnabled' => false,
            ],
            'shop' => [
                'key' => 'shop',
                'name' => 'Card Design Shop',
                'description' => 'Allow users to purchase premium card design sets',
                'defaultEnabled' => true,
            ],
        ];

        $features = [];

        foreach ($availableFeatures as $key => $config) {
            $setting = $this->featureSettingsRepository->findOneBy(['featureKey' => $key]);

            $features[] = [
                'key' => $key,
                'name' => $config['name'],
                'description' => $config['description'],
                'enabled' => $setting ? $setting->isEnabled() : $config['defaultEnabled'],
            ];
        }

        return $this->json([
            'success' => true,
            'data' => ['features' => $features],
        ]);
    }
}
